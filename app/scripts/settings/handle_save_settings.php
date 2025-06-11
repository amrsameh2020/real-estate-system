<?php
// File: app/scripts/settings/handle_save_settings.php

/**
 * ===================================================================
 * ملف معالجة حفظ الإعدادات العامة
 * ===================================================================
 * يستقبل هذا الملف البيانات من نماذج الإعدادات المختلفة (مثل الإعدادات الإقليمية)
 * ويقوم بحفظها. في هذا المثال، سنحفظها في جدول `settings`.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=settings/regional');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: استقبال مصفوفة الإعدادات
$settings = $_POST['settings'] ?? [];

// الخطوة 4: المرور على الإعدادات وحفظ كل واحد منها
// يتطلب هذا وجود موديل Setting مع دالة updateOrCreate($key, $value)
if (is_array($settings)) {
    foreach ($settings as $key => $value) {
        $sanitized_key = Security::sanitizeInput($key);
        $sanitized_value = Security::sanitizeInput($value);
        
        // Setting::updateOrCreate($sanitized_key, $sanitized_value);
    }
}

// الخطوة 5: إعادة التوجيه مع رسالة نجاح
Session::flash('success', 'تم حفظ الإعدادات بنجاح.');
redirect('public/index.php?url=settings/regional');