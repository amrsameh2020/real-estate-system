<?php
// File: app/views/dashboard/admin_dashboard.php

/**
 * ===================================================================
 * لوحة تحكم مدير النظام (Admin Dashboard)
 * ===================================================================
 * هذه هي الصفحة الرئيسية التي يراها مدير النظام بعد تسجيل الدخول.
 * تعرض ملخصاً شاملاً وفورياً لأهم مؤشرات الأداء في النظام.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
// التأكد من أن المستخدم الحالي هو مدير نظام فقط
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات لعرضها في لوحة التحكم
// في تطبيق حقيقي، سيتم جلب هذه البيانات من الـ Models المختلفة
// $total_properties = Property::count();
// $total_units = Unit::count();
// $active_leases = Lease::countActive();
// $open_maintenance = MaintenanceRequest::countOpen();
// $recent_activities = AuditLog::getRecent();

// بيانات وهمية للعرض حالياً
$stats = [
    'total_properties' => 15,
    'total_units' => 250,
    'active_leases' => 210,
    'open_maintenance' => 8,
];

$recent_leases = [
    ['property_name' => 'برج النخيل', 'unit_number' => 'A-101', 'tenant_name' => 'علي أحمد', 'end_date' => '2025-08-15'],
    ['property_name' => 'مجمع الياسمين', 'unit_number' => 'فيلا 23', 'tenant_name' => 'فاطمة الزهراء', 'end_date' => '2025-09-01'],
];


// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'لوحة تحكم مدير النظام';

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($stats, $recent_leases) {
?>
    <!-- صف بطاقات الإحصائيات الرئيسية -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary text-white rounded-3 p-3 me-3">
                        <i class="bi bi-building fs-2"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-1 text-muted">إجمالي العقارات</h6>
                        <h2 class="card-title mb-0 fw-bold"><?php e($stats['total_properties']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info text-white rounded-3 p-3 me-3">
                        <i class="bi bi-door-open-fill fs-2"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-1 text-muted">إجمالي الوحدات</h6>
                        <h2 class="card-title mb-0 fw-bold"><?php e($stats['total_units']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success text-white rounded-3 p-3 me-3">
                        <i class="bi bi-file-earmark-text-fill fs-2"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-1 text-muted">العقود النشطة</h6>
                        <h2 class="card-title mb-0 fw-bold"><?php e($stats['active_leases']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning text-white rounded-3 p-3 me-3">
                        <i class="bi bi-tools fs-2"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-1 text-muted">طلبات الصيانة المفتوحة</h6>
                        <h2 class="card-title mb-0 fw-bold"><?php e($stats['open_maintenance']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- صف الرسوم البيانية والجداول -->
    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 fw-bold">نظرة عامة على الإشغال</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">مكان لعرض رسم بياني (مثل Chart.js) يوضح نسبة الوحدات المؤجرة والشاغرة وقيد الصيانة.</p>
                    <canvas id="occupancyChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
             <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 fw-bold">العقود التي تنتهي قريباً</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>المستأجر</th>
                                    <th>العقار / الوحدة</th>
                                    <th>تاريخ الانتهاء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_leases as $lease): ?>
                                <tr>
                                    <td><?php e($lease['tenant_name']); ?></td>
                                    <td>
                                        <small class="d-block text-muted"><?php e($lease['property_name']); ?></small>
                                        <strong><?php e($lease['unit_number']); ?></strong>
                                    </td>
                                    <td><?php e($lease['end_date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مكان لعناصر إضافية مثل آخر الأنشطة في النظام -->

<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';

