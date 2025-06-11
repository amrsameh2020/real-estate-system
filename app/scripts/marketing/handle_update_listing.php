<?php
// File: app/scripts/marketing/handle_update_listing.php

/**
 * ===================================================================
 * ملف معالجة تحديث حالة العرض التسويقي (Update Listing Handler)
 * ===================================================================
 * هذا الملف مسؤول عن تحديث حالة عرض وحدة معينة (مثل نشرها أو إلغاء نشرها).
 * يتم استدعاؤه عادة عبر طلب AJAX.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

// الخطوة 3: تنظيف واستقبال البيانات
$unit_id = filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT);
$new_status = Security::sanitizeInput($_POST['status'] ?? ''); // e.g., 'Published' or 'Unpublished'

// الخطوة 4: التحقق من صحة المدخلات
$allowed_statuses = ['Published', 'Unpublished'];
if (!$unit_id || empty($new_status) || !in_array($new_status, $allowed_statuses)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'بيانات غير صالحة.']);
    exit();
}

// الخطوة 5: محاولة تحديث الحالة
// في تطبيق حقيقي، ستكون هناك دالة في موديل Listing أو Unit لتحديث هذه الحالة
// $success = Listing::updateStatus($unit_id, $new_status);
$success = true; // للتوضيح

if ($success) {
    http_response_code(200);
    echo json_encode(['success' => 'تم تحديث حالة العرض بنجاح.']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'حدث خطأ أثناء تحديث الحالة.']);
}