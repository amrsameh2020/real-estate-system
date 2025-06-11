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
require_once __DIR__ . '/../app/core/bootstrap.php';

// --- الخطوة 2: تحليل الرابط (Routing) ---
$request_uri = trim($_GET['url'] ?? '', '/');

// --- الخطوة 3: التحقق من المصادقة (Authentication Gate) ---
$public_pages = ['login', 'register', 'handle-login', 'handle-register'];
$is_public_page = in_array($request_uri, $public_pages);

if (!Auth::check() && !$is_public_page) {
    Session::flash('warning', 'يجب عليك تسجيل الدخول أولاً للوصول لهذه الصفحة.');
    redirect('public/index.php?url=login');
}

if (Auth::check() && $is_public_page) {
    redirect('public/index.php?url=dashboard');
}

// --- الخطوة 4: توجيه الطلب إلى الصفحة أو ملف المعالجة المناسب ---
$view_path = APP_ROOT . '/app/views/';
$script_path = APP_ROOT . '/app/scripts/';
$page_file = '';

// تقسيم الرابط إلى أجزاء للتعامل مع الروابط المعقدة
$uri_parts = explode('/', $request_uri);
$main_route = $uri_parts[0] ?? '';
$sub_route = $uri_parts[1] ?? '';
$id = $uri_parts[2] ?? null;

// تمرير الـ ID إلى الصفحات التي تحتاجه
if ($id) { $_GET['id'] = $id; }

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
    case '':
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

    // --- إدارة العقارات ---
    case 'properties':
        Auth::requireRole('SystemAdmin');
        $sub_route = $sub_route ?: 'index';
        $page_file = $view_path . "properties/{$sub_route}.php";
        break;

    // --- إدارة الوحدات ---
    case 'units':
        Auth::requireRole('SystemAdmin');
        $sub_route = $sub_route ?: 'index';
        $page_file = $view_path . "units/{$sub_route}.php";
        break;

    // --- إدارة المستخدمين ---
    case 'users':
        Auth::requireRole('SystemAdmin');
        $sub_route = $sub_route ?: 'index';
        $page_file = $view_path . "users/{$sub_route}.php";
        break;

    // --- الإعدادات ---
    case 'settings':
        Auth::requireRole('SystemAdmin');
        $sub_route = $sub_route ?: 'index';
        $page_file = $view_path . "settings/{$sub_route}.php";
        break;

    // --- العقود والإيجارات ---
    case 'leases':
        Auth::requireRole('SystemAdmin', 'Accountant');
        $sub_route = $sub_route ?: 'index';
        $page_file = $view_path . "leases/{$sub_route}.php";
        break;

    // --- المالية (الفواتير، المصروفات، التقارير) ---
    case 'finance':
        Auth::requireRole('SystemAdmin', 'Accountant');
        $sub_route = $sub_route ?: 'invoices';
        $page_file = $view_path . "finance/{$sub_route}.php";
        break;

    // --- التسويق وإدارة العملاء ---
    case 'marketing':
        Auth::requireRole('SystemAdmin');
        $sub_route = $sub_route ?: 'leads';
        $page_file = $view_path . "marketing/{$sub_route}.php";
        break;

    // --- إدارة العملاء (CRM) ---
    case 'crm':
        Auth::requireRole('SystemAdmin');
        $sub_route = $sub_route ?: 'clients';
        $page_file = $view_path . "crm/{$sub_route}.php";
        break;

    // --- الصيانة ---
    case 'maintenance':
        Auth::requireRole('SystemAdmin');
        $sub_route = $sub_route ?: 'board';
        $page_file = $view_path . "maintenance/{$sub_route}.php";
        break;

    // --- المالك ---
    case 'owner':
        Auth::requireRole('Owner');
        $sub_route = $sub_route ?: 'reports';
        $page_file = $view_path . "owner/{$sub_route}.php";
        if ($sub_route == 'properties' && isset($uri_parts[2]) && $uri_parts[2] == 'view') {
            $page_file = $view_path . "owner/properties/view.php";
        }
        break;

    // --- المستأجر ---
    case 'tenant':
        Auth::requireRole('Tenant');
        $sub_route = $sub_route ?: 'payments';
        $page_file = $view_path . "tenant/{$sub_route}.php";
        if ($sub_route == 'maintenance' && isset($uri_parts[2]) && $uri_parts[2] == 'create') {
            $page_file = $view_path . "tenant/maintenance/create.php";
        }
        break;

    default:
        $page_file = $view_path . 'errors/404.php';
        break;
}

// --- الخطوة 5: عرض الصفحة أو خطأ 404 ---
if (file_exists($page_file)) {
    require $page_file;
} else {
    http_response_code(404);
    require $view_path . 'errors/404.php';
}
