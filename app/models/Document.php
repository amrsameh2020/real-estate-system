<?php
// File: app/models/Document.php

/**
 * ===================================================================
 * موديل المستند (Document Model)
 * ===================================================================
 * هذا الكلاس مسؤول عن جميع عمليات قاعدة البيانات المتعلقة بجدول `documents`.
 * يوفر دوال لإدارة المستندات المرفوعة للنظام (مثل عقود الإيجار، هويات المستأجرين،
 * صكوك الملكية)، ويربطها بالسجلات الأخرى باستخدام علاقة متعددة الأشكال (Polymorphic).
 */
class Document {

    /**
     * إنشاء سجل مستند جديد في قاعدة البيانات.
     * @param array $data بيانات المستند (documentable_id, documentable_type, document_name, etc.).
     * @return string|false The ID of the new document record or false on failure.
     */
    public static function create($data) {
        $db = Database::getInstance();
        $sql = "INSERT INTO documents (documentable_id, documentable_type, document_name, file_path, uploaded_by_user_id) 
                VALUES (:documentable_id, :documentable_type, :document_name, :file_path, :uploaded_by_user_id)";

        $params = [
            ':documentable_id'      => $data['documentable_id'],
            ':documentable_type'    => $data['documentable_type'], // مثال: 'Lease', 'Unit', 'Property'
            ':document_name'        => $data['document_name'],
            ':file_path'            => $data['file_path'], // مسار الملف بعد رفعه للخادم
            ':uploaded_by_user_id'  => $data['uploaded_by_user_id'] ?? null,
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
     * البحث عن مستند عن طريق معرفه (ID).
     * @param int $id The ID of the document.
     * @return mixed The document data as an associative array or false if not found.
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM documents WHERE id = ? LIMIT 1", [$id]);
        return $stmt->fetch();
    }

    /**
     * جلب جميع المستندات المرتبطة بسجل معين (مثل جميع مستندات عقد إيجار معين).
     * @param int $ownerId The ID of the owner record (e.g., lease_id, property_id).
     * @param string $ownerType The type of the owner record (e.g., 'Lease', 'Property').
     * @return array An array of documents.
     */
    public static function findByOwner($ownerId, $ownerType) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM documents WHERE documentable_id = ? AND documentable_type = ? ORDER BY created_at DESC";
        $stmt = $db->query($sql, [$ownerId, $ownerType]);
        return $stmt->fetchAll();
    }
    
    /**
     * حذف مستند من قاعدة البيانات.
     * يجب أيضاً حذف الملف الفعلي من الخادم عند استدعاء هذه الدالة.
     * @param int $id The ID of the document to delete.
     * @return bool True on success, false on failure.
     */
    public static function delete($id) {
        $db = Database::getInstance();
        
        // جلب مسار الملف قبل حذفه من قاعدة البيانات
        $document = self::findById($id);
        if (!$document) {
            return false; // المستند غير موجود
        }
        
        $sql = "DELETE FROM documents WHERE id = ?";
        try {
            $db->query($sql, [$id]);
            
            // بعد الحذف الناجح من قاعدة البيانات، قم بحذف الملف من الخادم
            $filePath = APP_ROOT . '/public/uploads/' . $document['file_path']; // افترض أن الملفات يتم تخزينها في public/uploads
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            return true;
        } catch (PDOException $e) {
            // error_log($e->getMessage());
            return false;
        }
    }
}
