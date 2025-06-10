<?php
// File: app/views/users/create.php

/**
 * ===================================================================
 * صفحة إنشاء مستخدم جديد (Create New User Page)
 * ===================================================================
 * تتيح هذه الصفحة لمسؤولي النظام إضافة مستخدمين جدد إلى النظام.
 * تتضمن نموذجًا لإدخال تفاصيل المستخدم.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مسؤول نظام)
Auth::requireRole('SystemAdmin');

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'إضافة مستخدم جديد';

// جلب أي بيانات نموذج سابقة في حالة إعادة التوجيه بعد فشل التحقق
$form_data = Session::get('form_data', []);
Session::remove('form_data'); // إزالة البيانات بعد جلبها

// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($form_data) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">إضافة مستخدم جديد</h1>
            <a href="?url=users" class="btn btn-secondary">العودة إلى قائمة المستخدمين</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">تفاصيل المستخدم</h6>
            </div>
            <div class="card-body">
                <form action="app/scripts/users/handle_create.php" method="POST">
                    <?php Security::generateCSRF(); // توليد توكن CSRF ?>

                    <div class="form-group">
                        <label for="full_name">الاسم الكامل:</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">البريد الإلكتروني:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">كلمة المرور:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">تأكيد كلمة المرور:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="form-group">
                        <label for="role">الدور:</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">اختر دوراً</option>
                            <option value="SystemAdmin" <?php echo (isset($form_data['role']) && $form_data['role'] == 'SystemAdmin') ? 'selected' : ''; ?>>مسؤول النظام</option>
                            <option value="Owner" <?php echo (isset($form_data['role']) && $form_data['role'] == 'Owner') ? 'selected' : ''; ?>>مالك عقار</option>
                            <option value="Tenant" <?php echo (isset($form_data['role']) && $form_data['role'] == 'Tenant') ? 'selected' : ''; ?>>مستأجر</option>
                            <option value="Accountant" <?php echo (isset($form_data['role']) && $form_data['role'] == 'Accountant') ? 'selected' : ''; ?>>محاسب</option>
                            </select>
                    </div>

                    <div class="form-group">
                        <label for="phone_number">رقم الهاتف (اختياري):</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($form_data['phone_number'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">العنوان (اختياري):</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">إنشاء مستخدم</button>
                </form>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 5: تضمين القالب العام للمسؤول
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>