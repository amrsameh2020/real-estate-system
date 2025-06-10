<?php
// File: app/views/leases/index.php

/**
 * ===================================================================
 * صفحة عرض قائمة عقود الإيجار (Leases Index)
 * ===================================================================
 * تعرض هذه الصفحة جدولاً بجميع عقود الإيجار في النظام،
 * مع فلاتر وتمييز للحالات المختلفة (نشط، منتهي، ...).
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin'); // أو 'Accountant'

// الخطوة 3: جلب البيانات
// $leases = Lease::getAll();
$leases = [
    ['id' => 1, 'property_name' => 'برج النخيل', 'unit_number' => 'A-101', 'tenant_name' => 'علي أحمد', 'start_date' => '2024-08-15', 'end_date' => '2025-08-14', 'status' => 'Active'],
    ['id' => 2, 'property_name' => 'مجمع الياسمين', 'unit_number' => 'فيلا 23', 'tenant_name' => 'فاطمة الزهراء', 'start_date' => '2023-09-01', 'end_date' => '2024-08-31', 'status' => 'ExpiringSoon'],
    ['id' => 3, 'property_name' => 'مركز الأعمال', 'unit_number' => 'مكتب 305', 'tenant_name' => 'شركة الحلول المبتكرة', 'start_date' => '2022-01-01', 'end_date' => '2023-12-31', 'status' => 'Expired'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة العقود والإيجارات';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($leases) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">قائمة عقود الإيجار</h5>
            <a href="?url=leases/create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> إنشاء عقد جديد
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>العقار / الوحدة</th>
                            <th>المستأجر</th>
                            <th>تاريخ البدء</th>
                            <th>تاريخ الانتهاء</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leases as $lease): ?>
                            <tr>
                                <td><?php e($lease['id']); ?></td>
                                <td>
                                    <strong><?php e($lease['property_name']); ?></strong>
                                    <small class="d-block text-muted"><?php e($lease['unit_number']); ?></small>
                                </td>
                                <td><?php e($lease['tenant_name']); ?></td>
                                <td><?php e($lease['start_date']); ?></td>
                                <td><?php e($lease['end_date']); ?></td>
                                <td class="text-center">
                                    <?php 
                                        $status_badge = '';
                                        switch ($lease['status']) {
                                            case 'Active': $status_badge = 'bg-success'; break;
                                            case 'ExpiringSoon': $status_badge = 'bg-warning text-dark'; break;
                                            case 'Expired': $status_badge = 'bg-secondary'; break;
                                            default: $status_badge = 'bg-light text-dark'; break;
                                        }
                                    ?>
                                    <span class="badge <?php echo $status_badge; ?>"><?php e($lease['status']); ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="?url=leases/view/<?php e($lease['id']); ?>" class="btn btn-sm btn-outline-info" title="عرض"><i class="bi bi-eye-fill"></i></a>
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