<?php
// File: app/scripts/properties/handle_delete.php

/**
 * ===================================================================
 * ملف معالجة حذف عقار (Delete Property Handler)
 * ===================================================================
 * هذا الملف مسؤول عن معالجة طلب حذف عقار معين.
 * يستقبل معرف العقار، يتحقق من الصلاحيات، يقوم بحذفه من قاعدة البيانات،
 * ثم يعيد توجيه المستخدم إلى قائمة العقارات مع رسالة تأكيد.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام للوصول إلى كل شيء
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

// هذا الملف يجب أن يستقبل الطلب عبر GET أو POST
// للتأكيد عبر رابط، سنستخدم GET. في بيئة إنتاجية، يفضل استخدام POST مع توكن CSRF.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    redirect('public/index.php?url=properties');
}

// الخطوة 3: تنظيف واستقبال معرف العقار
$property_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// الخطوة 4: التحقق من صحة المعرف
if ($property_id === false || $property_id === null) {
    Session::flash('error', 'معرف العقار غير صالح أو مفقود.');
    redirect('public/index.php?url=properties');
}

// قبل الحذف، من الجيد التحقق مما إذا كان العقار موجوداً
$property = Property::findById($property_id);
if (!$property) {
    Session::flash('error', 'العقار الذي تحاول حذفه غير موجود.');
    redirect('public/index.php?url=properties');
}

// يمكنك إضافة منطق إضافي هنا، مثل التحقق من عدم وجود عقود إيجار نشطة في هذا العقار قبل حذفه

// الخطوة 5: محاولة حذف العقار من قاعدة البيانات
$success = Property::delete($property_id);

if ($success) {
    // في حالة النجاح
    Session::flash('success', 'تم حذف العقار "' . $property['name'] . '" بنجاح.');
} else {
    // في حالة الفشل
    Session::flash('error', 'حدث خطأ أثناء حذف العقار. قد يكون مرتبطاً بسجلات أخرى.');
}

// الخطوة 6: إعادة التوجيه إلى قائمة العقارات
redirect('public/index.php?url=properties');
