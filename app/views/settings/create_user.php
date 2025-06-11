<?php
// File: app/views/settings/create_user.php

/**
 * ===================================================================
 * صفحة إضافة مستخدم جديد (بواسطة المدير)
 * ===================================================================
 * تعرض هذه الصفحة نموذجاً لمدير النظام لإنشاء حساب مستخدم جديد
 * وتعيين دوره وصلاحياته مباشرة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات اللازمة (مثل قائمة الأدوار)
// $roles = Role::getAll();
$roles = [
    ['id' => 1, 'name' => 'SystemAdmin'],
    ['id' => 2, 'name' => 'Accountant'],
    ['id' => 3, 'name' => 'Owner'],
    ['id' => 4, 'name' => 'Tenant'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إضافة مستخدم جديد';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($roles) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><?php e($GLOBALS['page_title']); ?></h5>
            <a href="?url=settings/users" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i> العودة للقائمة</a>
        </div>
        <div class="card-body">
            <form action="?url=scripts/settings/handle_create_user" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                
                <div class="row g-3">
                    <div class="col-md-6"><label for="full_name" class="form-label">الاسم الكامل</label><input type="text" class="form-control" name="full_name" required></div>
                    <div class="col-md-6"><label for="email" class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email" required></div>
                    <div class="col-md-6"><label for="phone_number" class="form-label">رقم الهاتف</label><input type="tel" class="form-control" name="phone_number" required></div>
                    <div class="col-md-6"><label for="role_id" class="form-label">الدور</label>
                        <select class="form-select" name="role_id" required>
                            <?php foreach($roles as $role): ?>
                                <option value="<?php e($role['id']); ?>"><?php e($role['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label for="password" class="form-label">كلمة المرور</label><input type="password" class="form-control" name="password" required></div>
                    <div class="col-md-6"><label for="is_active" class="form-label">الحالة</label>
                         <select class="form-select" name="is_active">
                            <option value="1" selected>نشط</option>
                            <option value="0">غير نشط</option>
                        </select>
                    </div>
                </div>
                
                <hr class="my-4">
                <button type="submit" class="btn btn-primary">حفظ المستخدم</button>
            </form>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>
