<?php
// File: app/views/finance/invoices.php

/**
 * ===================================================================
 * صفحة عرض قائمة الفواتير (Invoices Index)
 * ===================================================================
 * تعرض هذه الصفحة جدولاً بجميع الفواتير في النظام، مع أدوات للبحث
 * والفلترة، وإجراءات سريعة مثل تسجيل دفعة أو عرض تفاصيل الفاتورة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
// فقط مدير النظام والمحاسب يمكنهم الوصول لهذه الصفحة
if (!Auth::hasRole('SystemAdmin') && !Auth::hasRole('Accountant')) {
    Auth::requireRole('SystemAdmin'); // سيقوم هذا بإعادة التوجيه مع رسالة خطأ
}

// الخطوة 3: جلب البيانات لعرضها
// $invoices = Invoice::getAll();
// للتوضيح، سنستخدم بيانات وهمية حالياً
$invoices = [
    ['id' => 101, 'tenant_name' => 'علي أحمد', 'property_name' => 'برج النخيل', 'unit_number' => 'A-101', 'total_amount' => '6250.00', 'due_date' => '2025-02-15', 'status' => 'Unpaid'],
    ['id' => 100, 'tenant_name' => 'علي أحمد', 'property_name' => 'برج النخيل', 'unit_number' => 'A-101', 'total_amount' => '6250.00', 'due_date' => '2024-11-15', 'status' => 'Paid'],
    ['id' => 99, 'tenant_name' => 'فاطمة الزهراء', 'property_name' => 'مجمع الياسمين', 'unit_number' => 'فيلا 23', 'total_amount' => '12500.00', 'due_date' => '2024-09-01', 'status' => 'Paid'],
    ['id' => 98, 'tenant_name' => 'شركة الحلول', 'property_name' => 'مركز الأعمال', 'unit_number' => 'مكتب 305', 'total_amount' => '75000.00', 'due_date' => '2024-07-01', 'status' => 'Overdue'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة الفواتير والإيرادات';

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($invoices) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0 fw-bold">قائمة الفواتير</h5>
            <div class="d-flex gap-2">
                <a href="#" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i> تصدير إلى Excel
                </a>
                <a href="?url=finance/handle_create_invoice" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> إنشاء فاتورة يدوية
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">رقم الفاتورة</th>
                            <th scope="col">المستأجر</th>
                            <th scope="col">العقار / الوحدة</th>
                            <th scope="col">المبلغ الإجمالي</th>
                            <th scope="col">تاريخ الاستحقاق</th>
                            <th scope="col" class="text-center">الحالة</th>
                            <th scope="col" class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted p-4">لا توجد فواتير لعرضها.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><strong>#<?php e($invoice['id']); ?></strong></td>
                                    <td><?php e($invoice['tenant_name']); ?></td>
                                    <td>
                                        <small class="d-block text-muted"><?php e($invoice['property_name']); ?></small>
                                        <span><?php e($invoice['unit_number']); ?></span>
                                    </td>
                                    <td class="fw-bold"><?php e(number_format($invoice['total_amount'], 2)); ?> ريال</td>
                                    <td><?php e($invoice['due_date']); ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $status_map = [
                                                'Paid' => ['class' => 'bg-success', 'text' => 'مدفوعة'],
                                                'Unpaid' => ['class' => 'bg-info', 'text' => 'مستحقة'],
                                                'Overdue' => ['class' => 'bg-danger', 'text' => 'متأخرة'],
                                                'PartiallyPaid' => ['class' => 'bg-warning text-dark', 'text' => 'مدفوعة جزئياً'],
                                            ];
                                            $status_info = $status_map[$invoice['status']] ?? ['class' => 'bg-secondary', 'text' => 'غير معروف'];
                                        ?>
                                        <span class="badge <?php echo $status_info['class']; ?>"><?php e($status_info['text']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="?url=finance/view_invoice/<?php e($invoice['id']); ?>" class="btn btn-outline-secondary" title="عرض وطباعة">
                                                <i class="bi bi-printer-fill"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline-primary" title="إرسال تذكير">
                                                 <i class="bi bi-envelope-fill"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-success" onclick="recordPayment(<?php e($invoice['id']); ?>)" title="تسجيل دفعة">
                                                 <i class="bi bi-cash-coin"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <p class="text-muted mb-0 small">إجمالي عدد الفواتير: <?php echo count($invoices); ?></p>
        </div>
    </div>

    <script>
    // دالة لفتح نافذة منبثقة لتسجيل الدفعة
    function recordPayment(invoiceId) {
        Swal.fire({
            title: 'تسجيل دفعة للفاتورة #' + invoiceId,
            html: `
                <input type="number" id="amount" class="swal2-input" placeholder="المبلغ المدفوع">
                <input type="date" id="date" class="swal2-input" value="<?php echo date('Y-m-d'); ?>">
                <select id="method" class="swal2-select">
                    <option value="BankTransfer">تحويل بنكي</option>
                    <option value="Cash">نقداً</option>
                    <option value="CreditCard">بطاقة ائتمان</option>
                </select>`,
            confirmButtonText: 'تأكيد الدفع',
            showCancelButton: true,
            cancelButtonText: 'إلغاء',
            preConfirm: () => {
                const amount = Swal.getPopup().querySelector('#amount').value;
                const date = Swal.getPopup().querySelector('#date').value;
                if (!amount || !date) {
                    Swal.showValidationMessage(`الرجاء إدخال المبلغ وتاريخ الدفع`);
                }
                return { amount: amount, date: date, method: Swal.getPopup().querySelector('#method').value };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // هنا سيتم إرسال البيانات إلى ملف المعالجة عبر AJAX
                // $.post('?url=scripts/finance/handle_record_payment', { ...result.value, invoice_id: invoiceId });
                Swal.fire('تم!', 'تم تسجيل الدفعة بنجاح.', 'success');
            }
        });
    }
    </script>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
