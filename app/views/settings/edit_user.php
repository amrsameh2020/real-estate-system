<?php
// File: app/views/settings/edit_user.php

/**
 * ===================================================================
 * صفحة تعديل مستخدم (Edit User)
 * ===================================================================
 * تعرض هذه الصفحة نموذجاً لمدير النظام لتعديل بيانات مستخدم موجود،
 * بما في ذلك دوره وحالة حسابه.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات اللازمة
$user_id = $_GET['id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    abort404('معرف المستخدم غير صالح.');
}

// $user = User::findById($user_id);
// $roles = Role::getAll();
// if (!$user) { abort404('المستخدم المطلوب غير موجود.'); }
$user = ['id' => $user_id, 'full_name' => 'علي المحاسب', 'email' => 'ali.acc@app.com', 'phone_number' => '0501112222', 'role_id' => 2, 'is_active' => true];
$roles = [['id' => 1, 'name' => 'SystemAdmin'], ['id' => 2, 'name' => 'Accountant'], ['id' => 3, 'name' => 'Owner'], ['id' => 4, 'name' => 'Tenant']];


// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'تعديل المستخدم: ' . $user['full_name'];

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($user, $roles) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><?php e($GLOBALS['page_title']); ?></h5>
            <a href="?url=settings/users" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i> العودة للقائمة</a>
        </div>
        <div class="card-body">
            <form action="?url=scripts/settings/handle_edit_user" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                <input type="hidden" name="id" value="<?php e($user['id']); ?>">
                
                <div class="row g-3">
                    <div class="col-md-6"><label for="full_name" class="form-label">الاسم الكامل</label><input type="text" class="form-control" name="full_name" value="<?php e($user['full_name']); ?>" required></div>
                    <div class="col-md-6"><label for="email" class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email" value="<?php e($user['email']); ?>" required></div>
                    <div class="col-md-6"><label for="phone_number" class="form-label">رقم الهاتف</label><input type="tel" class="form-control" name="phone_number" value="<?php e($user['phone_number']); ?>" required></div>
                    <div class="col-md-6"><label for="role_id" class="form-label">الدور</label>
                        <select class="form-select" name="role_id" required>
                            <?php foreach($roles as $role): ?>
                                <option value="<?php e($role['id']); ?>" <?php echo ($user['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                                    <?php e($role['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label for="password" class="form-label">كلمة المرور الجديدة</label><input type="password" class="form-control" name="password"><small class="text-muted">اتركه فارغاً لعدم التغيير.</small></div>
                    <div class="col-md-6"><label for="is_active" class="form-label">الحالة</label>
                         <select class="form-select" name="is_active">
                            <option value="1" <?php echo $user['is_active'] ? 'selected' : ''; ?>>نشط</option>
                            <option value="0" <?php echo !$user['is_active'] ? 'selected' : ''; ?>>غير نشط</option>
                        </select>
                    </div>
                </div>
                
                <hr class="my-4">
                <button type="submit" class="btn btn-success">حفظ التغييرات</button>
            </form>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>