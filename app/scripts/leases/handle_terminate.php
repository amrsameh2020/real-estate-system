<?php
// File: app/scripts/leases/handle_terminate.php

/**
 * ===================================================================
 * ملف معالجة إنهاء عقد إيجار (Terminate Lease Handler)
 * ===================================================================
 * هذا الملف مسؤول عن معالجة طلب إنهاء عقد إيجار نشط قبل تاريخ انتهاءه.
 * يستقبل معرف العقد، يتحقق من الصلاحيات، يقوم بتغيير حالة العقد إلى "منتهي"
 * وحالة الوحدة إلى "شاغرة"، ثم يعيد توجيه المستخدم.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

// هذا الملف يجب أن يستقبل الطلب عبر GET أو POST
// للتسهيل، سنستخدم GET مع معرف العقد. في بيئة إنتاجية، يفضل استخدام POST مع توكن CSRF.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    redirect('public/index.php?url=leases');
}

// الخطوة 3: تنظيف واستقبال معرف العقد
$lease_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// الخطوة 4: التحقق من صحة المعرف والعقد
if (!$lease_id) {
    Session::flash('error', 'معرف العقد غير صالح أو مفقود.');
    redirect('public/index.php?url=leases');
}

$lease = Lease::findById($lease_id);
if (!$lease) {
    Session::flash('error', 'العقد الذي تحاول إنهاءه غير موجود.');
    redirect('public/index.php?url=leases');
}

if ($lease['status'] !== 'Active') {
    Session::flash('error', 'يمكن إنهاء العقود النشطة فقط.');
    redirect('public/index.php?url=leases/view/' . $lease_id);
}


// الخطوة 5: بدء معاملة (Transaction) لضمان تنفيذ العمليتين معاً
$db = Database::getInstance()->pdo;
try {
    $db->beginTransaction();

    // 5.1: تحديث حالة العقد إلى "Terminated"
    // نستخدم دالة updateStatus التي تقوم بتحديث حالة الوحدة أيضاً
    $success = Lease::updateStatus($lease_id, 'Terminated');

    if (!$success) {
        throw new Exception("فشل تحديث حالة العقد.");
    }
    
    // يمكنك إضافة منطق إضافي هنا، مثل:
    // - حساب أي تسويات مالية.
    // - إلغاء الفواتير المستقبلية غير المدفوعة لهذا العقد.

    // 5.2: تأكيد جميع العمليات
    $db->commit();

    // الخطوة 6: إعادة التوجيه مع رسالة نجاح
    Session::flash('success', 'تم إنهاء العقد #' . $lease_id . ' بنجاح.');
    redirect('public/index.php?url=leases/view/' . $lease_id);

} catch (Exception $e) {
    // في حالة حدوث أي خطأ، يتم التراجع عن جميع التغييرات
    $db->rollBack();
    Session::flash('error', 'حدث خطأ أثناء إنهاء العقد: ' . $e->getMessage());
    redirect('public/index.php?url=leases/view/' . $lease_id);
}
