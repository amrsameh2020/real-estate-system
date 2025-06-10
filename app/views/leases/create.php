<?php
// File: app/views/leases/create.php

/**
 * ===================================================================
 * صفحة إنشاء عقد إيجار جديد (Create Lease)
 * ===================================================================
 * تعرض هذه الصفحة معالجاً (wizard) لإدخال بيانات عقد جديد خطوة بخطوة.
 */

// الخطوة 1: تحميل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات اللازمة للنماذج (العقارات، المستأجرين)
// $properties = Property::getAll();
// $tenants = User::findAllByRole('Tenant');
$properties = [['id' => 1, 'name' => 'برج النخيل'], ['id' => 2, 'name' => 'مجمع الياسمين']];
$tenants = [['id' => 1, 'full_name' => 'علي أحمد'], ['id' => 2, 'full_name' => 'فاطمة الزهراء']];


// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إنشاء عقد إيجار جديد';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($properties, $tenants) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold"><?php e($GLOBALS['page_title']); ?></h5>
        </div>
        <div class="card-body">
            <form action="?url=scripts/leases/handle_create" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                
                <!-- الخطوة 1: اختيار الوحدة -->
                <h6>الخطوة 1: اختيار العقار والوحدة</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="property_id" class="form-label">العقار</label>
                        <select class="form-select" id="property_id" name="property_id" required>
                            <option selected disabled value="">اختر عقاراً...</option>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?php e($property['id']); ?>"><?php e($property['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="unit_id" class="form-label">الوحدة الشاغرة</label>
                        <select class="form-select" id="unit_id" name="unit_id" required disabled>
                            <option>اختر عقاراً أولاً...</option>
                        </select>
                    </div>
                </div>

                <!-- الخطوة 2: اختيار المستأجر -->
                <h6>الخطوة 2: تحديد المستأجر</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                         <label for="tenant_user_id" class="form-label">المستأجر</label>
                         <select class="form-select" id="tenant_user_id" name="tenant_user_id" required>
                            <option selected disabled value="">اختر مستأجراً...</option>
                             <?php foreach ($tenants as $tenant): ?>
                                <option value="<?php e($tenant['id']); ?>"><?php e($tenant['full_name']); ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                </div>

                <!-- الخطوة 3: تفاصيل العقد -->
                <h6>الخطوة 3: تفاصيل العقد المالية</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label for="start_date" class="form-label">تاريخ البدء</label><input type="date" class="form-control" name="start_date" id="start_date" required></div>
                    <div class="col-md-6"><label for="end_date" class="form-label">تاريخ الانتهاء</label><input type="date" class="form-control" name="end_date" id="end_date" required></div>
                    <div class="col-md-6"><label for="rent_amount" class="form-label">قيمة الإيجار (سنوي)</label><input type="number" class="form-control" name="rent_amount" id="rent_amount" required></div>
                    <div class="col-md-6"><label for="payment_frequency" class="form-label">دورية الدفع</label><select class="form-select" name="payment_frequency" id="payment_frequency"><option value="Monthly">شهري</option><option value="Quarterly">ربع سنوي</option><option value="Annually">سنوي</option></select></div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="?url=leases" class="btn btn-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">حفظ وإنشاء العقد</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // كود JS لجلب الوحدات عند اختيار عقار
    </script>
<?php
};

// الخطوة 6: تضمين القالب
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>