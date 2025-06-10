<?php
// File: app/core/Database.php

/**
 * ===================================================================
 * كلاس الاتصال بقاعدة البيانات (Database Class)
 * ===================================================================
 * يستخدم هذا الكلاس نمط Singleton لضمان وجود اتصال واحد فقط بقاعدة البيانات
 * طوال دورة حياة الطلب، مما يحسن الأداء ويحافظ على الموارد.
 * جميع الاستعلامات يتم تنفيذها باستخدام PDO Prepared Statements للحماية من هجمات حقن SQL.
 */
class Database {
    // سيحتفظ بنسخة الكلاس الوحيدة
    private static $instance = null;
    
    // سيحتفظ بكائن الاتصال PDO
    public $pdo;

    /**
     * الـ constructor خاص (private) لمنع إنشاء نسخ جديدة من الكلاس مباشرة.
     */
    private function __construct() {
        // بناء سلسلة DSN (Data Source Name)
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        
        // إعدادات PDO لضمان أفضل الممارسات
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // رمي استثناءات (exceptions) عند حدوث أخطاء
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // جلب النتائج كمصفوفة ترابطية (associative array)
            PDO::ATTR_EMULATE_PREPARES   => false,                  // استخدام prepared statements حقيقية بدلاً من محاكاتها
        ];

        try {
            // محاولة إنشاء كائن PDO جديد للاتصال
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // في حالة فشل الاتصال، يتم إيقاف التنفيذ وعرض رسالة خطأ واضحة
            // في البيئة الإنتاجية، يجب تسجيل هذا الخطأ في ملف logs بدلاً من عرضه للمستخدم
            die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
        }
    }

    /**
     * الحصول على نسخة وحيدة من كلاس قاعدة البيانات (Singleton Pattern).
     * هذه هي الطريقة الوحيدة للحصول على كائن من هذا الكلاس.
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * دالة لتنفيذ الاستعلامات بشكل آمن.
     * @param string $sql The SQL query to execute.
     * @param array $params The parameters to bind to the query.
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // معالجة الخطأ أثناء تنفيذ الاستعلام
            die('خطأ في الاستعلام: ' . $e->getMessage());
        }
    }
}
