<?php
// File: app/views/maintenance/board.php

/**
 * ===================================================================
 * صفحة لوحة طلبات الصيانة - نسخة متجاوبة (Kanban Board - Responsive)
 * ===================================================================
 * تعرض هذه الصفحة جميع طلبات الصيانة في لوحة مرئية مقسمة حسب الحالة.
 * تم تعديلها لتكون متجاوبة بالكامل باستخدام نظام شبكة Bootstrap،
 * مما يمنع التمرير الأفقي على الشاشات الصغيرة.
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
    ['id' => 91, 'title' => 'مشكلة في التكييف المركزي', 'unit_number' => 'B-304', 'property_name' => 'برج النخيل', 'status' => 'New', 'priority' => 'High'],
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

// **FIX:** Define the helper function *before* the closure so it's available in the global scope.
function getPriorityBadgeClass($priority) {
    switch ($priority) {
        case 'Urgent':
            return 'bg-danger';
        case 'High':
            return 'bg-warning text-dark';
        case 'Medium':
            return 'bg-info text-dark';
        case 'Low':
        default:
            return 'bg-secondary';
    }
}

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'لوحة طلبات الصيانة';

// الخطوة 5: تعريف دالة المحتوى
// **FIX:** Removed the string 'getPriorityBadgeClass' from the `use` statement.
$content_callback = function() use ($requests_by_status) {
?>
<style>
    .kanban-column {
        background-color: #f1f5f9;
        border-radius: 0.75rem;
        padding: 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .kanban-header {
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
    }
    .requests-list {
        flex-grow: 1;
        overflow-y: auto;
        max-height: 65vh;
    }
    .kanban-card {
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .kanban-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
    }
    .border-dashed {
        border: 2px dashed #ced4da;
        border-radius: 0.5rem;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">لوحة طلبات الصيانة</h4>
    <a href="?url=maintenance/create_request" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill me-2"></i> إنشاء طلب جديد
    </a>
</div>

<div class="row g-4">
    <?php foreach ($requests_by_status as $status => $requests): ?>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="kanban-column">
                <div class="kanban-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-uppercase text-muted mb-0"><?php e($status); ?></h6>
                    <span class="badge bg-dark rounded-pill fs-6"><?php echo count($requests); ?></span>
                </div>
                <div class="requests-list d-flex flex-column gap-3">
                    <?php foreach ($requests as $request): ?>
                        <div class="card shadow-sm kanban-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title fw-bold mb-0" style="max-width: 80%;"><?php e($request['title']); ?></h6>
                                    <span class="badge <?php echo getPriorityBadgeClass($request['priority']); ?>"><?php e($request['priority']); ?></span>
                                </div>
                                <p class="card-text text-muted small mb-3">
                                    <i class="bi bi-building me-1"></i><?php e($request['property_name']); ?> -
                                    <i class="bi bi-hash me-1"></i><?php e($request['unit_number']); ?>
                                </p>
                                <a href="?url=maintenance/view_request/<?php e($request['id']); ?>" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="bi bi-eye-fill me-1"></i> عرض التفاصيل
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($requests)): ?>
                        <div class="text-center text-muted p-5 border-dashed">
                            <i class="bi bi-inbox fs-2"></i>
                            <p class="mt-2 mb-0">لا توجد طلبات</p>
                        </div>
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
