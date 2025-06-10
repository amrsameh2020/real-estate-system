<?php
// File: app/scripts/properties/handle_create.php

/**
 * ===================================================================
 * ملف معالجة إنشاء عقار جديد (Create Property Handler)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج إضافة عقار جديد.
 * يستقبل البيانات، يقوم بالتحقق منها، يحفظها في قاعدة البيانات،
 * ثم يعيد توجيه المستخدم مع رسالة مناسبة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام للوصول إلى كل شيء
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=properties/create');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال بيانات النموذج
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

// إذا وجدت أخطاء، قم بحفظها في رسالة flash وأعد التوجيه لصفحة الإنشاء
if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    // يمكنك أيضاً حفظ المدخلات القديمة في الجلسة لإعادة تعبئة النموذج
    // Session::flash('old_input', $data);
    redirect('public/index.php?url=properties/create');
}

// الخطوة 5: محاولة حفظ البيانات في قاعدة البيانات
$new_property_id = Property::create($data);

if ($new_property_id) {
    // في حالة النجاح
    Session::flash('success', 'تمت إضافة العقار بنجاح!');
    // إعادة التوجيه إلى صفحة عرض العقار الجديد
    redirect('public/index.php?url=properties/view/' . $new_property_id);
} else {
    // في حالة الفشل
    Session::flash('error', 'حدث خطأ أثناء حفظ العقار. الرجاء المحاولة مرة أخرى.');
    redirect('public/index.php?url=properties/create');
}
