<?php
// File: app/core/bootstrap.php

/**
 * ===================================================================
 * ملف التشغيل الأساسي (Bootstrap)
 * ===================================================================
 * يتم تضمين هذا الملف في بداية كل طلب يصل إلى النظام.
 * وظيفته هي تحميل وتهيئة جميع المكونات الأساسية بالترتيب الصحيح،
 * مما يضمن أن بيئة العمل جاهزة قبل تنفيذ أي منطق برمجي خاص بالصفحة.
 */

// -- الخطوة 1: تحديد مسار جذر التطبيق لسهولة الوصول للملفات --
// هذا يضمن أن المسارات ستعمل بشكل صحيح بغض النظر عن مكان تشغيل السكربت.
// dirname(__DIR__, 2) تعني "اذهب إلى المجلد الأصل مرتين" من موقع هذا الملف.
define('APP_ROOT', dirname(__DIR__, 2));

// -- الخطوة 2: تحميل ملفات الإعدادات الأساسية --
// هذه الملفات تحتوي على معلومات حساسة وإعدادات عامة.
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/database.php';

// -- الخطوة 3: إعداد بيئة عرض الأخطاء --
// يتم عرض الأخطاء التفصيلية فقط في وضع التطوير (Development) للأمان.
if (defined('APP_DEBUG') && APP_DEBUG === true) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
    // في البيئة الإنتاجية، يمكنك هنا إضافة كود لتسجيل الأخطاء في ملف logs
    // error_log('Error message', 3, APP_ROOT . '/logs/errors.log');
}

// -- الخطوة 4: تحميل الكلاسات والدوال الأساسية --
// يتم تحميل هذه المكونات بالترتيب لضمان الاعتمادية الصحيحة.
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Session.php';
require_once APP_ROOT . '/app/core/Security.php';
require_once APP_ROOT . '/app/core/functions.php';

// -- الخطوة 5: بدء الجلسة الآمنة --
// يجب أن يتم بعد تحميل كلاس Session مباشرةً.
Session::start();

// -- الخطوة 6: تحميل جميع كلاسات النماذج (Models) --
// هذا الجزء سيقوم بتحميل كل ملفات الموديلات التي تمثل جداول قاعدة البيانات.
// في مشروع حقيقي كبير، ستستخدم autoloading، ولكن للتوضيح، سنقوم بتضمينها يدوياً.
// Autoloader example:
// spl_autoload_register(function ($class_name) {
//     $file = APP_ROOT . '/app/models/' . $class_name . '.php';
//     if (file_exists($file)) {
//         require_once $file;
//     }
// });

require_once APP_ROOT . '/app/models/User.php';
require_once APP_ROOT . '/app/models/Property.php';
require_once APP_ROOT . '/app/models/Unit.php';
require_once APP_ROOT . '/app/models/Lease.php';
require_once APP_ROOT . '/app/models/Invoice.php';
require_once APP_ROOT . '/app/models/Payment.php';
require_once APP_ROOT . '/app/models/Expense.php';
require_once APP_ROOT . '/app/models/MaintenanceRequest.php';
require_once APP_ROOT . '/app/models/Document.php';


// -- الخطوة 7: تحميل كلاس المصادقة --
// يجب أن يتم بعد تحميل موديل User الذي يعتمد عليه.
require_once APP_ROOT . '/app/core/Auth.php';

/**
 * -------------------------------------------------------------------
 * النظام جاهز الآن!
 * -------------------------------------------------------------------
 * أي ملف يقوم بتضمين هذا الملف (bootstrap.php) سيكون لديه الآن وصول
 * كامل إلى جميع الكلاسات والدوال الأساسية (Database, Session, Auth, etc.).
 */
