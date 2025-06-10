<?php
// File: app/scripts/finance/handle_create_invoice.php

/**
 * ===================================================================
 * معالج إنشاء فاتورة جديدة (Handle Create New Invoice)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لإنشاء فاتورة جديدة في النظام.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (Accountant أو SystemAdmin).
 * - يقوم بالتحقق من صحة المدخلات.
 * - يقوم بإنشاء الفاتورة.
 * - يعيد التوجيه مع رسالة نجاح أو فشل.
 */

require_once __DIR__ . '/../../core/bootstrap.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::set('error_message', 'Invalid request method.');
    redirect('finance/invoices');
}

// التحقق من توكن CSRF
if (!Security::verifyCSRF($_POST['csrf_token'])) {
    Session::set('error_message', 'CSRF token validation failed.');
    redirect('finance/invoices');
}

// التحقق من صلاحيات المستخدم (يجب أن يكون محاسب أو مسؤول نظام)
Auth::requireRole(['Accountant', 'SystemAdmin']);

$errors = [];

// جلب وتصفية المدخلات
$tenant_id = filter_input(INPUT_POST, 'tenant_id', FILTER_VALIDATE_INT);
$unit_id = filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT); // يمكن أن يكون فارغاً
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$due_date = filter_input(INPUT_POST, 'due_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// التحقق من صحة المدخلات
if (!$tenant_id) {
    $errors[] = 'يجب اختيار مستأجر.';
}
if (empty($description)) {
    $errors[] = 'وصف الفاتورة مطلوب.';
}
if ($amount === false || $amount <= 0) {
    $errors[] = 'المبلغ الإجمالي يجب أن يكون رقماً موجباً.';
}
if (empty($due_date) || !strtotime($due_date)) {
    $errors[] = 'تاريخ استحقاق صالح مطلوب.';
}
$valid_statuses = ['Unpaid', 'Partial', 'Paid', 'Cancelled'];
if (empty($status) || !in_array($status, $valid_statuses)) {
    $errors[] = 'حالة فاتورة صالحة مطلوبة.';
}

// إذا لم تكن هناك أخطاء، قم بإنشاء الفاتورة
if (empty($errors)) {
    $invoiceModel = new Invoice();

    $invoiceData = [
        'tenant_id' => $tenant_id,
        'description' => $description,
        'amount' => $amount,
        'due_date' => $due_date,
        'status' => $status,
        'created_by_user_id' => Auth::getUserId(), // من قام بإنشاء الفاتورة
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // إضافة unit_id إذا تم تحديده
    if ($unit_id !== false && $unit_id > 0) {
        $invoiceData['unit_id'] = $unit_id;
    } else {
        $invoiceData['unit_id'] = null; // تأكد من أنه null إذا لم يتم تحديده
    }

    if ($invoiceModel->create($invoiceData)) {
        Session::set('success_message', 'تم إنشاء الفاتورة بنجاح.');
        redirect('finance/invoices');
    } else {
        Session::set('error_message', 'حدث خطأ أثناء إنشاء الفاتورة.');
        redirect('finance/create_invoice');
    }
}

// في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم إلى صفحة الإنشاء
if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    Session::set('form_data', $_POST); // للحفاظ على بيانات النموذج
    redirect('finance/create_invoice');
}