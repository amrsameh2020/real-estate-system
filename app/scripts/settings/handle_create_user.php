<?php
// File: app/scripts/settings/handle_create_user.php

/**
 * ===================================================================
 * ملف معالجة إنشاء مستخدم جديد (بواسطة المدير)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج إضافة مستخدم جديد من لوحة تحكم المدير.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=settings/users');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال بيانات النموذج
$data = [
    'full_name'    => Security::sanitizeInput($_POST['full_name'] ?? ''),
    'email'        => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
    'phone_number' => Security::sanitizeInput($_POST['phone_number'] ?? ''),
    'role_id'      => filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT),
    'password'     => $_POST['password'] ?? '', // لا يتم تنظيف كلمة المرور
    'is_active'    => filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT),
];

// الخطوة 4: التحقق من صحة المدخلات
$errors = [];
if (empty($data['full_name'])) $errors[] = 'الاسم الكامل مطلوب.';
if (empty($data['email'])) $errors[] = 'بريد إلكتروني صالح مطلوب.';
if (empty($data['phone_number'])) $errors[] = 'رقم الهاتف مطلوب.';
if (empty($data['role_id'])) $errors[] = 'الدور مطلوب.';
if (empty($data['password'])) $errors[] = 'كلمة المرور مطلوبة.';

if (User::findByEmail($data['email'])) {
    $errors[] = 'البريد الإلكتروني مستخدم بالفعل.';
}

if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=settings/create_user');
}

// الخطوة 5: محاولة إنشاء المستخدم
$new_user_id = User::create($data);

if ($new_user_id) {
    Session::flash('success', 'تم إنشاء المستخدم بنجاح.');
    redirect('public/index.php?url=settings/users');
} else {
    Session::flash('error', 'حدث خطأ أثناء إنشاء المستخدم.');
    redirect('public/index.php?url=settings/create_user');
}