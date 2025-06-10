<?php
// File: app/scripts/finance/handle_generate_report.php

/**
 * ===================================================================
 * معالج إنشاء تقرير مالي (Handle Generate Financial Report)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لإنشاء تقارير مالية بناءً على المعايير المحددة.
 * يمكن أن يكون هذا التقرير عبارة عن ملخص للمصروفات، الإيرادات، أو أرباح الملاك.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (Accountant أو SystemAdmin).
 * - يقوم بجمع البيانات اللازمة وتنسيقها.
 * - يمكنه توليد ملف PDF أو CSV أو عرض البيانات مباشرة.
 * (لأغراض هذا المثال، سيكتفي بعرض رسالة نجاح).
 */

require_once __DIR__ . '/../../core/bootstrap.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::set('error_message', 'Invalid request method.');
    redirect('finance/reports');
}

// التحقق من توكن CSRF
if (!Security::verifyCSRF($_POST['csrf_token'])) {
    Session::set('error_message', 'CSRF token validation failed.');
    redirect('finance/reports');
}

// التحقق من صلاحيات المستخدم (يجب أن يكون محاسب أو مسؤول نظام)
Auth::requireRole(['Accountant', 'SystemAdmin']);

$errors = [];

// جلب وتصفية المدخلات
$report_type = filter_input(INPUT_POST, 'report_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT); // اختياري للتقارير الخاصة بالعقار

// التحقق من صحة المدخلات
if (empty($report_type)) {
    $errors[] = 'نوع التقرير مطلوب.';
}
if (empty($start_date) || !strtotime($start_date)) {
    $errors[] = 'تاريخ بدء صالح مطلوب.';
}
if (empty($end_date) || !strtotime($end_date)) {
    $errors[] = 'تاريخ انتهاء صالح مطلوب.';
}
if (strtotime($start_date) > strtotime($end_date)) {
    $errors[] = 'تاريخ البدء يجب أن يكون قبل أو يساوي تاريخ الانتهاء.';
}

$report_data = [];
$report_title = '';

if (empty($errors)) {
    $expenseModel = new Expense();
    $invoiceModel = new Invoice();
    $paymentModel = new Payment();
    $userModel = new User(); // لربط الملاك بالتقارير

    switch ($report_type) {
        case 'expenses':
            $report_title = 'تقرير المصروفات';
            // جلب المصروفات ضمن النطاق الزمني المحدد، ويمكن تصفيتها حسب العقار
            $report_data = $expenseModel->getExpensesByDateRange($start_date, $end_date, $property_id); // افترض وجود هذه الدالة
            break;
        case 'income':
            $report_title = 'تقرير الإيرادات';
            // جلب الدفعات ضمن النطاق الزمني المحدد، ويمكن تصفيتها حسب العقار/الوحدة
            $report_data = $paymentModel->getPaymentsByDateRange($start_date, $end_date, $property_id); // افترض وجود هذه الدالة
            break;
        case 'owner_payouts':
            $report_title = 'تقرير مدفوعات الملاك';
            // هذا النوع من التقرير قد يتطلب منطقاً معقداً يجمع بين الإيرادات والمصروفات لكل مالك
            // لغرض هذا المثال، سنفترض أنه يجلب ملخصات مدفوعات الملاك
            $owner_id = Auth::getUserId(); // إذا كان المالك هو من يطلب التقرير
            $report_data = $invoiceModel->getOwnerPayoutsReport($owner_id, $start_date, $end_date); // افترض وجود هذه الدالة
            break;
        case 'tenant_invoices':
            $report_title = 'تقرير فواتير المستأجرين';
            // جلب فواتير المستأجرين ضمن النطاق الزمني
            $report_data = $invoiceModel->getInvoicesByDateRange($start_date, $end_date, $property_id); // افترض وجود هذه الدالة
            break;
        default:
            $errors[] = 'نوع تقرير غير معروف.';
            break;
    }
}

if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    Session::set('form_data', $_POST);
    redirect('finance/reports');
} else {
    // هنا يمكنك اختيار كيفية عرض التقرير
    // 1. تخزين البيانات في الجلسة وإعادة التوجيه لصفحة عرض التقرير
    Session::set('report_results', [
        'title' => $report_title,
        'type' => $report_type,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'data' => $report_data
    ]);
    Session::set('success_message', 'تم توليد التقرير بنجاح.');
    redirect('finance/reports'); // إعادة التوجيه إلى نفس الصفحة لعرض النتائج

    // 2. أو، إذا كنت ترغب في توليد ملف PDF/CSV، يمكنك إضافة منطق هنا
    //    مثل استخدام مكتبات مثل FPDF أو TCPDF لإنشاء PDF، أو fputcsv لإنشاء CSV.
    //    يجب أن يتم إرسال الرؤوس المناسبة لتنزيل الملف.
}