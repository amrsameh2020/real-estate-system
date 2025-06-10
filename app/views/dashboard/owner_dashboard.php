<?php
// File: app/views/dashboard/owner_dashboard.php

/**
 * ===================================================================
 * لوحة تحكم المالك (Owner Dashboard)
 * ===================================================================
 * هذه هي الصفحة الرئيسية التي يراها المالك بعد تسجيل الدخول.
 * تعرض ملخصاً لأداء محفظته العقارية وتفاصيل ممتلكاته.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
// يجب أن يكون المستخدم مالكاً أو مدير نظام للوصول لهذه الصفحة
if (!Auth::hasRole('Owner') && !Auth::hasRole('SystemAdmin')) {
    Session::flash('error', 'غير مصرح لك بالوصول لهذه الصفحة.');
    redirect('public/index.php?url=dashboard');
}

// الخطوة 3: جلب بيانات المالك وممتلكاته
$current_user = Auth::user();
// في تطبيق حقيقي، سيتم جلب هذه البيانات من الـ Models المختلفة
// $owner_properties = Property::findByOwnerId($current_user['id']);
// $financial_summary = Report::getOwnerSummary($current_user['id']);

// بيانات وهمية للعرض حالياً
$summary = [
    'portfolio_value' => '4,500,000',
    'net_income_ytd' => '280,000',
    'occupancy_rate' => '92%',
];

$properties = [
    ['id' => 1, 'name' => 'برج النخيل', 'city' => 'الرياض', 'units_count' => 50, 'occupancy' => '95%'],
    ['id' => 2, 'name' => 'مجمع الياسمين السكني', 'city' => 'جدة', 'units_count' => 20, 'occupancy' => '85%'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'لوحة التحكم الاستثمارية';

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($summary, $properties) {
?>
    <!-- صف بطاقات الإحصائيات الرئيسية -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cash-stack fs-1 text-primary"></i>
                    <h6 class="card-subtitle my-2 text-muted">القيمة التقديرية للمحفظة</h6>
                    <h3 class="card-title mb-0 fw-bold"><?php e($summary['portfolio_value']); ?> <small class="fs-6">ريال</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow fs-1 text-success"></i>
                    <h6 class="card-subtitle my-2 text-muted">صافي الدخل (هذا العام)</h6>
                    <h3 class="card-title mb-0 fw-bold"><?php e($summary['net_income_ytd']); ?> <small class="fs-6">ريال</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-pie-chart-fill fs-1 text-info"></i>
                    <h6 class="card-subtitle my-2 text-muted">متوسط نسبة الإشغال</h6>
                    <h3 class="card-title mb-0 fw-bold"><?php e($summary['occupancy_rate']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول الممتلكات -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 fw-bold">قائمة ممتلكاتي</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>اسم العقار</th>
                                    <th>المدينة</th>
                                    <th>عدد الوحدات</th>
                                    <th>نسبة الإشغال</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($properties)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted p-4">لم يتم إضافة أي عقارات لحسابك بعد.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <td><?php e($property['id']); ?></td>
                                        <td><strong><?php e($property['name']); ?></strong></td>
                                        <td><?php e($property['city']); ?></td>
                                        <td><?php e($property['units_count']); ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?php e($property['occupancy']); ?>;" aria-valuenow="<?php e(intval($property['occupancy'])); ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?php e($property['occupancy']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="?url=owner/properties/view/<?php e($property['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye-fill me-1"></i> عرض التفاصيل
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي لبوابة المالك
require_once APP_ROOT . '/app/views/layouts/owner_layout.php';
