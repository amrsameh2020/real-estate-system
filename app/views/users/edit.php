<?php
// File: app/views/users/edit.php

/**
 * ===================================================================
 * صفحة تعديل المستخدم (Edit User Page)
 * ===================================================================
 * تتيح هذه الصفحة لمسؤولي النظام تعديل معلومات مستخدم موجود.
 * تتضمن نموذجًا مملوءًا مسبقًا بتفاصيل المستخدم الحالية.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مسؤول نظام)
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب معرف المستخدم من الرابط
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$user_id) {
    Session::set('error_message', 'معرف المستخدم غير صالح.');
    redirect('users');
}

// الخطوة 4: جلب بيانات المستخدم من قاعدة البيانات
$userModel = new User();
$user_data = $userModel->getUserById($user_id);

if (!$user_data) {
    Session::set('error_message', 'المستخدم غير موجود.');
    redirect('users');
}

// الخطوة 5: تحديد عنوان الصفحة
$page_title = 'تعديل المستخدم: ' . htmlspecialchars($user_data['full_name']);

// جلب أي بيانات نموذج سابقة في حالة إعادة التوجيه بعد فشل التحقق
$form_data = Session::get('form_data', []);
Session::remove('form_data'); // إزالة البيانات بعد جلبها

// استخدام بيانات النموذج السابقة إن وجدت، وإلا استخدام بيانات المستخدم الأصلية
$display_data = empty($form_data) ? $user_data : array_merge($user_data, $form_data);

// الخطوة 6: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($display_data, $user_id) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">تعديل المستخدم: <?php echo htmlspecialchars($display_data['full_name']); ?></h1>
            <a href="?url=users" class="btn btn-secondary">العودة إلى قائمة المستخدمين</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">تعديل تفاصيل المستخدم</h6>
            </div>
            <div class="card-body">
                <form action="app/scripts/users/handle_edit.php" method="POST">
                    <?php Security::generateCSRF(); // توليد توكن CSRF ?>
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

                    <div class="form-group">
                        <label for="full_name">الاسم الكامل:</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($display_data['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">البريد الإلكتروني:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($display_data['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="role">الدور:</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">اختر دوراً</option>
                            <option value="SystemAdmin" <?php echo (isset($display_data['role']) && $display_data['role'] == 'SystemAdmin') ? 'selected' : ''; ?>>مسؤول النظام</option>
                            <option value="Owner" <?php echo (isset($display_data['role']) && $display_data['role'] == 'Owner') ? 'selected' : ''; ?>>مالك عقار</option>
                            <option value="Tenant" <?php echo (isset($display_data['role']) && $display_data['role'] == 'Tenant') ? 'selected' : ''; ?>>مستأجر</option>
                            <option value="Accountant" <?php echo (isset($display_data['role']) && $display_data['role'] == 'Accountant') ? 'selected' : ''; ?>>محاسب</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="phone_number">رقم الهاتف (اختياري):</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($display_data['phone_number'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">العنوان (اختياري):</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($display_data['address'] ?? ''); ?></textarea>
                    </div>

                    <hr>
                    <h5>تغيير كلمة المرور (اختياري)</h5>
                    <p class="text-muted">اترك حقلي كلمة المرور فارغين إذا كنت لا ترغب في تغيير كلمة المرور.</p>

                    <div class="form-group">
                        <label for="password">كلمة المرور الجديدة:</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">تأكيد كلمة المرور الجديدة:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>

                    <button type="submit" class="btn btn-primary">تحديث المستخدم</button>
                </form>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 7: تضمين القالب العام للمسؤول
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>