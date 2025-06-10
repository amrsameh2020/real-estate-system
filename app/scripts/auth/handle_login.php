<?php
// File: app/scripts/auth/handle_login.php

/**
 * ===================================================================
 * ملف معالجة تسجيل الدخول (Login Handler Script)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية (Backend) لنموذج تسجيل الدخول.
 * يستقبل البيانات المرسلة، يقوم بالتحقق منها، يستدعي دالة المصادقة،
 * ثم يعيد توجيه المستخدم إلى الصفحة المناسبة مع رسالة نجاح أو خطأ.
 */

// الخطوة 1: تضمين ملف التشغيل الأساسي للوصول إلى الدوال والكلاسات
// هذا ضروري للوصول إلى كل شيء: Database, Session, Security, Auth, etc.
require_once __DIR__ . '/../../core/bootstrap.php';

// -- الخطوة 2: التحقق من أن الطلب هو من نوع POST --
// هذا يمنع الوصول المباشر للملف عبر الرابط في المتصفح.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // إذا لم يكن الطلب POST، أعد التوجيه للصفحة الرئيسية أو صفحة الدخول
    redirect('public/index.php?url=login');
}

// -- الخطوة 3: التحقق من توكن CSRF للأمان --
// استلام التوكن من النموذج
$token = $_POST['csrf_token'] ?? '';
// التحقق من صحة التوكن
Security::validateCsrfToken($token);

// -- الخطوة 4: تنظيف واستقبال بيانات النموذج --
// استخدام دالة التنظيف من كلاس Security لضمان إزالة أي أكواد ضارة
$email = Security::sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// -- الخطوة 5: التحقق من صحة المدخلات (Server-side Validation) --
$errors = [];
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'الرجاء إدخال بريد إلكتروني صالح.';
}
if (empty($password)) {
    $errors[] = 'الرجاء إدخال كلمة المرور.';
}

// إذا وجدت أخطاء، قم بحفظها في رسالة flash وأعد التوجيه لصفحة الدخول
if (!empty($errors)) {
    // يمكنك حفظ الأخطاء لعرضها فوق الحقول
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=login');
}

// -- الخطوة 6: محاولة تسجيل الدخول باستخدام كلاس Auth --
if (Auth::login($email, $password)) {
    // في حالة النجاح
    Session::flash('success', 'أهلاً بعودتك! تم تسجيل الدخول بنجاح.');
    redirect('public/index.php?url=dashboard');
} else {
    // في حالة الفشل (البيانات غير صحيحة)
    Session::flash('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة.');
    redirect('public/index.php?url=login');
}