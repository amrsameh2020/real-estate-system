<?php
// File: app/views/settings/index.php

/**
 * ===================================================================
 * صفحة إعدادات النظام (System Settings Page)
 * ===================================================================
 * تتيح هذه الصفحة لمسؤول النظام إدارة إعدادات النظام العامة.
 * يمكن أن تشمل إعدادات عامة، مثل اسم النظام، معلومات الاتصال،
 * أو أي إعدادات قابلة للتعديل.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مسؤول نظام)
Auth::requireRole('SystemAdmin');

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'إعدادات النظام';

// الخطوة 4: (اختياري) جلب الإعدادات الحالية من قاعدة البيانات
// افترض أن لديك موديل لإدارة الإعدادات، أو جدول إعدادات بسيط
// For now, we'll use placeholder values. In a real app, you'd fetch them.
$settings = [
    'system_name' => 'نظام إدارة العقارات (RMS)',
    'admin_email' => 'admin@example.com',
    'contact_phone' => '+201001234567',
    'address' => '123 شارع الاستقلال، مدينة نصر، القاهرة'
];

// جلب أي بيانات نموذج سابقة في حالة إعادة التوجيه بعد فشل التحقق
$form_data = Session::get('form_data', []);
Session::remove('form_data'); // إزالة البيانات بعد جلبها

// استخدام بيانات النموذج السابقة إن وجدت، وإلا استخدام بيانات الإعدادات الأصلية
$display_data = empty($form_data) ? $settings : array_merge($settings, $form_data);


// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($display_data) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">إعدادات النظام</h1>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">الإعدادات العامة</h6>
            </div>
            <div class="card-body">
                <form action="app/scripts/settings/handle_update.php" method="POST">
                    <?php Security::generateCSRF(); // توليد توكن CSRF ?>

                    <div class="form-group">
                        <label for="system_name">اسم النظام:</label>
                        <input type="text" class="form-control" id="system_name" name="system_name" value="<?php echo htmlspecialchars($display_data['system_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="admin_email">بريد المسؤول الإلكتروني:</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($display_data['admin_email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_phone">رقم هاتف الاتصال:</label>
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($display_data['contact_phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">العنوان:</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($display_data['address'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
                </form>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب العام للمسؤول
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>