<?php
// File: app/scripts/maintenance/handle_update_status.php

/**
 * ===================================================================
 * ملف معالجة تحديث حالة طلب صيانة
 * ===================================================================
 * هذا الملف مسؤول عن تحديث حالة طلب صيانة (مثلاً من "جديد" إلى "مكتمل").
 * يتم استدعاؤه عادة عبر طلب AJAX أو رابط.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // يفضل استخدام POST لتغيير البيانات
    redirect('public/index.php?url=maintenance/board');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال البيانات
$request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
$new_status = Security::sanitizeInput($_POST['status'] ?? '');

// الخطوة 4: التحقق من صحة المدخلات
$allowed_statuses = ['New', 'Assigned', 'InProgress', 'Completed', 'Cancelled'];
if (!$request_id || empty($new_status) || !in_array($new_status, $allowed_statuses)) {
    Session::flash('error', 'بيانات غير صالحة لتحديث حالة الطلب.');
    redirect($_SERVER['HTTP_REFERER'] ?? 'public/index.php?url=maintenance/board');
}

// الخطوة 5: محاولة تحديث الحالة
$success = MaintenanceRequest::updateStatus($request_id, $new_status);

if ($success) {
    // يمكن إضافة سجل في audit_logs هنا
    Session::flash('success', 'تم تحديث حالة طلب الصيانة بنجاح.');
} else {
    Session::flash('error', 'حدث خطأ أثناء تحديث حالة الطلب.');
}

// الخطوة 6: إعادة التوجيه للصفحة السابقة
redirect($_SERVER['HTTP_REFERER'] ?? 'public/index.php?url=maintenance/board');
