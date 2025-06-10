<?php
// File: app/core/functions.php

/**
 * ===================================================================
 * ملف الدوال المساعدة (Helper Functions)
 * ===================================================================
 * يحتوي هذا الملف على مجموعة من الدوال العامة التي يتم استخدامها بشكل متكرر
 * في جميع أنحاء التطبيق لتبسيط الكود وتجنب التكرار.
 * هذه الدوال لا تنتمي إلى أي كلاس معين.
 */

/**
 * دالة مختصرة لطباعة البيانات بشكل آمن (للحماية من هجمات XSS).
 * تقوم بتحويل الأحرف الخاصة إلى صيغة HTML الخاصة بها.
 * @param string|null $string النص المراد طباعته.
 */
function e($string) {
    echo htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * إعادة توجيه المستخدم إلى رابط معين داخل التطبيق.
 * @param string $path المسار المراد التوجيه إليه (مثل 'public/index.php?url=login').
 */
function redirect($path) {
    // استخدام APP_URL لضمان بناء روابط كاملة وصحيحة
    header("Location: " . APP_URL . '/' . ltrim($path, '/'));
    exit(); // إيقاف التنفيذ بعد إعادة التوجيه مباشرة
}

/**
 * عرض صفحة خطأ 404 وإيقاف التنفيذ.
 * @param string $message رسالة الخطأ (اختياري).
 */
function abort404($message = '404 - الصفحة غير موجودة') {
    http_response_code(404);
    // في تطبيق حقيقي، يمكنك هنا تضمين ملف عرض مخصص لصفحة 404
    // require_once APP_ROOT . '/app/views/errors/404.php';
    die($message);
}

/**
 * دالة لعرض مخرجات متغيرة بشكل منسق لأغراض التصحيح فقط (Dump and Die).
 * لا تستخدم هذه الدالة في البيئة الإنتاجية.
 * @param mixed $data البيانات المراد عرضها.
 */
function dd($data) {
    // التأكد من أن الدالة تعمل فقط في وضع التصحيح
    if (defined('APP_DEBUG') && APP_DEBUG === true) {
        echo '<pre style="background-color: #1d1d1d; color: #f8f8f2; padding: 1rem; border-radius: 5px; direction: ltr; text-align: left;">';
        var_dump($data);
        echo '</pre>';
    }
    die(); // إيقاف التنفيذ دائماً بعد العرض
}

/**
 * بناء رابط كامل لأحد ملفات assets (CSS, JS, Images).
 * @param string $path مسار الملف داخل مجلد public/assets.
 * @return string الرابط الكامل للملف.
 */
function asset($path) {
    return APP_URL . '/public/assets/' . ltrim($path, '/');
}
