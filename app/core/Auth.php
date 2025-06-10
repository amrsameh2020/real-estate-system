<?php
// File: app/core/Auth.php

/**
 * ===================================================================
 * كلاس المصادقة (Authentication Class)
 * ===================================================================
 * هذا الكلاس هو المسؤول عن كل ما يتعلق بمصادقة المستخدمين وصلاحياتهم.
 * يوفر دوال لتسجيل الدخول، الخروج، التحقق من حالة تسجيل الدخول،
 * جلب بيانات المستخدم الحالي، والتحقق من الأدوار والصلاحيات.
 * يعتمد على كلاس Session لتخزين بيانات المستخدم وكلاس User لجلبها من قاعدة البيانات.
 */
class Auth {

    /**
     * محاولة تسجيل دخول المستخدم.
     * تبحث عن المستخدم بالبريد الإلكتروني ثم تتحقق من تطابق كلمة المرور المشفرة.
     * @param string $email
     * @param string $password
     * @return bool - true عند نجاح تسجيل الدخول، false عند الفشل.
     */
    public static function login($email, $password) {
        // البحث عن المستخدم باستخدام الموديل User
        $user = User::findByEmail($email);

        // التحقق من وجود المستخدم ومن أن كلمة المرور صحيحة
        if ($user && password_verify($password, $user['password_hash'])) {
            // تجديد معرف الجلسة بعد تسجيل الدخول الناجح للحماية من هجمات Session Fixation
            session_regenerate_id(true);

            // تسجيل بيانات المستخدم الأساسية في الجلسة للوصول السريع إليها
            Session::set('user_id', $user['id']);
            Session::set('user_role_id', $user['role_id']);
            Session::set('user_full_name', $user['full_name']);
            return true;
        }
        return false;
    }

    /**
     * تسجيل خروج المستخدم الحالي.
     * يقوم بتدمير جميع بيانات الجلسة.
     */
    public static function logout() {
        Session::destroy();
    }

    /**
     * التحقق مما إذا كان هناك مستخدم قد قام بتسجيل الدخول.
     * @return bool
     */
    public static function check() {
        return Session::has('user_id');
    }

    /**
     * جلب بيانات المستخدم الحالي المسجل دخوله بالكامل من قاعدة البيانات.
     * @return array|null - بيانات المستخدم كمصفوفة أو null إذا لم يكن هناك مستخدم مسجل.
     */
    public static function user() {
        if (self::check()) {
            // استخدام الموديل User لجلب أحدث بيانات المستخدم
            return User::findById(Session::get('user_id'));
        }
        return null;
    }

    /**
     * التحقق مما إذا كان للمستخدم الحالي دور (Role) معين.
     * @param string $roleName - اسم الدور للتحقق منه (مثال: 'SystemAdmin').
     * @return bool
     */
    public static function hasRole($roleName) {
        if (!self::check()) return false;
        
        $user_with_role = self::user(); // هذه الدالة تجلب اسم الدور مع بيانات المستخدم

        return $user_with_role && $user_with_role['role_name'] === $roleName;
    }
    
    /**
     * فرض دور معين للوصول إلى الصفحة. إذا لم يكن للمستخدم هذا الدور،
     * يتم حفظ رسالة خطأ وإعادة توجيهه إلى لوحة التحكم الخاصة به.
     * هذه الدالة تعمل كبوابة حماية (Gatekeeper) في بداية الصفحات المحمية.
     * @param string $roleName - اسم الدور المطلوب.
     */
    public static function requireRole($roleName) {
        if (!self::hasRole($roleName)) {
            Session::flash('error', 'غير مصرح لك بالوصول لهذه الصفحة.');
            redirect('public/index.php?url=dashboard');
        }
    }
}
