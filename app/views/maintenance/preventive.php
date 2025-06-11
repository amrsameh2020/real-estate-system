<?php
// File: app/views/maintenance/preventive.php

/**
 * ===================================================================
 * صفحة الصيانة الوقائية (Preventive Maintenance)
 * ===================================================================
 * تتيح هذه الصفحة لمدير النظام جدولة مهام الصيانة الدورية للأصول
 * المختلفة في العقارات (مثل صيانة المصاعد، تنظيف الخزانات، إلخ).
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $scheduled_tasks = PreventiveMaintenance::getAll();
$scheduled_tasks = [
    ['id' => 1, 'task_name' => 'صيانة المصعد الشهري - برج النخيل', 'asset_name' => 'مصعد #1', 'frequency' => 'شهرياً', 'next_due' => '2025-07-01'],
    ['id' => 2, 'task_name' => 'تنظيف خزانات المياه - مجمع الياسمين', 'asset_name' => 'خزان مياه رئيسي', 'frequency' => 'كل 6 أشهر', 'next_due' => '2025-09-15'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'جدولة الصيانة الوقائية';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($scheduled_tasks) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">المهام المجدولة</h5>
            <a href="#" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> جدولة مهمة جديدة
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>اسم المهمة</th>
                            <th>الأصل المرتبط</th>
                            <th>التكرار</th>
                            <th>تاريخ التنفيذ القادم</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scheduled_tasks as $task): ?>
                            <tr>
                                <td><?php e($task['id']); ?></td>
                                <td><strong><?php e($task['task_name']); ?></strong></td>
                                <td><?php e($task['asset_name']); ?></td>
                                <td><?php e($task['frequency']); ?></td>
                                <td class="text-danger"><?php e($task['next_due']); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary" title="تعديل"><i class="bi bi-pencil-fill"></i></button>
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