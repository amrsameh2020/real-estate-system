<?php
// File: public/index.php

/**
 * ===================================================================
 * نقطة الدخول الوحيدة للتطبيق (The Single Entry Point)
 * ===================================================================
 * هذا الملف يعمل كموجه (Router) للتطبيق بأكمله.
 * 1. يستقبل جميع الطلبات بفضل ملف .htaccess.
 * 2. يقوم بتحميل وتشغيل قلب النظام (bootstrap.php).
 * 3. يحلل الرابط المطلوب لتحديد الصفحة التي يريدها المستخدم.
 * 4. يقوم بالتحقق من صلاحيات الوصول (Authentication Gate).
 * 5. يقوم بتضمين ملف العرض (View) المناسب من مجلد app/views.
 */


// --- الخطوة 1: تحميل وتشغيل قلب النظام ---
// هذا السطر يقوم بتحميل كل الكلاسات والإعدادات الأساسية.
// يجب أن يكون المسار صحيحاً بناءً على مكان ملف index.php.
require_once __DIR__ . '/../app/core/bootstrap.php';


// --- الخطوة 2: تحليل الرابط (Routing) ---
// جلب الرابط المطلوب من متغير 'url' الذي تم إعداده في ملف .htaccess
// وتنظيفه من أي مسافات أو شرطات زائدة من البداية والنهاية.
$request_uri = trim($_GET['url'] ?? '', '/');


// --- الخطوة 3: التحقق من المصادقة (Authentication Gate) ---
// قائمة بالصفحات التي يمكن للزائر الوصول إليها بدون تسجيل دخول.
$public_pages = ['login', 'register', 'handle-login', 'handle-register'];
$is_public_page = in_array($request_uri, $public_pages);

// إذا لم يكن المستخدم مسجل دخوله ويحاول الوصول لصفحة محمية، قم بتحويله لصفحة الدخول.
if (!Auth::check() && !$is_public_page) {
    Session::flash('warning', 'يجب عليك تسجيل الدخول أولاً للوصول لهذه الصفحة.');
    redirect('public/index.php?url=login');
}

// إذا كان المستخدم مسجل دخوله ويحاول الوصول لصفحة الدخول/التسجيل، قم بتحويله للوحة التحكم.
if (Auth::check() && $is_public_page) {
    redirect('public/index.php?url=dashboard');
}


// --- الخطوة 4: توجيه الطلب إلى الصفحة أو ملف المعالجة المناسب ---
$view_path = APP_ROOT . '/app/views/';
$script_path = APP_ROOT . '/app/scripts/';
$page_file = '';

// تقسيم الرابط إلى أجزاء للتعامل مع الروابط المعقدة (e.g., /properties/view/15)
$uri_parts = explode('/', $request_uri);
$main_route = $uri_parts[0] ?? '';

switch ($main_route) {
    // --- صفحات المصادقة ---
    case 'login':
        $page_file = $view_path . 'auth/login.php';
        break;
    case 'register':
        $page_file = $view_path . 'auth/register.php';
        break;
    case 'logout':
        $page_file = $view_path . 'auth/logout.php';
        break;

    // --- ملفات معالجة المصادقة ---
    case 'handle-login':
        $page_file = $script_path . 'auth/handle_login.php';
        break;
    case 'handle-register':
        $page_file = $script_path . 'auth/handle_register.php';
        break;
        
    // --- لوحة التحكم ---
    case 'dashboard':
    case '': // الصفحة الافتراضية
        // تحديد أي لوحة تحكم سيتم عرضها بناءً على دور المستخدم
        if (Auth::hasRole('SystemAdmin')) {
            $page_file = $view_path . 'dashboard/admin_dashboard.php';
        } elseif (Auth::hasRole('Accountant')) {
            $page_file = $view_path . 'dashboard/accountant_dashboard.php';
        } elseif (Auth::hasRole('Owner')) {
            $page_file = $view_path . 'dashboard/owner_dashboard.php';
        } else { // Tenant
            $page_file = $view_path . 'dashboard/tenant_dashboard.php';
        }
        break;

    // --- صفحات إدارة العقارات ---
    case 'properties':
        Auth::requireRole('SystemAdmin'); // مثال على التحقق من الصلاحية
        $sub_route = $uri_parts[1] ?? 'index';
        $id = $uri_parts[2] ?? null;

        // تمرير الـ ID إلى الصفحات التي تحتاجه
        if ($id) { $_GET['id'] = $id; }

        switch($sub_route) {
            case 'index':
            default:
                $page_file = $view_path . 'properties/index.php';
                break;
            case 'create':
                $page_file = $view_path . 'properties/create.php';
                break;
            case 'view':
                $page_file = $view_path . 'properties/view.php';
                break;
            case 'edit':
                 $page_file = $view_path . 'properties/edit.php';
                 break;
        }
        break;

    default:
        // إذا لم يتم العثور على الرابط، اعرض صفحة خطأ 404
        $page_file = $view_path . 'errors/404.php'; // سنحتاج لإنشاء هذا الملف
        break;
}

// --- الخطوة 5: عرض الصفحة أو خطأ 404 ---
if (file_exists($page_file)) {
    require $page_file;
} else {
    http_response_code(404);
    // يمكنك هنا تضمين ملف عرض مخصص لصفحة 404
    require $view_path . 'errors/404.php';
}

