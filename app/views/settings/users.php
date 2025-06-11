<?php
// File: app/views/settings/users.php

/**
 * ===================================================================
 * صفحة إدارة المستخدمين (Users Management)
 * ===================================================================
 * تعرض هذه الصفحة لمدير النظام قائمة بجميع المستخدمين المسجلين في النظام
 * مع أدوات لإضافة مستخدم جديد أو تعديل/حذف مستخدم حالي.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $users = User::getAll();
$users = [
    ['id' => 1, 'full_name' => 'مدير النظام', 'email' => 'admin@app.com', 'role_name' => 'SystemAdmin', 'is_active' => true],
    ['id' => 2, 'full_name' => 'علي المحاسب', 'email' => 'ali.acc@app.com', 'role_name' => 'Accountant', 'is_active' => true],
    ['id' => 3, 'full_name' => 'محمد عبدالله', 'email' => 'mohammed@example.com', 'role_name' => 'Owner', 'is_active' => true],
    ['id' => 4, 'full_name' => 'خالد يوسف', 'email' => 'khalid@example.com', 'role_name' => 'Tenant', 'is_active' => false],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة المستخدمين';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($users) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">قائمة المستخدمين</h5>
            <a href="?url=settings/create_user" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus-fill me-1"></i> إضافة مستخدم جديد
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الاسم الكامل</th>
                            <th>البريد الإلكتروني</th>
                            <th class="text-center">الدور</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php e($user['id']); ?></td>
                                <td><strong><?php e($user['full_name']); ?></strong></td>
                                <td><?php e($user['email']); ?></td>
                                <td class="text-center"><span class="badge bg-primary"><?php e($user['role_name']); ?></span></td>
                                <td class="text-center">
                                    <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $user['is_active'] ? 'نشط' : 'غير نشط'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                     <a href="?url=settings/edit_user/<?php e($user['id']); ?>" class="btn btn-sm btn-outline-secondary" title="تعديل"><i class="bi bi-pencil-fill"></i></a>
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