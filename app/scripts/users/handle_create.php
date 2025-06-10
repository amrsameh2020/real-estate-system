<?php
// File: app/scripts/users/handle_create.php

/**
 * ===================================================================
 * معالج إنشاء مستخدم جديد (Handle Create New User)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لإنشاء مستخدم جديد في النظام.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (SystemAdmin).
 * - يقوم بالتحقق من صحة المدخلات.
 * - يقوم بإنشاء المستخدم وتشفير كلمة المرور.
 * - يعيد التوجيه مع رسالة نجاح أو فشل.
 */

require_once __DIR__ . '/../../core/bootstrap.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::set('error_message', 'Invalid request method.');
    redirect('users');
}

// التحقق من توكن CSRF
if (!Security::verifyCSRF($_POST['csrf_token'])) {
    Session::set('error_message', 'CSRF token validation failed.');
    redirect('users');
}

// التحقق من صلاحيات المستخدم (يجب أن يكون مسؤول نظام)
Auth::requireRole('SystemAdmin');

$errors = [];

// جلب وتصفية المدخلات
$full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // إضافة رقم الهاتف
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // إضافة العنوان

// التحقق من صحة المدخلات
if (empty($full_name)) {
    $errors[] = 'الاسم الكامل مطلوب.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'بريد إلكتروني صالح مطلوب.';
}
if (empty($password)) {
    $errors[] = 'كلمة المرور مطلوبة.';
} elseif (strlen($password) < 6) {
    $errors[] = 'يجب أن تتكون كلمة المرور من 6 أحرف على الأقل.';
}
if ($password !== $confirm_password) {
    $errors[] = 'كلمة المرور وتأكيد كلمة المرور غير متطابقين.';
}
if (empty($role)) {
    $errors[] = 'الدور مطلوب.';
}
// يمكن إضافة تحقق إضافي على رقم الهاتف والعنوان إذا لزم الأمر

// إذا لم تكن هناك أخطاء، قم بإنشاء المستخدم
if (empty($errors)) {
    $userModel = new User();

    // التحقق مما إذا كان البريد الإلكتروني موجودًا بالفعل
    if ($userModel->getUserByEmail($email)) {
        $errors[] = 'هذا البريد الإلكتروني مسجل بالفعل.';
    } else {
        // تشفير كلمة المرور
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // إنشاء المستخدم
        $userData = [
            'full_name' => $full_name,
            'email' => $email,
            'password' => $hashed_password,
            'role' => $role,
            'phone_number' => $phone_number,
            'address' => $address,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($userModel->create($userData)) {
            Session::set('success_message', 'تم إنشاء المستخدم بنجاح.');
            redirect('users');
        } else {
            Session::set('error_message', 'حدث خطأ أثناء إنشاء المستخدم.');
            redirect('users/create');
        }
    }
}

// في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم إلى صفحة الإنشاء
if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    Session::set('form_data', $_POST); // للحفاظ على بيانات النموذج
    redirect('users/create');
}