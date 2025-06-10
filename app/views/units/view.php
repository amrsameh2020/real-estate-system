<?php
// File: app/views/units/view.php

/**
 * ===================================================================
 * صفحة عرض تفاصيل وحدة (Unit View)
 * ===================================================================
 * تعرض هذه الصفحة جميع التفاصيل المتعلقة بوحدة عقارية معينة، مثل
 * مواصفاتها، حالتها، والمرافق الخاصة بها.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات لعرضها
// الحصول على معرف الوحدة من الرابط
$unit_id = $_GET['id'] ?? null;

if (!$unit_id || !is_numeric($unit_id)) {
    abort404('معرف الوحدة غير صالح.');
}

// جلب بيانات الوحدة الأساسية من قاعدة البيانات
// $unit = Unit::findById($unit_id);
// if (!$unit) {
//     abort404('الوحدة المطلوبة غير موجودة.');
// }

// بيانات وهمية للعرض حالياً
$unit = [
    'id' => $unit_id, 
    'unit_number' => 'A-102', 
    'unit_type' => 'شقة', 
    'status' => 'Vacant', 
    'floor_number' => 10,
    'area_sqm' => 150.50,
    'bedrooms' => 3,
    'bathrooms' => 2,
    'market_rent' => '35000.00',
    'amenities' => json_encode(['مطبخ مجهز', 'شرفة', 'موقف خاص']),
    'property_id' => 1,
    'property_name' => 'برج النخيل'
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'تفاصيل الوحدة: ' . $unit['unit_number'];

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($unit) {
?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <span class="text-muted">العقار: </span>
            <a href="?url=properties/view/<?php e($unit['property_id']); ?>" class="text-decoration-none"><?php e($unit['property_name']); ?></a>
            <i class="bi bi-chevron-left small"></i>
            <span class="fw-bold"><?php e($unit['unit_number']); ?></span>
        </h4>
        <div>
            <a href="?url=units/edit/<?php e($unit['id']); ?>" class="btn btn-secondary btn-sm">
                <i class="bi bi-pencil-square me-1"></i> تعديل الوحدة
            </a>
            <a href="?url=properties/view/<?php e($unit['property_id']); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right me-1"></i> العودة للعقار
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">المعلومات الأساسية للوحدة</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- قسم المعلومات الأساسية -->
                <div class="col-md-7">
                    <dl class="row g-3">
                        <dt class="col-sm-4 text-muted">رقم الوحدة</dt>
                        <dd class="col-sm-8 fs-5"><strong><?php e($unit['unit_number']); ?></strong></dd>

                        <dt class="col-sm-4 text-muted">نوع الوحدة</dt>
                        <dd class="col-sm-8"><?php e($unit['unit_type']); ?></dd>

                        <dt class="col-sm-4 text-muted">الطابق</dt>
                        <dd class="col-sm-8"><?php e($unit['floor_number']); ?></dd>

                        <dt class="col-sm-4 text-muted">المساحة</dt>
                        <dd class="col-sm-8"><?php e($unit['area_sqm']); ?> متر مربع</dd>

                        <dt class="col-sm-4 text-muted">غرف النوم</dt>
                        <dd class="col-sm-8"><?php e($unit['bedrooms']); ?></dd>

                        <dt class="col-sm-4 text-muted">دورات المياه</dt>
                        <dd class="col-sm-8"><?php e($unit['bathrooms']); ?></dd>
                    </dl>
                </div>
                <!-- قسم الحالة والإيجار -->
                <div class="col-md-5 border-end">
                     <div class="text-center p-3">
                        <h6 class="text-muted">الحالة الحالية</h6>
                        <?php if ($unit['status'] === 'Vacant'): ?>
                            <h3 class="fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i>شاغرة</h3>
                        <?php else: ?>
                            <h3 class="fw-bold text-info"><i class="bi bi-person-fill me-2"></i>مؤجرة</h3>
                        <?php endif; ?>
                     </div>
                     <hr>
                     <div class="text-center p-3">
                        <h6 class="text-muted">قيمة الإيجار التقديرية</h6>
                        <h3 class="fw-bold text-primary"><?php e(number_format($unit['market_rent'])); ?> <small class="fs-6">ريال/سنة</small></h3>
                     </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">المرافق ووسائل الراحة</h5>
        </div>
        <div class="card-body">
            <?php $amenities = json_decode($unit['amenities'], true); ?>
            <?php if (!empty($amenities)): ?>
                <ul class="list-unstyled d-flex flex-wrap">
                    <?php foreach ($amenities as $amenity): ?>
                        <li class="me-3 mb-2 badge bg-light text-dark fs-6 fw-normal border">
                            <i class="bi bi-check-lg text-success"></i> <?php e($amenity); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">لم يتم تحديد أي مرافق لهذه الوحدة.</p>
            <?php endif; ?>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
