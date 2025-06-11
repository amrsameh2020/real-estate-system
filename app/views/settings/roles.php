<?php
// File: app/views/settings/roles.php

/**
 * ===================================================================
 * صفحة إدارة الأدوار والصلاحيات (Roles & Permissions)
 * ===================================================================
 * تتيح هذه الصفحة لمدير النظام إنشاء أدوار جديدة وتحديد الصلاحيات
 * لكل دور بشكل دقيق.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $roles = Role::getAllWithPermissions();
$roles = [
    ['id' => 1, 'name' => 'SystemAdmin', 'permissions_count' => 50],
    ['id' => 2, 'name' => 'Accountant', 'permissions_count' => 25],
    ['id' => 3, 'name' => 'Owner', 'permissions_count' => 10],
    ['id' => 4, 'name' => 'Tenant', 'permissions_count' => 8],
];


// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة الأدوار والصلاحيات';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($roles) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">الأدوار الحالية</h5>
            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-circle-fill me-1"></i> إضافة دور جديد</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>اسم الدور</th>
                            <th>عدد الصلاحيات</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td><?php e($role['id']); ?></td>
                                <td><strong><?php e($role['name']); ?></strong></td>
                                <td><?php e($role['permissions_count']); ?></td>
                                <td class="text-center">
                                     <a href="?url=settings/edit_role/<?php e($role['id']); ?>" class="btn btn-sm btn-outline-secondary" title="تعديل الصلاحيات"><i class="bi bi-shield-lock-fill"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>