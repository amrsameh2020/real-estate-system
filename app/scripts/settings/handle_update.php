<?php
// File: app/scripts/settings/handle_update.php

/**
 * ===================================================================
 * معالج تحديث إعدادات النظام (Handle Update System Settings)
 * ===================================================================
 * يتعامل هذا السكريبت مع طلبات POST لتحديث إعدادات النظام العامة.
 * - يتحقق من توكن CSRF.
 * - يتحقق من صلاحيات المستخدم (SystemAdmin فقط).
 * - يقوم بالتحقق من صحة المدخلات.
 * - يقوم بتحديث الإعدادات في قاعدة البيانات.
 * - يعيد التوجيه مع رسالة نجاح أو فشل.
 *
 * ملاحظة: هذا التنفيذ يفترض أن لديك جدول إعدادات بسيط (مثلاً: مفتاح-قيمة)
 * أو موديل SettingsModel. لغرض هذا المثال، سنقوم بتحديث وهمي
 * أو حفظها في مكان افتراضي إن لم يكن هناك جدول مخصص.
 */

require_once __DIR__ . '/../../core/bootstrap.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::set('error_message', 'Invalid request method.');
    redirect('settings');
}

// التحقق من توكن CSRF
if (!Security::verifyCSRF($_POST['csrf_token'])) {
    Session::set('error_message', 'CSRF token validation failed.');
    redirect('settings');
}

// التحقق من صلاحيات المستخدم (يجب أن يكون مسؤول نظام فقط)
Auth::requireRole('SystemAdmin');

$errors = [];

// جلب وتصفية المدخلات
$system_name = filter_input(INPUT_POST, 'system_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_SANITIZE_EMAIL);
$contact_phone = filter_input(INPUT_POST, 'contact_phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// التحقق من صحة المدخلات
if (empty($system_name)) {
    $errors[] = 'اسم النظام مطلوب.';
}
if (empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'بريد إلكتروني صالح للمسؤول مطلوب.';
}
// يمكن إضافة تحقق إضافي لرقم الهاتف والعنوان إذا لزم الأمر

// إذا لم تكن هناك أخطاء، قم بتحديث الإعدادات
if (empty($errors)) {
    // في بيئة حقيقية، ستقوم هنا باستدعاء موديل SettingsModel
    // مثل: $settingsModel = new Settings();
    // ثم تقوم بتحديث كل إعداد على حدة أو دفعة واحدة
    // For now, let's simulate success.

    // مثال على كيفية تحديث الإعدادات إذا كان لديك جدول بسيط (key, value)
    // تحتاج إلى إضافة موديل Settings.php ودوال updateByKey
    /*
    $settingsModel = new Settings();
    try {
        $settingsModel->updateSetting('system_name', $system_name);
        $settingsModel->updateSetting('admin_email', $admin_email);
        $settingsModel->updateSetting('contact_phone', $contact_phone);
        $settingsModel->updateSetting('address', $address);

        Session::set('success_message', 'تم تحديث إعدادات النظام بنجاح.');
        redirect('settings');
    } catch (Exception $e) {
        Session::set('error_message', 'حدث خطأ أثناء تحديث الإعدادات: ' . $e->getMessage());
        Session::set('form_data', $_POST);
        redirect('settings');
    }
    */

    // بديل بسيط: فقط اعرض رسالة نجاح بدون تحديث فعلي لقاعدة البيانات
    // (هذا يجب استبداله بمنطق قاعدة بيانات حقيقي)
    Session::set('success_message', 'تم حفظ الإعدادات بنجاح (محاكاة).');
    redirect('settings');

} else {
    // في حالة وجود أخطاء، قم بتخزينها وإعادة توجيه المستخدم إلى صفحة الإعدادات
    Session::set('error_message', implode('<br>', $errors));
    Session::set('form_data', $_POST); // للحفاظ على بيانات النموذج
    redirect('settings');
}