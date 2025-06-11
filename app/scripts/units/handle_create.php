<?php
// File: app/scripts/units/handle_create.php

/**
 * ===================================================================
 * معالج إنشاء وحدة جديدة (Handle Create New Unit)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لإنشاء وحدة عقارية جديدة.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (SystemAdmin أو Owner).
 * - يقوم بالتحقق من صحة المدخلات.
 * - يقوم بإنشاء الوحدة.
 * - يعيد التوجيه مع رسالة نجاح أو فشل.
 */

require_once __DIR__ . '/../../core/bootstrap.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::set('error_message', 'Invalid request method.');
    redirect('properties'); // يمكن إعادة التوجيه إلى قائمة العقارات
}

// التحقق من توكن CSRF
if (!Security::verifyCSRF($_POST['csrf_token'])) {
    Session::set('error_message', 'CSRF token validation failed.');
    redirect('properties');
}

// التحقق من صلاحيات المستخدم (يجب أن يكون مسؤول نظام أو مالك)
Auth::requireRole(['SystemAdmin', 'Owner']);

$errors = [];

// جلب وتصفية المدخلات
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
$unit_number = filter_input(INPUT_POST, 'unit_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$rent_amount = filter_input(INPUT_POST, 'rent_amount', FILTER_VALIDATE_FLOAT);
$number_of_bedrooms = filter_input(INPUT_POST, 'number_of_bedrooms', FILTER_VALIDATE_INT);
$number_of_bathrooms = filter_input(INPUT_POST, 'number_of_bathrooms', FILTER_VALIDATE_INT);
$size_sqm = filter_input(INPUT_POST, 'size_sqm', FILTER_VALIDATE_FLOAT);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// التحقق من صحة المدخلات
if (!$property_id) {
    $errors[] = 'يجب ربط الوحدة بعقار موجود.';
} else {
    // التحقق من وجود العقار ومن أن المالك الحالي يملكه (إذا كان مالكاً)
    $propertyModel = new Property();
    $property = $propertyModel->getPropertyById($property_id);
    if (!$property) {
        $errors[] = 'العقار المحدد غير موجود.';
    } elseif (Auth::getUserRole() === 'Owner' && $property['owner_id'] !== Auth::getUserId()) {
        $errors[] = 'ليس لديك صلاحية لإضافة وحدات لهذا العقار.';
    }
}

if (empty($unit_number)) {
    $errors[] = 'رقم الوحدة مطلوب.';
}
if (empty($type)) {
    $errors[] = 'نوع الوحدة مطلوب.';
}
if ($rent_amount === false || $rent_amount <= 0) {
    $errors[] = 'مبلغ الإيجار يجب أن يكون رقماً موجباً.';
}
if ($number_of_bedrooms === false || $number_of_bedrooms < 0) {
    $errors[] = 'عدد غرف النوم يجب أن يكون رقماً صحيحاً موجباً.';
}
if ($number_of_bathrooms === false || $number_of_bathrooms < 0) {
    $errors[] = 'عدد الحمامات يجب أن يكون رقماً صحيحاً موجباً.';
}
if ($size_sqm === false || $size_sqm <= 0) {
    $errors[] = 'المساحة بالمتر المربع يجب أن تكون رقماً موجباً.';
}
$valid_statuses = ['Vacant', 'Occupied', 'Under Maintenance', 'Unavailable'];
if (empty($status) || !in_array($status, $valid_statuses)) {
    $errors[] = 'حالة وحدة صالحة مطلوبة.';
}

// إذا لم تكن هناك أخطاء، قم بإنشاء الوحدة
if (empty($errors)) {
    $unitModel = new Unit();

    // التحقق مما إذا كان رقم الوحدة موجودًا بالفعل في هذا العقار
    if ($unitModel->getUnitByPropertyIdAndNumber($property_id, $unit_number)) { // افترض وجود هذه الدالة
        $errors[] = 'رقم الوحدة هذا موجود بالفعل في العقار المحدد.';
    } else {
        $unitData = [
            'property_id' => $property_id,
            'unit_number' => $unit_number,
            'type' => $type,
            'rent_amount' => $rent_amount,
            'number_of_bedrooms' => $number_of_bedrooms,
            'number_of_bathrooms' => $number_of_bathrooms,
            'size_sqm' => $size_sqm,
            'status' => $status,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($unitModel->create($unitData)) {
            Session::set('success_message', 'تم إنشاء الوحدة بنجاح.');
            redirect('properties/view&id=' . $property_id); // العودة إلى صفحة عرض العقار
        } else {
            Session::set('error_message', 'حدث خطأ أثناء إنشاء الوحدة.');
            redirect('units/create&property_id=' . $property_id);
        }
    }
}

// في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم إلى صفحة الإنشاء
if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    Session::set('form_data', $_POST); // للحفاظ على بيانات النموذج
    redirect('units/create&property_id=' . ($property_id ?? ''));
}