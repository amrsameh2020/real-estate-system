<?php
// File: app/core/Security.php

/**
 * ===================================================================
 * كلاس الأمان (Security Class)
 * ===================================================================
 * يوفر هذا الكلاس دوال أساسية للأمان لحماية التطبيق من الثغرات الشائعة
 * مثل هجمات تزوير الطلبات عبر المواقع (CSRF) وهجمات البرمجة عبر المواقع (XSS).
 */
class Security {
    /**
     * إنشاء أو جلب توكن CSRF من الجلسة.
     * يتم إنشاء توكن فريد لكل جلسة مستخدم.
     * @return string
     */
    public static function generateCsrfToken() {
        if (!Session::has('csrf_token')) {
            // إنشاء توكن آمن عشوائياً مكون من 32 بايت (64 حرفاً بالنظام الست عشري)
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return Session::get('csrf_token');
    }

    /**
     * التحقق من صحة توكن CSRF القادم مع النموذج.
     * تستخدم هذه الدالة `hash_equals` للمقارنة الآمنة ضد هجمات التوقيت (Timing Attacks).
     * @param string $token The token from the form.
     */
    public static function validateCsrfToken($token) {
        if (!Session::has('csrf_token') || !hash_equals(Session::get('csrf_token'), $token)) {
            // في حالة فشل التحقق، يجب إيقاف التنفيذ فوراً لمنع أي إجراء ضار.
            die('خطأ في التحقق من صحة الطلب (CSRF Token Mismatch).');
        }
        // بعد الاستخدام الناجح، قم بتجديد التوكن لمنع إعادة استخدامه
        self::regenerateCsrfToken();
    }

    /**
     * تجديد توكن CSRF بعد كل طلب ناجح لزيادة الأمان.
     * هذا يجعل من الصعب على المهاجم تخمين التوكن التالي.
     */
    private static function regenerateCsrfToken() {
        Session::remove('csrf_token');
        self::generateCsrfToken();
    }

    /**
     * دالة لتنظيف المدخلات من المستخدم للحماية من هجمات XSS.
     * تقوم بإزالة أي وسوم (Tags) HTML و PHP وفراغات زائدة من بداية ونهاية النص.
     * @param mixed $data The data to be sanitized (can be a string or an array).
     * @return mixed
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            // إذا كانت البيانات مصفوفة، قم بتنظيف كل عنصر فيها بشكل تعاودي (recursively)
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        // تنظيف البيانات الفردية
        return trim(strip_tags($data));
    }
}
