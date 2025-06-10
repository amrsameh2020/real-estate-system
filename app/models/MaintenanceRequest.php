<?php
// File: app/models/MaintenanceRequest.php

/**
 * ===================================================================
 * موديل طلب الصيانة (MaintenanceRequest Model)
 * ===================================================================
 * هذا الكلاس مسؤول عن جميع عمليات قاعدة البيانات المتعلقة بجدول `maintenance_requests`.
 * يوفر دوال لإدارة طلبات الصيانة المقدمة من المستأجرين، تعيينها للفنيين أو الموردين،
 * وتتبع حالتها من الإنشاء إلى الإنجاز.
 */
class MaintenanceRequest {

    /**
     * إنشاء طلب صيانة جديد في قاعدة البيانات.
     * @param array $data بيانات الطلب (unit_id, requested_by_tenant_id, title, etc.).
     * @return string|false The ID of the new request or false on failure.
     */
    public static function create($data) {
        $db = Database::getInstance();
        $sql = "INSERT INTO maintenance_requests (unit_id, requested_by_tenant_id, title, description, priority, status) 
                VALUES (:unit_id, :requested_by_tenant_id, :title, :description, :priority, :status)";

        $params = [
            ':unit_id' => $data['unit_id'],
            ':requested_by_tenant_id' => $data['requested_by_tenant_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':priority' => $data['priority'] ?? 'Medium',
            ':status' => $data['status'] ?? 'New',
        ];

        try {
            $db->query($sql, $params);
            return $db->pdo->lastInsertId();
        } catch (PDOException $e) {
            // error_log($e->getMessage());
            return false;
        }
    }

    /**
     * البحث عن طلب صيانة عن طريق معرفه (ID).
     * @param int $id The ID of the request.
     * @return mixed The request data as an associative array or false if not found.
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT 
                mr.*,
                u.unit_number,
                p.name as property_name,
                tenant.full_name as tenant_name,
                technician.full_name as technician_name,
                vendor.name as vendor_name
             FROM maintenance_requests mr
             LEFT JOIN units u ON mr.unit_id = u.id
             LEFT JOIN properties p ON u.property_id = p.id
             LEFT JOIN users tenant ON mr.requested_by_tenant_id = tenant.id
             LEFT JOIN users technician ON mr.assigned_to_user_id = technician.id
             LEFT JOIN vendors vendor ON mr.assigned_to_vendor_id = vendor.id
             WHERE mr.id = ? LIMIT 1",
            [$id]
        );
        return $stmt->fetch();
    }

    /**
     * جلب جميع طلبات الصيانة.
     * @return array An array of all maintenance requests.
     */
    public static function getAll() {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT 
                mr.id, mr.title, mr.status, mr.priority, mr.created_at,
                u.unit_number,
                p.name as property_name
             FROM maintenance_requests mr
             LEFT JOIN units u ON mr.unit_id = u.id
             LEFT JOIN properties p ON u.property_id = p.id
             ORDER BY mr.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * تحديث حالة طلب صيانة معين.
     * @param int $id The ID of the request.
     * @param string $status The new status.
     * @return bool True on success, false on failure.
     */
    public static function updateStatus($id, $status) {
        $db = Database::getInstance();
        $sql = "UPDATE maintenance_requests SET status = ? WHERE id = ?";
        try {
            $db->query($sql, [$status, $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * تعيين طلب صيانة لفني داخلي أو مورد خارجي.
     * @param int $id The ID of the request.
     * @param int|null $user_id The ID of the internal user (technician).
     * @param int|null $vendor_id The ID of the external vendor.
     * @return bool True on success, false on failure.
     */
    public static function assign($id, $user_id = null, $vendor_id = null) {
        $db = Database::getInstance();
        // عند التعيين، يتم أيضاً تحديث الحالة إلى "Assigned"
        $sql = "UPDATE maintenance_requests SET assigned_to_user_id = ?, assigned_to_vendor_id = ?, status = 'Assigned' WHERE id = ?";
        try {
            $db->query($sql, [$user_id, $vendor_id, $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * حذف طلب صيانة من قاعدة البيانات.
     * @param int $id The ID of the request to delete.
     * @return bool True on success, false on failure.
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $sql = "DELETE FROM maintenance_requests WHERE id = ?";
        try {
            $db->query($sql, [$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
