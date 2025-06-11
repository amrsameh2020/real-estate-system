<?php
// File: app/views/settings/integrations.php

/**
 * ===================================================================
 * صفحة مركز التكامل والربط (Integrations Hub)
 * ===================================================================
 * تعرض هذه الصفحة بطاقات لجميع خدمات الربط المتاحة وتسمح للمدير
 * بإدارة وتفعيل كل خدمة على حدة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $integrations_status = Integration::getStatuses();
$integrations = [
    'payment_gateway' => ['name' => 'بوابات الدفع', 'icon' => 'credit-card-2-front-fill', 'is_active' => true, 'description' => 'تفعيل الدفع الإلكتروني للإيجارات والفواتير.'],
    'sms_gateway' => ['name' => 'بوابة الرسائل النصية', 'icon' => 'chat-dots-fill', 'is_active' => false, 'description' => 'إرسال إشعارات وتذكيرات عبر رسائل SMS.'],
    'email_service' => ['name' => 'خدمة البريد الإلكتروني', 'icon' => 'envelope-fill', 'is_active' => true, 'description' => 'ضمان وصول رسائل البريد الإلكتروني بشكل موثوق.'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'مركز التكامل والربط';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($integrations) {
?>
    <div class="row g-4">
        <?php foreach($integrations as $key => $integration): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi <?php e($integration['icon']); ?> fs-2 text-primary me-3"></i>
                        <h5 class="card-title fw-bold mb-0"><?php e($integration['name']); ?></h5>
                        <span class="badge ms-auto <?php echo $integration['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $integration['is_active'] ? 'نشط' : 'غير نشط'; ?>
                        </span>
                    </div>
                    <p class="card-text text-muted small">
                        <?php e($integration['description']); ?>
                    </p>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <a href="?url=settings/edit_integration/<?php e($key); ?>" class="btn btn-outline-secondary w-100">
                        إدارة
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php
};

// الخطوة 6: تضمين القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>
