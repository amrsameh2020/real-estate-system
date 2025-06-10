<?php
// File: app/models/Lease.php

/**
 * ===================================================================
 * موديل عقد الإيجار (Lease Model)
 * ===================================================================
 * هذا الكلاس مسؤول عن جميع عمليات قاعدة البيانات المتعلقة بجدول `leases`.
 * يوفر دوال لإدارة عقود الإيجار، ربط المستأجرين بالوحدات، وتتبع حالات العقود.
 */
class Lease {

    /**
     * إنشاء عقد إيجار جديد في قاعدة البيانات.
     * @param array $data بيانات العقد (unit_id, tenant_user_id, start_date, etc.).
     * @return string|false The ID of the new lease or false on failure.
     */
    public static function create($data) {
        $db = Database::getInstance();
        $sql = "INSERT INTO leases (unit_id, tenant_user_id, start_date, end_date, rent_amount, payment_frequency, security_deposit, status) 
                VALUES (:unit_id, :tenant_user_id, :start_date, :end_date, :rent_amount, :payment_frequency, :security_deposit, :status)";

        $params = [
            ':unit_id' => $data['unit_id'],
            ':tenant_user_id' => $data['tenant_user_id'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':rent_amount' => $data['rent_amount'],
            ':payment_frequency' => $data['payment_frequency'],
            ':security_deposit' => $data['security_deposit'] ?? null,
            ':status' => $data['status'] ?? 'Draft',
        ];

        try {
            $db->query($sql, $params);
            $leaseId = $db->pdo->lastInsertId();
            
            // عند تفعيل العقد، قم بتحديث حالة الوحدة إلى "Occupied"
            if ($params[':status'] === 'Active') {
                Unit::update($params[':unit_id'], ['status' => 'Occupied']);
            }
            
            return $leaseId;
        } catch (PDOException $e) {
            // error_log($e->getMessage());
            return false;
        }
    }

    /**
     * البحث عن عقد إيجار عن طريق معرفه (ID).
     * @param int $id The ID of the lease.
     * @return mixed The lease data as an associative array or false if not found.
     */
    public static function findById($id) {
        $db = Database::getInstance();
        // يتم عمل JOIN مع جداول أخرى لجلب كافة التفاصيل المتعلقة بالعقد
        $stmt = $db->query(
            "SELECT 
                leases.*,
                units.unit_number,
                properties.name as property_name,
                tenants.full_name as tenant_name,
                tenants.email as tenant_email
             FROM leases 
             LEFT JOIN units ON leases.unit_id = units.id
             LEFT JOIN properties ON units.property_id = properties.id
             LEFT JOIN users as tenants ON leases.tenant_user_id = tenants.id
             WHERE leases.id = ? LIMIT 1", 
             [$id]
        );
        return $stmt->fetch();
    }

    /**
     * جلب جميع عقود الإيجار.
     * @return array An array of all leases.
     */
    public static function getAll() {
        $db = Database::getInstance();
        $stmt = $db->query(
           "SELECT 
                leases.id, leases.status, leases.start_date, leases.end_date,
                units.unit_number,
                properties.name as property_name,
                tenants.full_name as tenant_name
            FROM leases
            LEFT JOIN units ON leases.unit_id = units.id
            LEFT JOIN properties ON units.property_id = properties.id
            LEFT JOIN users as tenants ON leases.tenant_user_id = tenants.id
            ORDER BY leases.created_at DESC"
        );
        return $stmt->fetchAll();
    }
    
    /**
     * تحديث حالة عقد معين (مثال: من Draft إلى Active).
     * @param int $id The ID of the lease.
     * @param string $newStatus The new status.
     * @return bool True on success, false on failure.
     */
    public static function updateStatus($id, $newStatus) {
        $db = Database::getInstance();
        $sql = "UPDATE leases SET status = ? WHERE id = ?";
        try {
            $db->query($sql, [$newStatus, $id]);
            
            // منطق إضافي: تحديث حالة الوحدة بناءً على حالة العقد
            $lease = self::findById($id);
            if ($lease) {
                if ($newStatus === 'Active') {
                    Unit::update($lease['unit_id'], ['status' => 'Occupied']);
                } elseif (in_array($newStatus, ['Expired', 'Terminated'])) {
                    Unit::update($lease['unit_id'], ['status' => 'Vacant']);
                }
            }
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * جلب العقود التي على وشك الانتهاء.
     * @param int $days The number of days to look ahead.
     * @return array
     */
    public static function getExpiringSoon($days = 30) {
        $db = Database::getInstance();
        $date = date('Y-m-d', strtotime("+$days days"));
        $sql = "SELECT id, end_date, tenant_user_id FROM leases WHERE status = 'Active' AND end_date <= ?";
        $stmt = $db->query($sql, [$date]);
        return $stmt->fetchAll();
    }
}
