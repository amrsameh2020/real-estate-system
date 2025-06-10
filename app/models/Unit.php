<?php
// File: app/models/Unit.php

/**
 * ===================================================================
 * موديل الوحدة (Unit Model)
 * ===================================================================
 * هذا الكلاس مسؤول عن جميع عمليات قاعدة البيانات المتعلقة بجدول `units`.
 * يوفر دوال لإدارة بيانات الوحدات العقارية التابعة للعقارات.
 */
class Unit {

    /**
     * إنشاء وحدة جديدة في قاعدة البيانات وربطها بعقار.
     * @param array $data بيانات الوحدة (property_id, unit_number, unit_type, etc.).
     * @return string|false The ID of the new unit or false on failure.
     */
    public static function create($data) {
        $db = Database::getInstance();
        $sql = "INSERT INTO units (property_id, unit_number, unit_type, status, floor_number, area_sqm, bedrooms, bathrooms, market_rent, amenities) 
                VALUES (:property_id, :unit_number, :unit_type, :status, :floor_number, :area_sqm, :bedrooms, :bathrooms, :market_rent, :amenities)";

        $params = [
            ':property_id' => $data['property_id'],
            ':unit_number' => $data['unit_number'],
            ':unit_type' => $data['unit_type'],
            ':status' => $data['status'] ?? 'Vacant',
            ':floor_number' => $data['floor_number'] ?? null,
            ':area_sqm' => $data['area_sqm'] ?? null,
            ':bedrooms' => $data['bedrooms'] ?? null,
            ':bathrooms' => $data['bathrooms'] ?? null,
            ':market_rent' => $data['market_rent'] ?? null,
            // يتم تخزين المرافق ككائن JSON
            ':amenities' => isset($data['amenities']) ? json_encode($data['amenities']) : null,
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
     * البحث عن وحدة عن طريق معرفها (ID).
     * @param int $id The ID of the unit.
     * @return mixed The unit data as an associative array or false if not found.
     */
    public static function findById($id) {
        $db = Database::getInstance();
        // يتم عمل JOIN مع العقار لجلب اسم العقار مع بيانات الوحدة
        $stmt = $db->query(
            "SELECT units.*, properties.name as property_name 
             FROM units 
             LEFT JOIN properties ON units.property_id = properties.id
             WHERE units.id = ? LIMIT 1", 
             [$id]
        );
        return $stmt->fetch();
    }

    /**
     * تحديث بيانات وحدة موجودة.
     * @param int $id The ID of the unit to update.
     * @param array $data The data to update.
     * @return bool True on success, false on failure.
     */
    public static function update($id, $data) {
        $db = Database::getInstance();
        
        $fields = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                if ($key === 'amenities' && is_array($value)) {
                    $fields[] = "amenities = :amenities";
                    $params[":amenities"] = json_encode($value);
                } else {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }
        }

        if (empty($fields)) {
            return true;
        }

        $sql = "UPDATE units SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $db->query($sql, $params);
            return true;
        } catch (PDOException $e) {
            // error_log($e->getMessage());
            return false;
        }
    }

    /**
     * حذف وحدة من قاعدة البيانات.
     * @param int $id The ID of the unit to delete.
     * @return bool True on success, false on failure.
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $sql = "DELETE FROM units WHERE id = ?";
        try {
            // قبل الحذف، يجب التأكد من عدم وجود عقود إيجار نشطة مرتبطة بالوحدة
            // هذا المنطق يجب أن يكون في الـ Controller أو الـ Script قبل استدعاء هذه الدالة
            $db->query($sql, [$id]);
            return true;
        } catch (PDOException $e) {
            // error_log($e->getMessage());
            return false;
        }
    }
}
