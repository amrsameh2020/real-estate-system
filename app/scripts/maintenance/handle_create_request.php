<?php
// File: app/scripts/maintenance/handle_create_request.php

/**
 * ===================================================================
 * ملف معالجة إنشاء طلب صيانة (Create Maintenance Request Handler)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج إنشاء طلب صيانة، سواء تم
 * إنشاؤه من قبل المستأجر أو مدير النظام.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
// يمكن للمستأجر أو مدير النظام إنشاء طلب
if (!Auth::check()) {
    redirect('public/index.php?url=login');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=dashboard');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال بيانات النموذج
$data = [
    'unit_id'               => filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT),
    'title'                 => Security::sanitizeInput($_POST['title'] ?? ''),
    'description'           => Security::sanitizeInput($_POST['description'] ?? ''),
    'priority'              => Security::sanitizeInput($_POST['priority'] ?? 'Medium'),
    'requested_by_tenant_id'=> Auth::user()['id'], // يتم تعيين مقدم الطلب تلقائياً
];

// الخطوة 4: التحقق من صحة المدخلات
$errors = [];
if (empty($data['unit_id'])) {
    $errors[] = 'يجب تحديد الوحدة المتعلقة بالطلب.';
}
if (empty($data['title'])) {
    $errors[] = 'عنوان الطلب مطلوب.';
}
if (empty($data['description'])) {
    $errors[] = 'وصف المشكلة مطلوب.';
}
if (!in_array($data['priority'], ['Low', 'Medium', 'High', 'Urgent'])) {
    $errors[] = 'قيمة الأولوية غير صالحة.';
}

if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    // إعادة التوجيه للصفحة السابقة
    redirect($_SERVER['HTTP_REFERER'] ?? 'public/index.php?url=dashboard');
}

// الخطوة 5: محاولة إنشاء سجل طلب الصيانة
$new_request_id = MaintenanceRequest::create($data);

if ($new_request_id) {
    // إرسال إشعار لمدير النظام (يمكن تنفيذ هذا لاحقاً)
    Session::flash('success', 'تم إرسال طلب الصيانة بنجاح. سيتم التواصل معك قريباً.');
    redirect('public/index.php?url=maintenance/view_request/' . $new_request_id);
} else {
    Session::flash('error', 'حدث خطأ أثناء إرسال طلب الصيانة.');
    redirect($_SERVER['HTTP_REFERER'] ?? 'public/index.php?url=dashboard');
}