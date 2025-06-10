<?php
// File: app/scripts/finance/handle_record_payment.php

/**
 * ===================================================================
 * معالج تسجيل دفعة (Handle Record Payment)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لتسجيل دفعة جديدة مرتبطة بفاتورة.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (Accountant أو SystemAdmin).
 * - يقوم بالتحقق من صحة المدخلات.
 * - يسجل الدفعة ويحدث حالة الفاتورة (Paid/Partial).
 * - يعيد التوجيه مع رسالة نجاح أو فشل.
 */

require_once __DIR__ . '/../../core/bootstrap.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::set('error_message', 'Invalid request method.');
    // افتراض أننا سنعود إلى صفحة الفواتير أو صفحة الدفعة إذا كانت موجودة
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
$invoice_id = filter_input(INPUT_POST, 'invoice_id', FILTER_VALIDATE_INT);
$payment_amount = filter_input(INPUT_POST, 'payment_amount', FILTER_VALIDATE_FLOAT);
$payment_date = filter_input(INPUT_POST, 'payment_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// التحقق من صحة المدخلات
if (!$invoice_id) {
    $errors[] = 'معرف الفاتورة مطلوب.';
}
if ($payment_amount === false || $payment_amount <= 0) {
    $errors[] = 'مبلغ الدفعة يجب أن يكون رقماً موجباً.';
}
if (empty($payment_date) || !strtotime($payment_date)) {
    $errors[] = 'تاريخ دفعة صالح مطلوب.';
}
if (empty($payment_method)) {
    $errors[] = 'طريقة الدفع مطلوبة.';
}

// إذا لم تكن هناك أخطاء، قم بتسجيل الدفعة وتحديث الفاتورة
if (empty($errors)) {
    $invoiceModel = new Invoice();
    $paymentModel = new Payment();

    // جلب تفاصيل الفاتورة
    $invoice = $invoiceModel->getInvoiceById($invoice_id);

    if (!$invoice) {
        $errors[] = 'الفاتورة غير موجودة.';
    } else {
        // حساب المبلغ المدفوع بالفعل للفاتورة
        $total_paid_on_invoice = $paymentModel->getTotalPaidForInvoice($invoice_id); // افترض وجود هذه الدالة

        $new_total_paid = $total_paid_on_invoice + $payment_amount;
        $remaining_amount = $invoice['amount'] - $new_total_paid;

        $new_invoice_status = 'Unpaid';
        if ($new_total_paid >= $invoice['amount']) {
            $new_invoice_status = 'Paid';
        } elseif ($new_total_paid > 0) {
            $new_invoice_status = 'Partial';
        }

        // إنشاء الدفعة
        $paymentData = [
            'invoice_id' => $invoice_id,
            'amount' => $payment_amount,
            'payment_date' => $payment_date,
            'payment_method' => $payment_method,
            'notes' => $notes,
            'recorded_by_user_id' => Auth::getUserId(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // بدء عملية المعاملة (transaction) لضمان اتساق البيانات
        // (افتراض أن موديل قاعدة البيانات يدعم المعاملات)
        $database = new Database(); // تحتاج إلى وصول إلى كائن قاعدة البيانات
        $conn = $database->connect();
        $conn->beginTransaction();

        try {
            if (!$paymentModel->create($paymentData)) {
                throw new Exception('فشل في تسجيل الدفعة.');
            }

            // تحديث حالة الفاتورة والمبلغ المتبقي
            $invoiceUpdateData = [
                'status' => $new_invoice_status,
                'amount_due' => max(0, $remaining_amount), // المبلغ المستحق لا يمكن أن يكون سالباً
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!$invoiceModel->update($invoice_id, $invoiceUpdateData)) {
                throw new Exception('فشل في تحديث حالة الفاتورة.');
            }

            $conn->commit();
            Session::set('success_message', 'تم تسجيل الدفعة بنجاح وتحديث الفاتورة.');
            redirect('finance/view_invoice&id=' . $invoice_id); // العودة إلى عرض الفاتورة
        } catch (Exception $e) {
            $conn->rollBack();
            Session::set('error_message', 'حدث خطأ: ' . $e->getMessage());
            redirect('finance/view_invoice&id=' . $invoice_id); // العودة إلى عرض الفاتورة مع رسالة خطأ
        }
    }
}

// في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم
if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    // في حالة وجود أخطاء، قد نرغب في إعادة التوجيه إلى نفس صفحة الفاتورة
    redirect('finance/view_invoice&id=' . ($invoice_id ?? ''));
}