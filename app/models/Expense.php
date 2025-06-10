<?php
// File: app/models/Expense.php

/**
 * ===================================================================
 * موديل المصروف (Expense Model)
 * ===================================================================
 * هذا الكلاس مسؤول عن جميع عمليات قاعدة البيانات المتعلقة بجدول `expenses`.
 * يوفر دوال لتسجيل المصروفات، ربطها بالعقارات، وتتبع حالة الموافقة عليها
 * من قبل المالك.
 */
class Expense {

    /**
     * تسجيل مصروف جديد في قاعدة البيانات.
     * @param array $data بيانات المصروف (property_id, expense_category, amount, etc.).
     * @return string|false The ID of the new expense or false on failure.
     */
    public static function create($data) {
        $db = Database::getInstance();
        $sql = "INSERT INTO expenses (property_id, expense_category, amount, expense_date, description, receipt_url, recorded_by_user_id, approval_status) 
                VALUES (:property_id, :expense_category, :amount, :expense_date, :description, :receipt_url, :recorded_by_user_id, :approval_status)";

        $params = [
            ':property_id' => $data['property_id'] ?? null,
            ':expense_category' => $data['expense_category'],
            ':amount' => $data['amount'],
            ':expense_date' => $data['expense_date'],
            ':description' => $data['description'],
            ':receipt_url' => $data['receipt_url'] ?? null,
            ':recorded_by_user_id' => $data['recorded_by_user_id'],
            ':approval_status' => $data['approval_status'] ?? 'Pending',
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
     * البحث عن مصروف عن طريق معرفه (ID).
     * @param int $id The ID of the expense.
     * @return mixed The expense data as an associative array or false if not found.
     */
    public static function findById($id) {
        $db = Database::getInstance();
        // يتم عمل JOIN مع جداول أخرى لجلب اسم العقار واسم المسجل
        $stmt = $db->query(
            "SELECT 
                expenses.*,
                properties.name as property_name,
                users.full_name as recorder_name
             FROM expenses
             LEFT JOIN properties ON expenses.property_id = properties.id
             LEFT JOIN users ON expenses.recorded_by_user_id = users.id
             WHERE expenses.id = ? LIMIT 1",
            [$id]
        );
        return $stmt->fetch();
    }

    /**
     * جلب جميع المصروفات.
     * @return array An array of all expenses.
     */
    public static function getAll() {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT 
                expenses.id, expenses.expense_category, expenses.amount, expenses.expense_date, expenses.approval_status,
                properties.name as property_name
             FROM expenses
             LEFT JOIN properties ON expenses.property_id = properties.id
             ORDER BY expenses.expense_date DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * تحديث حالة الموافقة على مصروف معين.
     * @param int $id The ID of the expense.
     * @param string $status The new status ('Approved', 'Rejected').
     * @param int $owner_id The ID of the owner who approved/rejected.
     * @return bool True on success, false on failure.
     */
    public static function updateApprovalStatus($id, $status, $owner_id) {
        $db = Database::getInstance();
        $sql = "UPDATE expenses SET approval_status = ?, approved_by_owner_id = ? WHERE id = ?";
        try {
            $db->query($sql, [$status, $owner_id, $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * حذف مصروف من قاعدة البيانات.
     * @param int $id The ID of the expense to delete.
     * @return bool True on success, false on failure.
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $sql = "DELETE FROM expenses WHERE id = ?";
        try {
            $db->query($sql, [$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
