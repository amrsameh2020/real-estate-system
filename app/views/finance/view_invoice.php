<?php
// File: app/views/finance/view_invoice.php

/**
 * ===================================================================
 * صفحة عرض وطباعة فاتورة (View Invoice)
 * ===================================================================
 * تعرض هذه الصفحة فاتورة مفصلة بتصميم مناسب للطباعة.
 * تحتوي على جميع تفاصيل الفاتورة، معلومات الشركة، ومعلومات العميل.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
if (!Auth::hasRole('SystemAdmin') && !Auth::hasRole('Accountant')) {
    Auth::requireRole('SystemAdmin');
}

// الخطوة 3: جلب البيانات
$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id || !is_numeric($invoice_id)) {
    abort404('معرف الفاتورة غير صالح.');
}

// $invoice = Invoice::findById($invoice_id);
// if (!$invoice) {
//     abort404('الفاتورة المطلوبة غير موجودة.');
// }

// بيانات وهمية للعرض حالياً
$invoice = [
    'id' => $invoice_id,
    'tenant_name' => 'علي أحمد عبدالله',
    'tenant_address' => 'حي الياسمين، شارع الأمير محمد، الرياض',
    'property_name' => 'برج النخيل',
    'unit_number' => 'A-101',
    'issue_date' => date('Y-m-d'),
    'due_date' => '2025-02-15',
    'total_amount' => '6250.00',
    'vat_percentage' => 15,
    'amount_before_vat' => 5434.78,
    'vat_amount' => 815.22,
    'status' => 'Unpaid',
    'items' => [
        ['description' => 'إيجار الوحدة A-101 (الربع الأول)', 'amount' => '5434.78'],
    ]
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'عرض الفاتورة #' . $invoice['id'];

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($invoice) {
?>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .invoice-box, .invoice-box * {
                visibility: visible;
            }
            .invoice-box {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h4 class="mb-0">تفاصيل الفاتورة</h4>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer-fill me-1"></i> طباعة / حفظ PDF
            </button>
            <a href="?url=finance/invoices" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right me-1"></i> العودة للقائمة
            </a>
        </div>
    </div>

    <div class="card shadow-sm invoice-box">
        <div class="card-body p-5">
            <div class="row mb-4">
                <div class="col-6">
                    <h2 class="text-primary fw-bold"><?php echo APP_NAME; ?></h2>
                    <p class="text-muted">شركة إدارة الأملاك والعقارات</p>
                    <p class="text-muted mb-0">شارع العليا، الرياض، المملكة العربية السعودية</p>
                </div>
                <div class="col-6 text-end">
                    <h1 class="fw-bold mb-0" style="color: #6c757d;">فاتورة</h1>
                    <p class="mb-0">#<?php e($invoice['id']); ?></p>
                    <div>
                        <?php 
                            $status_map = [
                                'Paid' => ['class' => 'bg-success', 'text' => 'مدفوعة'],
                                'Unpaid' => ['class' => 'bg-info', 'text' => 'مستحقة'],
                                'Overdue' => ['class' => 'bg-danger', 'text' => 'متأخرة'],
                            ];
                            $status_info = $status_map[$invoice['status']] ?? ['class' => 'bg-secondary', 'text' => 'غير معروف'];
                        ?>
                        <span class="badge fs-6 <?php echo $status_info['class']; ?>"><?php e($status_info['text']); ?></span>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-6">
                    <strong>فاتورة إلى:</strong>
                    <address class="mb-0">
                        <strong><?php e($invoice['tenant_name']); ?></strong><br>
                        <?php e($invoice['tenant_address']); ?>
                    </address>
                </div>
                <div class="col-6 text-end">
                    <strong>تاريخ الإصدار:</strong> <?php e($invoice['issue_date']); ?><br>
                    <strong>تاريخ الاستحقاق:</strong> <?php e($invoice['due_date']); ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>الوصف</th>
                            <th class="text-end">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($invoice['items'] as $index => $item): ?>
                        <tr>
                            <td class="text-center"><?php echo $index + 1; ?></td>
                            <td>
                                <?php e($item['description']); ?>
                                <small class="d-block text-muted">العقار: <?php e($invoice['property_name']); ?> - الوحدة: <?php e($invoice['unit_number']); ?></small>
                            </td>
                            <td class="text-end"><?php e(number_format($item['amount'], 2)); ?> ريال</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="2" class="text-end">المجموع الفرعي</td>
                            <td class="text-end"><?php e(number_format($invoice['amount_before_vat'], 2)); ?> ريال</td>
                        </tr>
                         <tr>
                            <td colspan="2" class="text-end">ضريبة القيمة المضافة (<?php e($invoice['vat_percentage']); ?>%)</td>
                            <td class="text-end"><?php e(number_format($invoice['vat_amount'], 2)); ?> ريال</td>
                        </tr>
                        <tr class="table-primary">
                            <td colspan="2" class="text-end fs-5">الإجمالي المستحق</td>
                            <td class="text-end fs-5"><?php e(number_format($invoice['total_amount'], 2)); ?> ريال</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4">
                <strong>ملاحظات:</strong>
                <p class="text-muted">الرجاء سداد المبلغ المستحق قبل تاريخ الاستحقاق لتجنب أي رسوم تأخير. شكراً لتعاونكم.</p>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
