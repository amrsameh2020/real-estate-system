<?php
// File: app/scripts/users/handle_edit.php

/**
 * ===================================================================
 * معالج تعديل المستخدم (Handle Edit User)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لتعديل معلومات مستخدم موجود.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (SystemAdmin).
 * - يقوم بالتحقق من صحة المدخلات.
 * - يقوم بتحديث معلومات المستخدم.
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
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$password = $_POST['password'] ?? ''; // قد تكون فارغة إذا لم يتم تغيير كلمة المرور
$confirm_password = $_POST['confirm_password'] ?? '';

// التحقق من صحة المدخلات الأساسية
if (empty($user_id)) {
    $errors[] = 'معرف المستخدم مطلوب.';
}
if (empty($full_name)) {
    $errors[] = 'الاسم الكامل مطلوب.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'بريد إلكتروني صالح مطلوب.';
}
if (empty($role)) {
    $errors[] = 'الدور مطلوب.';
}

// إذا تم إدخال كلمة مرور جديدة، تحقق منها
if (!empty($password)) {
    if (strlen($password) < 6) {
        $errors[] = 'يجب أن تتكون كلمة المرور الجديدة من 6 أحرف على الأقل.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'كلمة المرور الجديدة وتأكيد كلمة المرور غير متطابقين.';
    }
}

// إذا لم تكن هناك أخطاء، قم بتحديث المستخدم
if (empty($errors)) {
    $userModel = new User();

    // جلب بيانات المستخدم الحالي للتحقق من البريد الإلكتروني
    $currentUser = $userModel->getUserById($user_id);
    if (!$currentUser) {
        Session::set('error_message', 'المستخدم غير موجود.');
        redirect('users');
    }

    // التحقق مما إذا كان البريد الإلكتروني قد تغير وإذا كان موجودًا بالفعل لمستخدم آخر
    if ($email !== $currentUser['email']) {
        if ($userModel->getUserByEmail($email)) {
            $errors[] = 'هذا البريد الإلكتروني مسجل بالفعل لمستخدم آخر.';
        }
    }

    if (empty($errors)) {
        $userData = [
            'full_name' => $full_name,
            'email' => $email,
            'role' => $role,
            'phone_number' => $phone_number,
            'address' => $address,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // إذا تم إدخال كلمة مرور جديدة، قم بتشفيرها وإضافتها إلى البيانات
        if (!empty($password)) {
            $userData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($userModel->update($user_id, $userData)) {
            Session::set('success_message', 'تم تحديث معلومات المستخدم بنجاح.');
            redirect('users');
        } else {
            Session::set('error_message', 'حدث خطأ أثناء تحديث المستخدم.');
            redirect('users/edit&id=' . $user_id);
        }
    }
}

// في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم إلى صفحة التعديل
if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    Session::set('form_data', $_POST); // للحفاظ على بيانات النموذج
    // تأكد من تمرير معرف المستخدم بشكل صحيح عند إعادة التوجيه
    redirect('users/edit&id=' . ($user_id ?? ''));
}