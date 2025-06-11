<?php
// File: app/views/tenant/payments.php

/**
 * ===================================================================
 * صفحة دفعات المستأجر (Tenant Payments Page)
 * ===================================================================
 * تتيح هذه الصفحة للمستأجرين عرض سجل الدفعات الخاصة بهم
 * والفواتير المعلقة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مستأجراً)
Auth::requireRole('Tenant');

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'دفعاتي والفواتير';

// جلب معرف المستأجر الحالي
$tenant_id = Auth::getUserId();

// جلب الفواتير الخاصة بهذا المستأجر
$invoiceModel = new Invoice();
$invoices = $invoiceModel->getInvoicesByTenantId($tenant_id); // افترض وجود هذه الدالة

// جلب الدفعات الخاصة بهذا المستأجر
$paymentModel = new Payment();
$payments = $paymentModel->getPaymentsByTenantId($tenant_id); // افترض وجود هذه الدالة

// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($invoices, $payments) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">دفعاتي والفواتير</h1>
            <a href="?url=tenant/dashboard" class="btn btn-secondary">العودة إلى لوحة التحكم</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">الفواتير المستحقة والمعلقة</h6>
            </div>
            <div class="card-body">
                <?php
                $has_outstanding_invoices = false;
                foreach ($invoices as $invoice) {
                    if ($invoice['status'] === 'Unpaid' || $invoice['status'] === 'Partial') {
                        $has_outstanding_invoices = true;
                        break;
                    }
                }
                ?>
                <?php if ($has_outstanding_invoices): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="outstandingInvoicesTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>الوصف</th>
                                    <th>المبلغ الإجمالي</th>
                                    <th>المبلغ المتبقي</th>
                                    <th>تاريخ الاستحقاق</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <?php if ($invoice['status'] === 'Unpaid' || $invoice['status'] === 'Partial'): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($invoice['id']); ?></td>
                                            <td><?php echo htmlspecialchars($invoice['description']); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($invoice['amount'], 2)); ?> ج.م</td>
                                            <td><?php echo htmlspecialchars(number_format($invoice['amount_due'], 2)); ?> ج.م</td>
                                            <td><?php echo htmlspecialchars($invoice['due_date']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo ($invoice['status'] == 'Unpaid' ? 'danger' : 'warning'); ?>">
                                                    <?php echo htmlspecialchars($invoice['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?url=finance/view_invoice&id=<?php echo htmlspecialchars($invoice['id']); ?>" class="btn btn-info btn-sm">عرض</a>
                                                <?php if ($invoice['status'] !== 'Paid'): ?>
                                                    <button type="button" class="btn btn-success btn-sm record-payment-btn" data-invoice-id="<?php echo htmlspecialchars($invoice['id']); ?>" data-amount-due="<?php echo htmlspecialchars($invoice['amount_due']); ?>">سجل دفعة</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>لا توجد فواتير مستحقة أو معلقة حالياً.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow mb-4 mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">سجل الدفعات</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="paymentHistoryTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>رقم الفاتورة</th>
                                    <th>المبلغ المدفوع</th>
                                    <th>تاريخ الدفعة</th>
                                    <th>طريقة الدفع</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['id']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['invoice_id']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?> ج.م</td>
                                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['notes'] ?: 'لا يوجد'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>لا توجد دفعات مسجلة حتى الآن.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal fade" id="recordPaymentModal" tabindex="-1" role="dialog" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="recordPaymentModalLabel">تسجيل دفعة للفاتورة رقم <span id="modalInvoiceId"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="paymentForm" action="app/scripts/finance/handle_record_payment.php" method="POST">
                        <div class="modal-body">
                            <?php Security::generateCSRF(); ?>
                            <input type="hidden" name="invoice_id" id="paymentInvoiceId">

                            <div class="form-group">
                                <label for="payment_amount">المبلغ المدفوع:</label>
                                <input type="number" step="0.01" class="form-control" id="payment_amount" name="payment_amount" required min="0.01">
                                <small class="form-text text-muted">المبلغ المتبقي: <strong id="amountDueInfo"></strong> ج.م</small>
                            </div>

                            <div class="form-group">
                                <label for="payment_date">تاريخ الدفعة:</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="payment_method">طريقة الدفع:</label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="">-- اختر طريقة الدفع --</option>
                                    <option value="Bank Transfer">تحويل بنكي</option>
                                    <option value="Cash">نقداً</option>
                                    <option value="Online Payment">دفع إلكتروني</option>
                                    <option value="Cheque">شيك</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="notes">ملاحظات (اختياري):</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-success">تسجيل الدفعة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DataTables Initialization
            if (typeof $().DataTable === 'function') {
                $('#outstandingInvoicesTable').DataTable({
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" }
                });
                $('#paymentHistoryTable').DataTable({
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" }
                });
            }

            // Handle "Record Payment" button click
            document.querySelectorAll('.record-payment-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const invoiceId = this.dataset.invoiceId;
                    const amountDue = this.dataset.amountDue;

                    document.getElementById('modalInvoiceId').textContent = invoiceId;
                    document.getElementById('paymentInvoiceId').value = invoiceId;
                    document.getElementById('payment_amount').value = amountDue; // Pre-fill with amount due
                    document.getElementById('payment_amount').max = amountDue; // Set max value
                    document.getElementById('amountDueInfo').textContent = amountDue;

                    $('#recordPaymentModal').modal('show');
                });
            });
        });
    </script>
<?php
};

// الخطوة 5: تضمين القالب العام للمستأجر
require_once APP_ROOT . '/app/views/layouts/tenant_layout.php';
?>