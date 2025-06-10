<?php
// File: app/core/Session.php

/**
 * ===================================================================
 * كلاس إدارة الجلسات (Session Class)
 * ===================================================================
 * يوفر هذا الكلاس واجهة بسيطة وآمنة للتعامل مع جلسات PHP.
 * يقوم بإدارة بدء الجلسات بإعدادات آمنة، تعيين وحذف البيانات، والتعامل
 * مع رسائل "flash" التي تظهر لمرة واحدة فقط (مثل رسائل النجاح أو الخطأ).
 */
class Session {
    /**
     * بدء الجلسة بإعدادات آمنة لمنع هجمات سرقة الجلسات.
     * يجب استدعاء هذه الدالة مرة واحدة فقط في بداية التطبيق.
     */
    public static function start() {
        // التحقق مما إذا كانت الجلسة قد بدأت بالفعل
        if (session_status() == PHP_SESSION_NONE) {
            // منع JavaScript من الوصول إلى session cookie (الحماية من XSS)
            ini_set('session.cookie_httponly', 1);
            
            // التأكد من أن معرف الجلسة يتم إرساله كـ cookie فقط
            ini_set('session.use_only_cookies', 1);

            // إرسال الـ cookie فقط عبر اتصال HTTPS آمن
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            
            // استخدام اسم مخصص للجلسة لزيادة الأمان
            session_name('app_secure_session');
            
            session_start();
        }
    }

    /**
     * تعيين قيمة في الجلسة.
     * @param string $key المفتاح
     * @param mixed $value القيمة
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * جلب قيمة من الجلسة.
     * @param string $key المفتاح
     * @param mixed $default القيمة الافتراضية إذا كان المفتاح غير موجود
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * التحقق من وجود مفتاح معين في الجلسة.
     * @param string $key المفتاح
     * @return bool
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * حذف مفتاح معين من الجلسة.
     * @param string $key المفتاح
     */
    public static function remove($key) {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * رسائل تظهر مرة واحدة فقط.
     * إذا تم استدعاؤها مع قيمة، تقوم بتعيين الرسالة.
     * إذا تم استدعاؤها بدون قيمة، تقوم بجلب الرسالة وحذفها.
     * @param string $key المفتاح
     * @param string $message الرسالة (اختياري)
     * @return string|null
     */
    public static function flash($key, $message = '') {
        if (!empty($message)) {
            self::set('flash_' . $key, $message);
        } else if (self::has('flash_' . $key)) {
            $message = self::get('flash_' . $key);
            self::remove('flash_' . $key);
            return $message;
        }
        return null;
    }

    /**
     * إنهاء الجلسة الحالية بالكامل.
     */
    public static function destroy() {
        // إفراغ جميع متغيرات الجلسة
        $_SESSION = [];
        
        // حذف الـ cookie الخاص بالجلسة
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // إنهاء الجلسة
        session_destroy();
    }
}
