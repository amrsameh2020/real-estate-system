<?php
// File: app/models/User.php

/**
 * ===================================================================
 * موديل المستخدم (User Model)
 * ===================================================================
 * هذا الكلاس هو المسؤول الوحيد عن كل التعاملات مع جدول `users` في قاعدة البيانات.
 * يقوم بتوفير دوال بسيطة لجلب وإنشاء وتعديل وحذف بيانات المستخدمين،
 * مما يفصل منطق قاعدة البيانات عن باقي أجزاء التطبيق.
 */
class User {

    /**
     * إنشاء مستخدم جديد في قاعدة البيانات.
     * @param array $data بيانات المستخدم (full_name, email, password, role_id, phone_number).
     * @return string|false The ID of the new user or false on failure.
     */
    public static function create($data) {
        // الحصول على نسخة من كائن قاعدة البيانات
        $db = Database::getInstance();
        
        // جملة SQL للادخال باستخدام named parameters للحماية
        $sql = "INSERT INTO users (role_id, full_name, email, phone_number, password_hash) 
                VALUES (:role_id, :full_name, :email, :phone_number, :password_hash)";
        
        // تحضير مصفوفة البيانات للادخال
        $params = [
            ':role_id'      => $data['role_id'],
            ':full_name'    => $data['full_name'],
            ':email'        => $data['email'],
            ':phone_number' => $data['phone_number'],
            // تشفير كلمة المرور دائماً قبل حفظها
            ':password_hash'=> password_hash($data['password'], PASSWORD_DEFAULT)
        ];

        try {
            // تنفيذ الاستعلام
            $db->query($sql, $params);
            // إرجاع ID المستخدم الجديد الذي تم إنشاؤه
            return $db->pdo->lastInsertId();
        } catch (PDOException $e) {
            // في حالة حدوث خطأ (مثل بريد إلكتروني مكرر)، قم بتسجيله وإرجاع false
            // error_log($e->getMessage());
            return false;
        }
    }

    /**
     * البحث عن مستخدم عن طريق البريد الإلكتروني.
     * @param string $email
     * @return mixed The user data as an associative array or false if not found.
     */
    public static function findByEmail($email) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
        return $stmt->fetch();
    }

    /**
     * البحث عن مستخدم عن طريق معرفه (ID).
     * يتم عمل JOIN مع جدول roles لجلب اسم الدور مع بيانات المستخدم.
     * @param int $id
     * @return mixed The user data as an associative array or false if not found.
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT users.*, roles.name as role_name 
                            FROM users 
                            LEFT JOIN roles ON users.role_id = roles.id 
                            WHERE users.id = ? LIMIT 1", [$id]);
        return $stmt->fetch();
    }

    /**
     * جلب جميع المستخدمين مع أسماء أدوارهم.
     * @return array An array of all users.
     */
    public static function getAll() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT users.*, roles.name as role_name 
                            FROM users 
                            LEFT JOIN roles ON users.role_id = roles.id 
                            ORDER BY users.created_at DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * تحديث بيانات مستخدم موجود.
     * @param int $id The ID of the user to update.
     * @param array $data The data to update.
     * @return bool True on success, false on failure.
     */
    public static function update($id, $data) {
        $db = Database::getInstance();
        
        // بناء جملة SQL ديناميكياً بناءً على البيانات المرسلة
        $fields = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            // تحديث كلمة المرور فقط إذا لم تكن فارغة
            if ($key === 'password' && !empty($value)) {
                $fields[] = "password_hash = :password_hash";
                $params[':password_hash'] = password_hash($value, PASSWORD_DEFAULT);
            } elseif ($key !== 'password' && $key !== 'id') {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false; // لا يوجد شيء لتحديثه
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $db->query($sql, $params);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * حذف مستخدم من قاعدة البيانات.
     * @param int $id The ID of the user to delete.
     * @return bool True on success, false on failure.
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $sql = "DELETE FROM users WHERE id = ?";
        try {
            $db->query($sql, [$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
