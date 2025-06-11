<?php
// File: app/scripts/marketing/handle_add_lead.php

/**
 * ===================================================================
 * ملف معالجة إضافة عميل محتمل (Add Lead Handler)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج إضافة عميل محتمل جديد.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=marketing/leads');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال البيانات
$data = [
    'full_name'    => Security::sanitizeInput($_POST['full_name'] ?? ''),
    'email'        => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: null,
    'phone_number' => Security::sanitizeInput($_POST['phone_number'] ?? ''),
    'source'       => Security::sanitizeInput($_POST['source'] ?? null),
    'status'       => 'New', // الحالة الافتراضية
];

// الخطوة 4: التحقق من صحة المدخلات
$errors = [];
if (empty($data['full_name'])) {
    $errors[] = 'اسم العميل الكامل مطلوب.';
}
if (empty($data['phone_number']) && empty($data['email'])) {
    $errors[] = 'يجب إدخال رقم الهاتف أو البريد الإلكتروني على الأقل.';
}

if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=marketing/leads');
}

// الخطوة 5: محاولة إنشاء سجل العميل المحتمل
// $new_lead_id = Lead::create($data); // سنحتاج لإنشاء هذا الموديل

// للتوضيح
$new_lead_id = true; 

if ($new_lead_id) {
    Session::flash('success', 'تمت إضافة العميل المحتمل بنجاح.');
} else {
    Session::flash('error', 'حدث خطأ أثناء إضافة العميل المحتمل.');
}

// الخطوة 6: إعادة التوجيه
redirect('public/index.php?url=marketing/leads');
