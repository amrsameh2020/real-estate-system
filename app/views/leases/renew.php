<?php
// File: app/views/leases/renew.php

/**
 * ===================================================================
 * صفحة تجديد عقد إيجار (Renew Lease)
 * ===================================================================
 * تعرض هذه الصفحة نموذجاً لتجديد عقد موجود، مع تعبئة البيانات القديمة.
 */

// الخطوة 1: تحميل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب بيانات العقد الحالي
$lease_id = $_GET['id'] ?? null;
if (!$lease_id) { abort404('لم يتم تحديد معرف العقد.'); }

// $lease = Lease::findById($lease_id);
// if (!$lease) { abort404('العقد المطلوب غير موجود.'); }
$lease = ['id' => $lease_id, 'property_name' => 'برج النخيل', 'unit_number' => 'A-101', 'tenant_name' => 'علي أحمد', 'start_date' => '2024-08-15', 'end_date' => '2025-08-14', 'rent_amount' => '25000'];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'تجديد العقد #' . $lease['id'];

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($lease) {
    // حساب التواريخ الجديدة (سنة من تاريخ الانتهاء القديم)
    $old_end_date = new DateTime($lease['end_date']);
    $new_start_date = (clone $old_end_date)->modify('+1 day')->format('Y-m-d');
    $new_end_date = (clone $old_end_date)->modify('+1 year')->format('Y-m-d');
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold"><?php e($GLOBALS['page_title']); ?></h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">أنت على وشك تجديد العقد للمستأجر "<?php e($lease['tenant_name']); ?>". يرجى مراجعة وتأكيد التفاصيل الجديدة.</div>
            <form action="?url=scripts/leases/handle_renew" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                <input type="hidden" name="old_lease_id" value="<?php e($lease['id']); ?>">

                <h6>تفاصيل العقد الجديد</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label class="form-label">العقار</label><input type="text" class="form-control" value="<?php e($lease['property_name']); ?>" disabled></div>
                    <div class="col-md-6"><label class="form-label">الوحدة</label><input type="text" class="form-control" value="<?php e($lease['unit_number']); ?>" disabled></div>
                    <div class="col-md-6"><label for="start_date" class="form-label">تاريخ البدء الجديد</label><input type="date" class="form-control" name="start_date" id="start_date" value="<?php e($new_start_date); ?>" required></div>
                    <div class="col-md-6"><label for="end_date" class="form-label">تاريخ الانتهاء الجديد</label><input type="date" class="form-control" name="end_date" id="end_date" value="<?php e($new_end_date); ?>" required></div>
                    <div class="col-md-6"><label for="rent_amount" class="form-label">قيمة الإيجار الجديدة (سنوي)</label><input type="number" class="form-control" name="rent_amount" id="rent_amount" value="<?php e($lease['rent_amount']); ?>" required></div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="?url=leases/view/<?php e($lease['id']); ?>" class="btn btn-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-success">تأكيد التجديد</button>
                </div>
            </form>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>