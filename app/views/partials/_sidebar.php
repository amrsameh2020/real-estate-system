<?php
// File: app/views/partials/_sidebar.php

/**
 * ===================================================================
 * الشريط الجانبي الديناميكي (_sidebar.php)
 * ===================================================================
 * هذا الملف مسؤول عن عرض شريط التنقل الجانبي.
 * يستخدم كلاس Auth للتحقق من دور المستخدم الحالي ويقوم بعرض
 * عناصر القائمة المسموح له برؤيتها فقط.
 * كما يقوم بتحديد الرابط النشط حالياً لتمييزه بصرياً.
 */

// جلب الرابط الحالي لتحديد العنصر النشط
$current_url = $_GET['url'] ?? 'dashboard';

// جلب بيانات المستخدم الحالي لعرضها في الأسفل
$current_user = Auth::user();

?>
<div class="sidebar bg-dark text-white p-3 d-flex flex-column" id="sidebar">
    <!-- Logo and App Name -->
    <a href="?url=dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="bi bi-buildings-fill me-2 fs-4 text-primary"></i>
        <span class="fs-4"><?php echo APP_NAME; ?></span>
    </a>
    <hr>

    <!-- Navigation Links -->
    <ul class="nav nav-pills flex-column mb-auto">

        <!-- لوحة التحكم (تظهر لجميع المستخدمين المسجلين) -->
        <li class="nav-item">
            <a href="?url=dashboard" class="nav-link text-white <?php echo ($current_url === 'dashboard') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2 me-2"></i>
                لوحة التحكم
            </a>
        </li>

        <?php if (Auth::hasRole('SystemAdmin')) : ?>
        <!-- ########## قسم مدير النظام (SystemAdmin) ########## -->
        <li class="nav-item mt-2">
            <small class="nav-link disabled text-secondary px-1">الإدارة والتطوير</small>
        </li>
        <li>
            <a href="?url=properties" class="nav-link text-white <?php echo (str_starts_with($current_url, 'properties')) ? 'active' : ''; ?>">
                <i class="bi bi-building me-2"></i>
                إدارة العقارات
            </a>
        </li>
        <li>
            <a href="?url=users" class="nav-link text-white <?php echo (str_starts_with($current_url, 'users')) ? 'active' : ''; ?>">
                <i class="bi bi-people-fill me-2"></i>
                إدارة المستخدمين
            </a>
        </li>
        <li>
            <a href="?url=settings" class="nav-link text-white <?php echo (str_starts_with($current_url, 'settings')) ? 'active' : ''; ?>">
                <i class="bi bi-gear-fill me-2"></i>
                الإعدادات
            </a>
        </li>
        <?php endif; ?>


        <?php if (Auth::hasRole('SystemAdmin') || Auth::hasRole('Accountant')) : ?>
        <!-- ########## قسم المحاسبة (Accountant) ########## -->
        <li class="nav-item mt-2">
            <small class="nav-link disabled text-secondary px-1">العمليات المالية</small>
        </li>
        <li>
            <a href="?url=leases" class="nav-link text-white <?php echo (str_starts_with($current_url, 'leases')) ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text me-2"></i>
                العقود والإيجارات
            </a>
        </li>
        <li>
            <a href="?url=invoices" class="nav-link text-white <?php echo (str_starts_with($current_url, 'invoices')) ? 'active' : ''; ?>">
                <i class="bi bi-receipt me-2"></i>
                الفواتير والإيرادات
            </a>
        </li>
         <li>
            <a href="?url=expenses" class="nav-link text-white <?php echo (str_starts_with($current_url, 'expenses')) ? 'active' : ''; ?>">
                <i class="bi bi-wallet2 me-2"></i>
                المصروفات
            </a>
        </li>
        <?php endif; ?>

         <?php if (Auth::hasRole('Owner')) : ?>
        <!-- ########## قسم المالك (Owner) ########## -->
         <li class="nav-item mt-2">
            <small class="nav-link disabled text-secondary px-1">ممتلكاتي</small>
        </li>
        <li>
            <a href="?url=owner/reports" class="nav-link text-white <?php echo (str_starts_with($current_url, 'owner/reports')) ? 'active' : ''; ?>">
                <i class="bi bi-graph-up-arrow me-2"></i>
                التقارير المالية
            </a>
        </li>
        <li>
            <a href="?url=owner/approvals" class="nav-link text-white <?php echo (str_starts_with($current_url, 'owner/approvals')) ? 'active' : ''; ?>">
                <i class="bi bi-check2-circle me-2"></i>
                الموافقات
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (Auth::hasRole('Tenant')) : ?>
        <!-- ########## قسم المستأجر (Tenant) ########## -->
        <li class="nav-item mt-2">
            <small class="nav-link disabled text-secondary px-1">خدماتي</small>
        </li>
        <li>
            <a href="?url=tenant/payments" class="nav-link text-white <?php echo (str_starts_with($current_url, 'tenant/payments')) ? 'active' : ''; ?>">
                <i class="bi bi-credit-card me-2"></i>
                الفواتير والدفع
            </a>
        </li>
         <li>
            <a href="?url=tenant/maintenance" class="nav-link text-white <?php echo (str_starts_with($current_url, 'tenant/maintenance')) ? 'active' : ''; ?>">
                <i class="bi bi-tools me-2"></i>
                طلبات الصيانة
            </a>
        </li>
        <li>
            <a href="?url=tenant/documents" class="nav-link text-white <?php echo (str_starts_with($current_url, 'tenant/documents')) ? 'active' : ''; ?>">
                <i class="bi bi-folder2-open me-2"></i>
                مستنداتي
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- User Profile Dropdown -->
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://i.pravatar.cc/40?u=<?php echo $current_user['id']; ?>" alt="صورة المستخدم" width="32" height="32" class="rounded-circle me-2">
            <strong><?php e($current_user['full_name']); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="#">الملف الشخصي</a></li>
            <li><a class="dropdown-item" href="#">الإعدادات</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="?url=logout">تسجيل الخروج</a></li>
        </ul>
    </div>
</div>
