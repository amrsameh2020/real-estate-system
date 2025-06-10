<?php
// File: app/scripts/properties/handle_edit.php

/**
 * ===================================================================
 * ملف معالجة تعديل عقار (Edit Property Handler)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج تعديل بيانات عقار موجود.
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
$property_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$data = [
    'name'          => Security::sanitizeInput($_POST['name'] ?? ''),
    'property_type' => Security::sanitizeInput($_POST['property_type'] ?? ''),
    'address'       => Security::sanitizeInput($_POST['address'] ?? ''),
    'city'          => Security::sanitizeInput($_POST['city'] ?? ''),
    'country'       => Security::sanitizeInput($_POST['country'] ?? ''),
    'latitude'      => Security::sanitizeInput($_POST['latitude'] ?? null),
    'longitude'     => Security::sanitizeInput($_POST['longitude'] ?? null),
];

// الخطوة 4: التحقق من صحة المدخلات (Server-side Validation)
$errors = [];
if ($property_id === false) {
    $errors[] = 'معرف العقار غير صالح.';
}
if (empty($data['name'])) {
    $errors[] = 'حقل اسم العقار مطلوب.';
}
if (empty($data['property_type'])) {
    $errors[] = 'حقل نوع العقار مطلوب.';
}
if (empty($data['address'])) {
    $errors[] = 'حقل العنوان مطلوب.';
}
if (empty($data['city'])) {
    $errors[] = 'حقل المدينة مطلوب.';
}
if (empty($data['country'])) {
    $errors[] = 'حقل الدولة مطلوب.';
}

// إذا وجدت أخطاء، قم بحفظها في رسالة flash وأعد التوجيه لصفحة التعديل
if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=properties/edit/' . $property_id);
}

// الخطوة 5: محاولة تحديث البيانات في قاعدة البيانات
$success = Property::update($property_id, $data);

if ($success) {
    // في حالة النجاح
    Session::flash('success', 'تم تحديث بيانات العقار بنجاح!');
    // إعادة التوجيه إلى صفحة عرض العقار الذي تم تحديثه
    redirect('public/index.php?url=properties/view/' . $property_id);
} else {
    // في حالة الفشل
    Session::flash('error', 'حدث خطأ أثناء تحديث بيانات العقار. الرجاء المحاولة مرة أخرى.');
    redirect('public/index.php?url=properties/edit/' . $property_id);
}
