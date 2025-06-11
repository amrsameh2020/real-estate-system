<?php
// File: app/views/settings/automation.php

/**
 * ===================================================================
 * صفحة قواعد الأتمتة (Automation Rules)
 * ===================================================================
 * تتيح هذه الصفحة لمدير النظام إنشاء قواعد عمل مخصصة (If This, Then That)
 * لتشغيل إجراءات معينة تلقائياً بناءً على أحداث محددة في النظام.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $rules = Automation::getAll();
$rules = [
    ['id' => 1, 'name' => 'إرسال تذكير قبل انتهاء العقد', 'trigger' => 'انتهاء العقد بعد 30 يوم', 'action' => 'إرسال بريد إلكتروني للمستأجر', 'is_active' => true],
    ['id' => 2, 'name' => 'إشعار بتأخر الدفع', 'trigger' => 'تأخر دفع الفاتورة 3 أيام', 'action' => 'إرسال رسالة SMS للمستأجر', 'is_active' => true],
    ['id' => 3, 'name' => 'طلب تقييم الخدمة', 'trigger' => 'إغلاق طلب الصيانة', 'action' => 'إرسال بريد إلكتروني للمستأجر', 'is_active' => false],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'قواعد الأتمتة';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($rules) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">قواعد التشغيل التلقائي</h5>
            <a href="?url=settings/create_rule" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle-fill me-1"></i> إنشاء قاعدة جديدة</a>
        </div>
        <div class="card-body p-0">
             <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الحالة</th>
                            <th>اسم القاعدة</th>
                            <th>الحدث (Trigger)</th>
                            <th>الإجراء (Action)</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rules as $rule): ?>
                            <tr>
                                <td>
                                    <div class="form-check form-switch">
                                      <input class="form-check-input" type="checkbox" role="switch" <?php echo $rule['is_active'] ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td><strong><?php e($rule['name']); ?></strong></td>
                                <td><?php e($rule['trigger']); ?></td>
                                <td><?php e($rule['action']); ?></td>
                                <td class="text-center">
                                     <a href="?url=settings/edit_rule/<?php e($rule['id']); ?>" class="btn btn-sm btn-outline-secondary" title="تعديل"><i class="bi bi-pencil-fill"></i></a>
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
