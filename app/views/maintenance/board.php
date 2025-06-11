<?php
// File: app/views/maintenance/board.php

/**
 * ===================================================================
 * صفحة لوحة طلبات الصيانة (Kanban Board)
 * ===================================================================
 * تعرض هذه الصفحة جميع طلبات الصيانة في لوحة مرئية مقسمة حسب الحالة،
 * مما يسهل متابعة سير العمل وتوزيع المهام.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin'); // أو 'MaintenanceManager'

// الخطوة 3: جلب البيانات وتصنيفها
// $all_requests = MaintenanceRequest::getAll();
$all_requests = [
    ['id' => 87, 'title' => 'تسريب في صنبور المطبخ', 'unit_number' => 'A-101', 'property_name' => 'برج النخيل', 'status' => 'InProgress', 'priority' => 'High'],
    ['id' => 88, 'title' => 'المصعد متوقف', 'unit_number' => 'Lobby', 'property_name' => 'برج النخيل', 'status' => 'New', 'priority' => 'Urgent'],
    ['id' => 89, 'title' => 'زجاج نافذة مكسور', 'unit_number' => 'فيلا 23', 'property_name' => 'مجمع الياسمين', 'status' => 'Assigned', 'priority' => 'Medium'],
    ['id' => 90, 'title' => 'الإنارة لا تعمل في الممر', 'unit_number' => 'Floor 5', 'property_name' => 'مركز الأعمال', 'status' => 'Completed', 'priority' => 'Low'],
];

// تصنيف الطلبات في مصفوفات حسب الحالة
$requests_by_status = [
    'New' => [],
    'Assigned' => [],
    'InProgress' => [],
    'Completed' => [],
];
foreach ($all_requests as $request) {
    if (array_key_exists($request['status'], $requests_by_status)) {
        $requests_by_status[$request['status']][] = $request;
    }
}

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'لوحة طلبات الصيانة';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($requests_by_status) {
?>
    <style>
        .kanban-board { display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 1rem; }
        .kanban-column { min-width: 300px; max-width: 300px; background-color: #e9ecef; border-radius: 0.5rem; }
        .kanban-card { cursor: grab; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 fw-bold">لوحة طلبات الصيانة</h4>
        <a href="?url=maintenance/create_request" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> إنشاء طلب جديد
        </a>
    </div>

    <div class="kanban-board">
        <?php foreach ($requests_by_status as $status => $requests): ?>
            <div class="kanban-column">
                <div class="p-3">
                    <h6 class="fw-bold text-uppercase text-muted"><?php e($status); ?> <span class="badge bg-secondary rounded-pill"><?php echo count($requests); ?></span></h6>
                    <div class="requests-list mt-3 d-flex flex-column gap-3">
                        <?php foreach ($requests as $request): ?>
                            <div class="card shadow-sm kanban-card">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="card-title fw-bold mb-1"><?php e($request['title']); ?></h6>
                                        <span class="badge bg-primary"><?php e($request['priority']); ?></span>
                                    </div>
                                    <p class="card-text text-muted small mb-2">
                                        <?php e($request['property_name']); ?> - <?php e($request['unit_number']); ?>
                                    </p>
                                    <a href="?url=maintenance/view_request/<?php e($request['id']); ?>" class="btn btn-sm btn-outline-secondary w-100">عرض التفاصيل</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($requests)): ?>
                            <div class="text-center text-muted p-4 border-dashed">لا توجد طلبات</div>
                        <?php endif; ?>
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