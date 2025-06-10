<?php
// File: app/scripts/users/handle_delete.php

/**
 * ===================================================================
 * معالج حذف المستخدم (Handle Delete User)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لحذف مستخدم موجود.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (SystemAdmin).
 * - يتحقق من صحة معرف المستخدم.
 * - يقوم بحذف المستخدم.
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

// جلب وتصفية معرف المستخدم
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

// التحقق من صحة معرف المستخدم
if (empty($user_id)) {
    $errors[] = 'معرف المستخدم مطلوب للحذف.';
}

// التحقق من أن المستخدم لا يحاول حذف نفسه
if ($user_id === Auth::getUserId()) {
    $errors[] = 'لا يمكنك حذف حسابك الخاص.';
}

// إذا لم تكن هناك أخطاء، قم بحذف المستخدم
if (empty($errors)) {
    $userModel = new User();

    if ($userModel->delete($user_id)) {
        Session::set('success_message', 'تم حذف المستخدم بنجاح.');
        redirect('users');
    } else {
        Session::set('error_message', 'حدث خطأ أثناء حذف المستخدم أو المستخدم غير موجود.');
        redirect('users');
    }
}

// في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم
if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    redirect('users');
}