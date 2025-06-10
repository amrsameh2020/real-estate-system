<?php
// File: app/views/properties/view.php

/**
 * ===================================================================
 * صفحة عرض تفاصيل عقار واحد (Property View)
 * ===================================================================
 * تعرض هذه الصفحة جميع التفاصيل المتعلقة بعقار معين في نظام من التبويبات،
 * مما يسهل الوصول إلى المعلومات المختلفة مثل الوحدات، المستندات، وغيرها.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات لعرضها
// الحصول على معرف العقار من الرابط (تم تمريره من index.php)
$property_id = $_GET['id'] ?? null;

if (!$property_id) {
    abort404('لم يتم تحديد معرف العقار.');
}

// جلب بيانات العقار الأساسية من قاعدة البيانات
// $property = Property::findById($property_id);
// جلب الوحدات التابعة لهذا العقار
// $units = Property::getUnits($property_id);

// إذا لم يتم العثور على العقار، اعرض صفحة خطأ
// if (!$property) {
//     abort404('العقار المطلوب غير موجود.');
// }

// بيانات وهمية للعرض حالياً
$property = ['id' => 1, 'name' => 'برج النخيل', 'property_type' => 'مبنى سكني', 'city' => 'الرياض', 'address' => 'شارع الملك فهد، حي العليا'];
$units = [
    ['id' => 101, 'unit_number' => 'A-101', 'unit_type' => 'شقة', 'status' => 'Occupied', 'bedrooms' => 2, 'bathrooms' => 2],
    ['id' => 102, 'unit_number' => 'A-102', 'unit_type' => 'شقة', 'status' => 'Vacant', 'bedrooms' => 3, 'bathrooms' => 2],
    ['id' => 103, 'unit_number' => 'B-101', 'unit_type' => 'مكتب', 'status' => 'Occupied', 'bedrooms' => null, 'bathrooms' => 1],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'تفاصيل العقار: ' . $property['name'];

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($property, $units) {
?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><?php e($property['name']); ?></h4>
        <div>
            <a href="?url=properties/edit/<?php e($property['id']); ?>" class="btn btn-secondary btn-sm">
                <i class="bi bi-pencil-square me-1"></i> تعديل العقار
            </a>
            <a href="?url=properties" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right me-1"></i> العودة للقائمة
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <!-- نظام التبويبات (Tabs) -->
            <ul class="nav nav-tabs card-header-tabs" id="propertyTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                        <i class="bi bi-info-circle-fill me-1"></i> نظرة عامة
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="units-tab" data-bs-toggle="tab" data-bs-target="#units" type="button" role="tab" aria-controls="units" aria-selected="false">
                        <i class="bi bi-door-open-fill me-1"></i> الوحدات (<?php echo count($units); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">
                        <i class="bi bi-folder-fill me-1"></i> المستندات
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="propertyTabContent">
                <!-- محتوى تبويب نظرة عامة -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    <h5 class="mb-3">المعلومات الأساسية</h5>
                    <dl class="row">
                        <dt class="col-sm-3 text-muted">رقم العقار</dt>
                        <dd class="col-sm-9"><?php e($property['id']); ?></dd>

                        <dt class="col-sm-3 text-muted">اسم العقار</dt>
                        <dd class="col-sm-9"><strong><?php e($property['name']); ?></strong></dd>

                        <dt class="col-sm-3 text-muted">نوع العقار</dt>
                        <dd class="col-sm-9"><?php e($property['property_type']); ?></dd>

                        <dt class="col-sm-3 text-muted">المدينة</dt>
                        <dd class="col-sm-9"><?php e($property['city']); ?></dd>
                        
                        <dt class="col-sm-3 text-muted">العنوان</dt>
                        <dd class="col-sm-9"><?php e($property['address']); ?></dd>
                    </dl>
                </div>
                <!-- محتوى تبويب الوحدات -->
                <div class="tab-pane fade" id="units" role="tabpanel" aria-labelledby="units-tab">
                     <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>رقم الوحدة</th>
                                    <th>النوع</th>
                                    <th>غرف نوم</th>
                                    <th>حمامات</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($units as $unit): ?>
                                <tr>
                                    <td><?php e($unit['id']); ?></td>
                                    <td><strong><?php e($unit['unit_number']); ?></strong></td>
                                    <td><?php e($unit['unit_type']); ?></td>
                                    <td><?php e($unit['bedrooms'] ?? '-'); ?></td>
                                    <td><?php e($unit['bathrooms'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($unit['status'] === 'Occupied'): ?>
                                            <span class="badge bg-info">مؤجرة</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">شاغرة</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                     </div>
                </div>
                <!-- محتوى تبويب المستندات -->
                <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                    <h5 class="mb-3">مستندات العقار</h5>
                    <p class="text-muted">مكان لعرض قائمة بالمستندات المرفوعة مثل صك الملكية، رخصة البناء، إلخ.</p>
                </div>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
