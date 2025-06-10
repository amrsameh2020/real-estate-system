<?php
// File: app/views/leases/view.php

/**
 * ===================================================================
 * صفحة عرض تفاصيل عقد إيجار (Lease View)
 * ===================================================================
 * تعرض هذه الصفحة جميع التفاصيل المتعلقة بعقد إيجار معين،
 * بما في ذلك جدول الدفعات والمستندات المرفقة.
 */

// الخطوة 1: تحميل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
$lease_id = $_GET['id'] ?? null;
if (!$lease_id) { abort404('لم يتم تحديد معرف العقد.'); }

// $lease = Lease::findById($lease_id);
// $payments = Payment::findByLeaseId($lease_id);
// if (!$lease) { abort404('العقد المطلوب غير موجود.'); }

$lease = ['id' => $lease_id, 'property_name' => 'برج النخيل', 'unit_number' => 'A-101', 'tenant_name' => 'علي أحمد', 'tenant_email' => 'ali@example.com', 'start_date' => '2024-08-15', 'end_date' => '2025-08-14', 'rent_amount' => '25000', 'payment_frequency' => 'Quarterly', 'status' => 'Active'];
$invoices = [
    ['due_date' => '2024-08-15', 'amount' => '6250', 'status' => 'Paid', 'paid_on' => '2024-08-14'],
    ['due_date' => '2024-11-15', 'amount' => '6250', 'status' => 'Paid', 'paid_on' => '2024-11-10'],
    ['due_date' => '2025-02-15', 'amount' => '6250', 'status' => 'Unpaid', 'paid_on' => null],
    ['due_date' => '2025-05-15', 'amount' => '6250', 'status' => 'Unpaid', 'paid_on' => null],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'تفاصيل العقد #' . $lease['id'];

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($lease, $invoices) {
?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><?php e($GLOBALS['page_title']); ?></h4>
        <div>
            <a href="?url=leases/renew/<?php e($lease['id']); ?>" class="btn btn-success btn-sm"><i class="bi bi-arrow-clockwise me-1"></i> تجديد العقد</a>
            <a href="?url=leases" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i> العودة للقائمة</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0">تفاصيل العقد</h5></div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-5">المستأجر:</dt><dd class="col-7"><?php e($lease['tenant_name']); ?></dd>
                        <dt class="col-5">العقار:</dt><dd class="col-7"><?php e($lease['property_name']); ?></dd>
                        <dt class="col-5">الوحدة:</dt><dd class="col-7"><?php e($lease['unit_number']); ?></dd>
                        <dt class="col-5">مدة العقد:</dt><dd class="col-7"><?php e($lease['start_date']); ?> إلى <?php e($lease['end_date']); ?></dd>
                        <dt class="col-5">قيمة الإيجار:</dt><dd class="col-7"><?php e(number_format($lease['rent_amount'])); ?> ريال/سنة</dd>
                        <dt class="col-5">دورية الدفع:</dt><dd class="col-7"><?php e($lease['payment_frequency']); ?></dd>
                        <dt class="col-5">الحالة:</dt><dd class="col-7"><span class="badge bg-success"><?php e($lease['status']); ?></span></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">جدول الدفعات</h5></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr><th>تاريخ الاستحقاق</th><th>المبلغ</th><th>الحالة</th><th>تاريخ الدفع</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><?php e($invoice['due_date']); ?></td>
                                    <td><?php e(number_format($invoice['amount'])); ?> ريال</td>
                                    <td>
                                        <span class="badge <?php echo ($invoice['status'] === 'Paid') ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ($invoice['status'] === 'Paid') ? 'مدفوعة' : 'مستحقة'; ?>
                                        </span>
                                    </td>
                                    <td><?php e($invoice['paid_on'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>