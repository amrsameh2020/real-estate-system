<?php
// File: app/models/Property.php

/**
 * ===================================================================
 * موديل العقار (Property Model)
 * ===================================================================
 * هذا الكلاس مسؤول عن جميع عمليات قاعدة البيانات المتعلقة بجدول `properties`.
 * يوفر دوال لإدارة بيانات العقارات الأساسية.
 */
class Property {

    /**
     * إنشاء عقار جديد في قاعدة البيانات.
     * @param array $data بيانات العقار (name, property_type, address, city, country).
     * @return string|false The ID of the new property or false on failure.
     */
    public static function create($data) {
        $db = Database::getInstance();
        $sql = "INSERT INTO properties (name, property_type, address, city, country, latitude, longitude) 
                VALUES (:name, :property_type, :address, :city, :country, :latitude, :longitude)";

        $params = [
            ':name' => $data['name'],
            ':property_type' => $data['property_type'],
            ':address' => $data['address'],
            ':city' => $data['city'],
            ':country' => $data['country'],
            ':latitude' => $data['latitude'] ?? null,
            ':longitude' => $data['longitude'] ?? null,
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
     * البحث عن عقار عن طريق معرفه (ID).
     * @param int $id The ID of the property.
     * @return mixed The property data as an associative array or false if not found.
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM properties WHERE id = ? LIMIT 1", [$id]);
        return $stmt->fetch();
    }

    /**
     * جلب جميع العقارات مع إمكانية التصفح (Pagination).
     * @return array An array of all properties.
     */
    public static function getAll() {
        $db = Database::getInstance();
        // يمكن إضافة منطق التصفح هنا لاحقاً
        $stmt = $db->query("SELECT * FROM properties ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * تحديث بيانات عقار موجود.
     * @param int $id The ID of the property to update.
     * @param array $data The data to update.
     * @return bool True on success, false on failure.
     */
    public static function update($id, $data) {
        $db = Database::getInstance();
        
        $fields = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            if ($key !== 'id') { // تجنب تحديث المفتاح الأساسي
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return true; // لا يوجد شيء لتحديثه، نعتبر العملية ناجحة
        }

        $sql = "UPDATE properties SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $db->query($sql, $params);
            return true;
        } catch (PDOException $e) {
            // error_log($e->getMessage());
            return false;
        }
    }

    /**
     * حذف عقار من قاعدة البيانات.
     * (ملاحظة: سيتم حذف الوحدات المرتبطة به تلقائياً بسبب إعدادات ON DELETE CASCADE في قاعدة البيانات).
     * @param int $id The ID of the property to delete.
     * @return bool True on success, false on failure.
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $sql = "DELETE FROM properties WHERE id = ?";
        try {
            $db->query($sql, [$id]);
            return true;
        } catch (PDOException $e) {
            // error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * جلب جميع الوحدات التابعة لعقار معين.
     * @param int $property_id
     * @return array
     */
    public static function getUnits($property_id) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM units WHERE property_id = ?", [$property_id]);
        return $stmt->fetchAll();
    }
}
