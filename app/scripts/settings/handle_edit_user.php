<?php
// File: app/scripts/settings/handle_edit_user.php

/**
 * ===================================================================
 * ملف معالجة تعديل مستخدم (بواسطة المدير)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج تعديل مستخدم موجود.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=settings/users');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال البيانات
$user_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$data = [
    'full_name'    => Security::sanitizeInput($_POST['full_name'] ?? ''),
    'email'        => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
    'phone_number' => Security::sanitizeInput($_POST['phone_number'] ?? ''),
    'role_id'      => filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT),
    'is_active'    => filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT),
    'password'     => $_POST['password'] ?? '', // كلمة المرور الجديدة (اختياري)
];

// الخطوة 4: التحقق من صحة المدخلات
$errors = [];
if (!$user_id) $errors[] = 'معرف المستخدم غير صالح.';
if (empty($data['full_name'])) $errors[] = 'الاسم الكامل مطلوب.';
if (empty($data['email'])) $errors[] = 'بريد إلكتروني صالح مطلوب.';

// التحقق من أن البريد الإلكتروني الجديد ليس مستخدماً من قبل شخص آخر
$existing_user = User::findByEmail($data['email']);
if ($existing_user && $existing_user['id'] != $user_id) {
    $errors[] = 'البريد الإلكتروني الجديد مستخدم بالفعل.';
}

// إزالة كلمة المرور من مصفوفة التحديث إذا كانت فارغة
if (empty($data['password'])) {
    unset($data['password']);
}

if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=settings/edit_user/' . $user_id);
}

// الخطوة 5: محاولة تحديث المستخدم
$success = User::update($user_id, $data);

if ($success) {
    Session::flash('success', 'تم تحديث بيانات المستخدم بنجاح.');
    redirect('public/index.php?url=settings/users');
} else {
    Session::flash('error', 'حدث خطأ أثناء تحديث بيانات المستخدم.');
    redirect('public/index.php?url=settings/edit_user/' . $user_id);
}