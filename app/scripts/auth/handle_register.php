<?php
// File: app/scripts/auth/handle_register.php

/**
 * ===================================================================
 * ملف معالجة إنشاء حساب جديد (Register Handler Script)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية (Backend) لنموذج التسجيل العام.
 * يستقبل بيانات المستخدم الجديد، يقوم بالتحقق منها بشكل صارم، ينشئ الحساب،
 * ثم يقوم بتسجيل دخوله تلقائياً ويعيد توجيهه إلى لوحة التحكم.
 */

// الخطوة 1: تضمين ملف التشغيل الأساسي للوصول إلى كل شيء
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من أن الطلب هو من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=register');
}

// الخطوة 3: التحقق من توكن CSRF للأمان
$token = $_POST['csrf_token'] ?? '';
Security::validateCsrfToken($token);

// الخطوة 4: تنظيف واستقبال بيانات النموذج
$full_name = Security::sanitizeInput($_POST['full_name'] ?? '');
$email = Security::sanitizeInput($_POST['email'] ?? '');
$phone_number = Security::sanitizeInput($_POST['phone_number'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// الخطوة 5: التحقق من صحة المدخلات (Server-side Validation)
$errors = [];

if (empty($full_name)) {
    $errors[] = 'حقل الاسم الكامل مطلوب.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'الرجاء إدخال بريد إلكتروني صالح.';
} elseif (User::findByEmail($email)) {
    // التحقق من أن البريد الإلكتروني غير مسجل مسبقاً
    $errors[] = 'هذا البريد الإلكتروني مسجل بالفعل. الرجاء استخدام بريد إلكتروني آخر.';
}

if (empty($phone_number)) {
    $errors[] = 'حقل رقم الهاتف مطلوب.';
}

if (empty($password)) {
    $errors[] = 'حقل كلمة المرور مطلوب.';
} elseif (strlen($password) < 8) {
    $errors[] = 'يجب أن لا تقل كلمة المرور عن 8 أحرف.';
}

if ($password !== $confirm_password) {
    $errors[] = 'كلمتا المرور غير متطابقتين.';
}

// إذا وجدت أخطاء، قم بحفظها في رسالة flash وأعد التوجيه لصفحة التسجيل
if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=register');
}

// الخطوة 6: إنشاء المستخدم الجديد
$data = [
    'full_name' => $full_name,
    'email' => $email,
    'phone_number' => $phone_number,
    'password' => $password,
    'role_id' => 4 // تعيين دور "مستأجر" (Tenant) افتراضياً للمسجلين الجدد
];

$new_user_id = User::create($data);

if ($new_user_id) {
    // الخطوة 7: في حالة النجاح، قم بتسجيل دخول المستخدم تلقائياً
    if (Auth::login($email, $password)) {
        Session::flash('success', 'تم إنشاء حسابك وتسجيل دخولك بنجاح. أهلاً بك!');
        redirect('public/index.php?url=dashboard');
    } else {
        // حالة نادرة: نجح إنشاء الحساب ولكن فشل تسجيل الدخول
        Session::flash('warning', 'تم إنشاء حسابك بنجاح، ولكن حدث خطأ أثناء تسجيل الدخول. الرجاء المحاولة يدوياً.');
        redirect('public/index.php?url=login');
    }
} else {
    // في حالة فشل إنشاء المستخدم في قاعدة البيانات
    Session::flash('error', 'حدث خطأ غير متوقع أثناء إنشاء الحساب. الرجاء المحاولة مرة أخرى.');
    redirect('public/index.php?url=register');
}
