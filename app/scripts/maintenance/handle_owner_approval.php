<?php
// File: app/scripts/maintenance/handle_owner_approval.php

/**
 * ===================================================================
 * معالج موافقة/رفض المالك لطلب الصيانة (Handle Owner Approval/Rejection for Maintenance Request)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST من المالك للموافقة على أو رفض
 * طلب صيانة يتطلب موافقته.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (Owner أو SystemAdmin).
 * - يتحقق من صحة معرف الطلب والإجراء المطلوب.
 * - يقوم بتحديث حالة طلب الصيانة.
 * - يعيد التوجيه مع رسالة نجاح أو فشل.
 */

require_once __DIR__ . '/../../core/bootstrap.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::set('error_message', 'Invalid request method.');
    redirect('owner/approvals');
}

// التحقق من توكن CSRF
if (!Security::verifyCSRF($_POST['csrf_token'])) {
    Session::set('error_message', 'CSRF token validation failed.');
    redirect('owner/approvals');
}

// التحقق من صلاحيات المستخدم (يجب أن يكون مالك أو مسؤول نظام)
Auth::requireRole(['Owner', 'SystemAdmin']);

$errors = [];

// جلب وتصفية المدخلات
$request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
$action_type = filter_input(INPUT_POST, 'action_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // 'approve' or 'reject'

// التحقق من صحة المدخلات
if (!$request_id) {
    $errors[] = 'معرف طلب الصيانة مطلوب.';
}
if (empty($action_type) || !in_array($action_type, ['approve', 'reject'])) {
    $errors[] = 'نوع الإجراء غير صالح (يجب أن يكون موافقة أو رفض).';
}

// إذا لم تكن هناك أخطاء، قم بتحديث حالة طلب الصيانة
if (empty($errors)) {
    $maintenanceRequestModel = new MaintenanceRequest();

    // جلب طلب الصيانة للتحقق من وجوده وصلاحيات المالك
    $request = $maintenanceRequestModel->getRequestById($request_id);

    if (!$request) {
        $errors[] = 'طلب الصيانة غير موجود.';
    } else {
        // التحقق من أن الطلب بالفعل في حالة "معلقة للموافقة" أو حالة تتطلب موافقة
        if ($request['status'] !== 'Pending Owner Approval' && $request['status'] !== 'Pending') { // يمكن تعديل الحالة وفقاً لمنطقك
            $errors[] = 'طلب الصيانة ليس في حالة تتطلب موافقة المالك.';
        } else {
            // التحقق من أن المالك الحالي هو مالك العقار المرتبط بطلب الصيانة (إذا كان مالكاً)
            if (Auth::getUserRole() === 'Owner') {
                $unitModel = new Unit();
                $unit_data = $unitModel->getUnitById($request['unit_id']);
                if (!$unit_data) {
                    $errors[] = 'الوحدة المرتبطة بطلب الصيانة غير موجودة.';
                } else {
                    $propertyModel = new Property();
                    $property_data = $propertyModel->getPropertyById($unit_data['property_id']);
                    if (!$property_data || $property_data['owner_id'] !== Auth::getUserId()) {
                        $errors[] = 'ليس لديك صلاحية للتعامل مع طلب الصيانة هذا.';
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        $new_status = '';
        $success_message = '';
        $error_message = '';

        if ($action_type === 'approve') {
            $new_status = 'Approved'; // يمكن أن يكون "In Progress" مباشرة
            $success_message = 'تمت الموافقة على طلب الصيانة بنجاح.';
            $error_message = 'فشل في الموافقة على طلب الصيانة.';
        } elseif ($action_type === 'reject') {
            $new_status = 'Rejected';
            $success_message = 'تم رفض طلب الصيانة بنجاح.';
            $error_message = 'فشل في رفض طلب الصيانة.';
        }

        $updateData = [
            'status' => $new_status,
            'owner_approval_date' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($maintenanceRequestModel->update($request_id, $updateData)) {
            Session::set('success_message', $success_message);
            redirect('owner/approvals');
        } else {
            Session::set('error_message', $error_message);
            redirect('owner/approvals');
        }
    }
}

// في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم
if (!empty($errors)) {
    Session::set('error_message', implode('<br>', $errors));
    redirect('owner/approvals');
}