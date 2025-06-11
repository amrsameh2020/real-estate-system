<?php
// File: app/scripts/maintenance/handle_add_vendor.php

/**
 * ===================================================================
 * ملف معالجة إضافة مورد جديد
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج إضافة مورد أو شركة صيانة جديدة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=maintenance/vendors');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال البيانات
$data = [
    'name'           => Security::sanitizeInput($_POST['name'] ?? ''),
    'contact_person' => Security::sanitizeInput($_POST['contact_person'] ?? null),
    'phone'          => Security::sanitizeInput($_POST['phone'] ?? null),
    'email'          => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: null,
    'specialty'      => Security::sanitizeInput($_POST['specialty'] ?? ''),
];

// الخطوة 4: التحقق من صحة المدخلات
$errors = [];
if (empty($data['name'])) {
    $errors[] = 'اسم المورد أو الشركة مطلوب.';
}
if (empty($data['specialty'])) {
    $errors[] = 'تخصص المورد مطلوب.';
}

if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=maintenance/vendors'); // أو لصفحة الإنشاء
}

// الخطوة 5: محاولة إنشاء سجل المورد
// $new_vendor_id = Vendor::create($data); // سنحتاج لإنشاء هذا الموديل

// للتوضيح
$new_vendor_id = true; 

if ($new_vendor_id) {
    Session::flash('success', 'تمت إضافة المورد بنجاح.');
} else {
    Session::flash('error', 'حدث خطأ أثناء إضافة المورد.');
}

// الخطوة 6: إعادة التوجيه
redirect('public/index.php?url=maintenance/vendors');
