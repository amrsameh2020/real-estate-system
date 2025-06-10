<?php
// File: app/views/properties/create.php

/**
 * ===================================================================
 * صفحة إنشاء عقار جديد (Create Property)
 * ===================================================================
 * تعرض هذه الصفحة نموذجاً لإدخال بيانات عقار جديد.
 * عند الإرسال، يتم توجيه البيانات إلى ملف معالجة خاص لحفظها في قاعدة البيانات.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'إضافة عقار جديد';

// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><?php e($GLOBALS['page_title']); ?></h5>
            <a href="?url=properties" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right me-1"></i> العودة للقائمة
            </a>
        </div>
        <div class="card-body">
            <!-- ملاحظة: يجب إنشاء ملف handle_create.php لاحقاً لمعالجة هذا النموذج -->
            <form action="?url=scripts/properties/handle_create" method="POST" class="needs-validation" novalidate>
                <!-- CSRF Token for security -->
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">اسم العقار</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">حقل اسم العقار مطلوب.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="property_type" class="form-label">نوع العقار</label>
                        <select class="form-select" id="property_type" name="property_type" required>
                            <option value="" selected disabled>اختر نوع العقار...</option>
                            <option value="مبنى سكني">مبنى سكني</option>
                            <option value="مبنى تجاري">مبنى تجاري</option>
                            <option value="مجمع فلل">مجمع فلل</option>
                            <option value="أرض">أرض</option>
                        </select>
                        <div class="invalid-feedback">الرجاء اختيار نوع العقار.</div>
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label">العنوان</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="مثال: شارع الملك فهد، حي العليا" required>
                         <div class="invalid-feedback">حقل العنوان مطلوب.</div>
                    </div>

                     <div class="col-md-6">
                        <label for="city" class="form-label">المدينة</label>
                        <input type="text" class="form-control" id="city" name="city" required>
                        <div class="invalid-feedback">حقل المدينة مطلوب.</div>
                    </div>

                     <div class="col-md-6">
                        <label for="country" class="form-label">الدولة</label>
                        <input type="text" class="form-control" id="country" name="country" value="المملكة العربية السعودية" required>
                         <div class="invalid-feedback">حقل الدولة مطلوب.</div>
                    </div>

                    <hr class="my-4">

                    <h6 class="text-muted">معلومات إضافية (اختياري)</h6>
                     <div class="col-md-6">
                        <label for="latitude" class="form-label">خط العرض (Latitude)</label>
                        <input type="text" class="form-control" id="latitude" name="latitude">
                    </div>

                     <div class="col-md-6">
                        <label for="longitude" class="form-label">خط الطول (Longitude)</label>
                        <input type="text" class="form-control" id="longitude" name="longitude">
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end">
                    <a href="?url=properties" class="btn btn-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">حفظ العقار</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
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

