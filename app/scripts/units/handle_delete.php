<?php
// File: app/scripts/units/handle_delete.php

/**
 * ===================================================================
 * معالج حذف وحدة (Handle Delete Unit)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لحذف وحدة عقارية موجودة.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (SystemAdmin أو Owner).
 * - يتحقق من صحة معرف الوحدة.
 * - يقوم بحذف الوحدة.
 * - يعيد التوجيه مع رسالة نجاح أو فشل.
 */

require_once __DIR__ . '/../../core/bootstrap.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::set('error_message', 'Invalid request method.');
    redirect('properties'); // إعادة توجيه عامة
}

// التحقق من توكن CSRF
if (!Security::verifyCSRF($_POST['csrf_token'])) {
    Session::set('error_message', 'CSRF token validation failed.');
    redirect('properties');
}

// التحقق من صلاحيات المستخدم (يجب أن يكون مسؤول نظام أو مالك)
Auth::requireRole(['SystemAdmin', 'Owner']);

$errors = [];

// جلب وتصفية معرف الوحدة
$unit_id = filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT);
$property_id_redirect = filter_input(INPUT_POST, 'property_id_redirect', FILTER_VALIDATE_INT); // لعنوان URL لإعادة التوجيه بعد الحذف

// التحقق من صحة معرف الوحدة
if (empty($unit_id)) {
    $errors[] = 'معرف الوحدة مطلوب للحذف.';
}

// إذا لم تكن هناك أخطاء، قم بحذف الوحدة
if (empty($errors)) {
    $unitModel = new Unit();

    // اختياري: تحقق من أن الوحدة تنتمي للعقار الذي يملكه المالك الحالي (إذا كان مالكاً)
    if (Auth::getUserRole() === 'Owner') {
        $unit_data = $unitModel->getUnitById($unit_id);
        if (!$unit_data) {
            $errors[] = 'الوحدة غير موجودة.';
        } else {
            $propertyModel = new Property();
            $property_data = $propertyModel->getPropertyById($unit_data['property_id']);
            if (!$property_data || $property_data['owner_id'] !== Auth::getUserId()) {
                $errors[] = 'ليس لديك صلاحية لحذف هذه الوحدة.';
            }
            // تحديث property_id_redirect لضمان العودة للعقار الصحيح
            $property_id_redirect = $unit_data['property_id'];
        }
    }

    if (empty($errors)) {
        // قبل الحذف، قد تحتاج إلى التحقق من وجود عقود إيجار نشطة مرتبطة بهذه الوحدة
        // إذا كان هناك، قد تحتاج إلى منع الحذف أو إلغاء تنشيط العقود أولاً
        $leaseModel = new Lease();
        $activeLeases = $leaseModel->getActiveLeasesByUnitId($unit_id); // افترض وجود هذه الدالة
        if (!empty($activeLeases)) {
            $errors[] = 'لا يمكن حذف الوحدة لوجود عقود إيجار نشطة مرتبطة بها.';
        } else {
            if ($unitModel->delete($unit_id)) {
                Session::set('success_message', 'تم حذف الوحدة بنجاح.');
                // إعادة التوجيه إلى صفحة العقار الذي كانت تتبعه الوحدة
                redirect('properties/view&id=' . ($property_id_redirect ?? ''));
            } else {
                Session::set('error_message', 'حدث خطأ أثناء حذف الوحدة أو الوحدة غير موجودة.');
                redirect('properties/view&id=' . ($property_id_redirect ?? ''));
            }
        }
    }
}

// في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم
if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    redirect('properties/view&id=' . ($property_id_redirect ?? ''));
}