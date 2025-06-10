<?php
// File: app/views/units/edit.php

/**
 * ===================================================================
 * صفحة تعديل وحدة (Edit Unit)
 * ===================================================================
 * تعرض هذه الصفحة نموذجاً لتعديل بيانات وحدة موجودة.
 * يتم جلب البيانات الحالية للوحدة وتعبئتها في حقول النموذج.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات لعرضها
$unit_id = $_GET['id'] ?? null;

if (!$unit_id || !is_numeric($unit_id)) {
    abort404('معرف الوحدة غير صالح.');
}

// جلب بيانات الوحدة الحالية من قاعدة البيانات
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
$page_title = 'تعديل الوحدة: ' . $unit['unit_number'];

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($unit) {
    // تحويل المرافق من JSON إلى نص للعرض في حقل النص
    $amenities_array = json_decode($unit['amenities'], true);
    $amenities_text = is_array($amenities_array) ? implode(', ', $amenities_array) : '';
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><?php e($GLOBALS['page_title']); ?></h5>
            <a href="?url=units/view/<?php e($unit['id']); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right me-1"></i> العودة لتفاصيل الوحدة
            </a>
        </div>
        <div class="card-body">
            <!-- ملاحظة: يجب إنشاء ملف handle_edit.php في مجلد scripts/units/ لاحقاً -->
            <form action="?url=scripts/units/handle_edit" method="POST" class="needs-validation" novalidate>
                <!-- CSRF Token for security -->
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                <!-- Unit ID to identify the record to update -->
                <input type="hidden" name="id" value="<?php e($unit['id']); ?>">
                <!-- Property ID for redirection -->
                <input type="hidden" name="property_id" value="<?php e($unit['property_id']); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="unit_number" class="form-label">رقم الوحدة</label>
                        <input type="text" class="form-control" id="unit_number" name="unit_number" value="<?php e($unit['unit_number']); ?>" required>
                        <div class="invalid-feedback">حقل رقم الوحدة مطلوب.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="unit_type" class="form-label">نوع الوحدة</label>
                        <input type="text" class="form-control" id="unit_type" name="unit_type" value="<?php e($unit['unit_type']); ?>" required>
                        <div class="invalid-feedback">حقل نوع الوحدة مطلوب.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="floor_number" class="form-label">رقم الطابق</label>
                        <input type="number" class="form-control" id="floor_number" name="floor_number" value="<?php e($unit['floor_number']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="area_sqm" class="form-label">المساحة (متر مربع)</label>
                        <input type="text" class="form-control" id="area_sqm" name="area_sqm" value="<?php e($unit['area_sqm']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="bedrooms" class="form-label">عدد غرف النوم</label>
                        <input type="number" class="form-control" id="bedrooms" name="bedrooms" value="<?php e($unit['bedrooms']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="bathrooms" class="form-label">عدد دورات المياه</label>
                        <input type="number" class="form-control" id="bathrooms" name="bathrooms" value="<?php e($unit['bathrooms']); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="market_rent" class="form-label">قيمة الإيجار التقديرية (سنوي)</label>
                        <input type="text" class="form-control" id="market_rent" name="market_rent" value="<?php e($unit['market_rent']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">الحالة</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Vacant" <?php echo ($unit['status'] === 'Vacant') ? 'selected' : ''; ?>>شاغرة</option>
                            <option value="Occupied" <?php echo ($unit['status'] === 'Occupied') ? 'selected' : ''; ?>>مؤجرة</option>
                            <option value="UnderMaintenance" <?php echo ($unit['status'] === 'UnderMaintenance') ? 'selected' : ''; ?>>قيد الصيانة</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="amenities" class="form-label">المرافق ووسائل الراحة</label>
                        <textarea class="form-control" id="amenities" name="amenities" rows="3"><?php e($amenities_text); ?></textarea>
                        <div class="form-text">أدخل المرافق مفصولة بفاصلة (مثال: مطبخ مجهز, شرفة, موقف خاص).</div>
                    </div>

                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end">
                    <a href="?url=units/view/<?php e($unit['id']); ?>" class="btn btn-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-success">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Bootstrap form validation
        (function () {
          'use strict'
          var forms = document.querySelectorAll('.needs-validation')
          Array.prototype.slice.call(forms)
            .forEach(function (form) {
              form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                  event.preventDefault()
                  event.stopPropagation()
                }
                form.classList.add('was-validated')
              }, false)
            })
        })()
    </script>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
