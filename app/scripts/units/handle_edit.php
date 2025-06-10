<?php
// File: app/scripts/units/handle_edit.php

/**
 * ===================================================================
 * ملف معالجة تعديل وحدة (Edit Unit Handler)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج تعديل بيانات وحدة موجودة.
 * يستقبل البيانات، يقوم بالتحقق منها، يحدثها في قاعدة البيانات،
 * ثم يعيد توجيه المستخدم مع رسالة مناسبة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام للوصول إلى كل شيء
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // إذا لم يكن الطلب POST، أعد التوجيه للصفحة الرئيسية
    redirect('public/index.php?url=dashboard');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال بيانات النموذج
$unit_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT); // للحفاظ عليه من أجل إعادة التوجيه

$data = [
    'unit_number' => Security::sanitizeInput($_POST['unit_number'] ?? ''),
    'unit_type'   => Security::sanitizeInput($_POST['unit_type'] ?? ''),
    'floor_number'=> filter_input(INPUT_POST, 'floor_number', FILTER_VALIDATE_INT, ['options' => ['default' => null]]),
    'area_sqm'    => filter_input(INPUT_POST, 'area_sqm', FILTER_VALIDATE_FLOAT, ['options' => ['default' => null]]),
    'bedrooms'    => filter_input(INPUT_POST, 'bedrooms', FILTER_VALIDATE_INT, ['options' => ['default' => null]]),
    'bathrooms'   => filter_input(INPUT_POST, 'bathrooms', FILTER_VALIDATE_INT, ['options' => ['default' => null]]),
    'market_rent' => filter_input(INPUT_POST, 'market_rent', FILTER_VALIDATE_FLOAT, ['options' => ['default' => null]]),
    'status'      => Security::sanitizeInput($_POST['status'] ?? 'Vacant'),
    'amenities'   => Security::sanitizeInput($_POST['amenities'] ?? ''),
];

// معالجة المرافق: تحويل النص المفصول بفاصلة إلى مصفوفة JSON
if (!empty($data['amenities'])) {
    // تقسيم النص إلى مصفوفة، وإزالة الفراغات الزائدة من كل عنصر
    $amenities_array = array_map('trim', explode(',', $data['amenities']));
    $data['amenities'] = $amenities_array; // سيتم تحويلها لـ JSON في الموديل
} else {
    $data['amenities'] = [];
}

// الخطوة 4: التحقق من صحة المدخلات
$errors = [];
if ($unit_id === false) {
    $errors[] = 'معرف الوحدة غير صالح.';
}
if (empty($data['unit_number'])) {
    $errors[] = 'حقل رقم الوحدة مطلوب.';
}
if (empty($data['unit_type'])) {
    $errors[] = 'حقل نوع الوحدة مطلوب.';
}
if (!in_array($data['status'], ['Vacant', 'Occupied', 'UnderMaintenance'])) {
    $errors[] = 'قيمة حالة الوحدة غير صالحة.';
}

// إذا وجدت أخطاء، قم بحفظها في رسالة flash وأعد التوجيه لصفحة التعديل
if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=units/edit/' . $unit_id);
}

// الخطوة 5: محاولة تحديث البيانات في قاعدة البيانات
$success = Unit::update($unit_id, $data);

if ($success) {
    // في حالة النجاح
    Session::flash('success', 'تم تحديث بيانات الوحدة بنجاح!');
    // إعادة التوجيه إلى صفحة عرض الوحدة التي تم تحديثها
    redirect('public/index.php?url=units/view/' . $unit_id);
} else {
    // في حالة الفشل
    Session::flash('error', 'حدث خطأ أثناء تحديث بيانات الوحدة. الرجاء المحاولة مرة أخرى.');
    redirect('public/index.php?url=units/edit/' . $unit_id);
}
