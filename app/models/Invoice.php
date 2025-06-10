<?php
// File: app/models/Invoice.php

/**
 * ===================================================================
 * موديل الفاتورة (Invoice Model)
 * ===================================================================
 * هذا الكلاس مسؤول عن جميع عمليات قاعدة البيانات المتعلقة بجدول `invoices`.
 * يوفر دوال لإنشاء الفواتير، جلبها، تحديث حالتها، وتوليد الفواتير الدورية
 * المرتبطة بعقود الإيجار.
 */
class Invoice {

    /**
     * إنشاء فاتورة جديدة في قاعدة البيانات.
     * @param array $data بيانات الفاتورة (lease_id, invoice_type, amount, due_date, etc.).
     * @return string|false The ID of the new invoice or false on failure.
     */
    public static function create($data) {
        $db = Database::getInstance();
        $sql = "INSERT INTO invoices (lease_id, invoice_type, amount, due_date, status, vat_percentage, total_amount) 
                VALUES (:lease_id, :invoice_type, :amount, :due_date, :status, :vat_percentage, :total_amount)";

        // حساب المبلغ الإجمالي مع ضريبة القيمة المضافة
        $vat_percentage = $data['vat_percentage'] ?? 0;
        $total_amount = $data['amount'] * (1 + ($vat_percentage / 100));

        $params = [
            ':lease_id' => $data['lease_id'],
            ':invoice_type' => $data['invoice_type'] ?? 'Rent',
            ':amount' => $data['amount'],
            ':due_date' => $data['due_date'],
            ':status' => $data['status'] ?? 'Unpaid',
            ':vat_percentage' => $vat_percentage,
            ':total_amount' => $total_amount,
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
     * البحث عن فاتورة عن طريق معرفها (ID).
     * @param int $id The ID of the invoice.
     * @return mixed The invoice data as an associative array or false if not found.
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT 
                invoices.*,
                leases.start_date, leases.end_date,
                units.unit_number,
                properties.name as property_name,
                tenants.full_name as tenant_name
             FROM invoices
             LEFT JOIN leases ON invoices.lease_id = leases.id
             LEFT JOIN units ON leases.unit_id = units.id
             LEFT JOIN properties ON units.property_id = properties.id
             LEFT JOIN users as tenants ON leases.tenant_user_id = tenants.id
             WHERE invoices.id = ? LIMIT 1",
            [$id]
        );
        return $stmt->fetch();
    }

    /**
     * جلب جميع الفواتير.
     * @return array An array of all invoices.
     */
    public static function getAll() {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT 
                invoices.id, invoices.status, invoices.due_date, invoices.total_amount,
                units.unit_number,
                tenants.full_name as tenant_name
             FROM invoices
             LEFT JOIN leases ON invoices.lease_id = leases.id
             LEFT JOIN units ON leases.unit_id = units.id
             LEFT JOIN users as tenants ON leases.tenant_user_id = tenants.id
             ORDER BY invoices.due_date DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * تحديث حالة فاتورة معينة.
     * @param int $id The ID of the invoice.
     * @param string $newStatus The new status ('Paid', 'PartiallyPaid', 'Overdue').
     * @return bool True on success, false on failure.
     */
    public static function updateStatus($id, $newStatus) {
        $db = Database::getInstance();
        $sql = "UPDATE invoices SET status = ? WHERE id = ?";
        try {
            $db->query($sql, [$newStatus, $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * جلب جميع الفواتير المتأخرة (التي تجاوز تاريخ استحقاقها ولم تدفع بالكامل).
     * @return array
     */
    public static function getOverdueInvoices() {
        $db = Database::getInstance();
        $today = date('Y-m-d');
        $sql = "SELECT * FROM invoices WHERE due_date < ? AND status IN ('Unpaid', 'PartiallyPaid', 'Overdue')";
        $stmt = $db->query($sql, [$today]);
        return $stmt->fetchAll();
    }
    
    /**
     * جلب جميع الفواتير الخاصة بعقد إيجار معين.
     * @param int $lease_id
     * @return array
     */
    public static function findByLeaseId($lease_id) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM invoices WHERE lease_id = ? ORDER BY due_date ASC", [$lease_id]);
        return $stmt->fetchAll();
    }
}
