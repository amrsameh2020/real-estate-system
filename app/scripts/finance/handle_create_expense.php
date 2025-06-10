<?php
// File: app/scripts/finance/handle_create_expense.php

/**
 * ===================================================================
 * معالج إنشاء مصروف جديد (Handle Create New Expense)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لتسجيل مصروف جديد في النظام.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (Accountant أو SystemAdmin).
 * - يقوم بالتحقق من صحة المدخلات.
 * - يقوم بإنشاء المصروف.
 * - يعيد التوجيه مع رسالة نجاح أو فشل.
 */

require_once __DIR__ . '/../../core/bootstrap.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::set('error_message', 'Invalid request method.');
    redirect('finance/expenses');
}

// التحقق من توكن CSRF
if (!Security::verifyCSRF($_POST['csrf_token'])) {
    Session::set('error_message', 'CSRF token validation failed.');
    redirect('finance/expenses');
}

// التحقق من صلاحيات المستخدم (يجب أن يكون محاسب أو مسؤول نظام)
Auth::requireRole(['Accountant', 'SystemAdmin']);

$errors = [];

// جلب وتصفية المدخلات
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$expense_date = filter_input(INPUT_POST, 'expense_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // تاريخ
$category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT); // يمكن أن يكون فارغاً أو 0 إذا لم يكن مرتبطًا بعقار

// التحقق من صحة المدخلات
if (empty($description)) {
    $errors[] = 'الوصف مطلوب.';
}
if ($amount === false || $amount <= 0) {
    $errors[] = 'المبلغ يجب أن يكون رقماً موجباً.';
}
if (empty($expense_date) || !strtotime($expense_date)) {
    $errors[] = 'تاريخ مصروف صالح مطلوب.';
}
if (empty($category)) {
    $errors[] = 'الفئة مطلوبة.';
}

// إذا لم تكن هناك أخطاء، قم بإنشاء المصروف
if (empty($errors)) {
    $expenseModel = new Expense();

    $expenseData = [
        'description' => $description,
        'amount' => $amount,
        'expense_date' => $expense_date,
        'category' => $category,
        'created_by_user_id' => Auth::getUserId(), // تسجيل من قام بإنشاء المصروف
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // إضافة property_id إذا تم تحديده
    if ($property_id !== false && $property_id > 0) {
        $expenseData['property_id'] = $property_id;
    } else {
        $expenseData['property_id'] = null; // تأكد من أنه null إذا لم يتم تحديده
    }

    if ($expenseModel->create($expenseData)) {
        Session::set('success_message', 'تم تسجيل المصروف بنجاح.');
        redirect('finance/expenses');
    } else {
        Session::set('error_message', 'حدث خطأ أثناء تسجيل المصروف.');
        redirect('finance/create_expense');
    }
}

// في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم إلى صفحة الإنشاء
if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    Session::set('form_data', $_POST); // للحفاظ على بيانات النموذج
    redirect('finance/create_expense');
}