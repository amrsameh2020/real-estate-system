<?php
// File: app/views/dashboard/accountant_dashboard.php

/**
 * ===================================================================
 * لوحة تحكم المحاسب (Accountant Dashboard)
 * ===================================================================
 * هذه هي الصفحة الرئيسية التي يراها المحاسب بعد تسجيل الدخول.
 * تعرض ملخصاً مالياً فورياً وأدوات للوصول السريع للمهام المحاسبية.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
// يجب أن يكون المستخدم محاسباً أو مدير نظام للوصول لهذه الصفحة
if (!Auth::hasRole('Accountant') && !Auth::hasRole('SystemAdmin')) {
    Session::flash('error', 'غير مصرح لك بالوصول لهذه الصفحة.');
    redirect('public/index.php?url=dashboard');
}

// الخطوة 3: جلب البيانات المالية لعرضها في لوحة التحكم
// في تطبيق حقيقي، سيتم جلب هذه البيانات من الـ Models المختلفة
// $total_income = Invoice::getTotalIncomeThisMonth();
// $total_expenses = Expense::getTotalExpensesThisMonth();
// $overdue_invoices = Invoice::getOverdueInvoices();

// بيانات وهمية للعرض حالياً
$financial_stats = [
    'total_income' => '150,000',
    'total_expenses' => '45,000',
    'net_profit' => '105,000',
    'overdue_amount' => '12,500',
];

$overdue_invoices = [
    ['tenant_name' => 'خالد يوسف', 'unit_number' => 'B-204', 'amount' => '5,000', 'due_date' => '2025-05-15'],
    ['tenant_name' => 'سارة محمود', 'unit_number' => 'C-301', 'amount' => '7,500', 'due_date' => '2025-05-20'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'لوحة التحكم المالية';

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($financial_stats, $overdue_invoices) {
?>
    <!-- صف بطاقات الإحصائيات المالية -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-1 text-success text-uppercase">الدخل (هذا الشهر)</h6>
                            <h2 class="card-title mb-0 fw-bold"><?php e($financial_stats['total_income']); ?> <small class="fs-6 text-muted">ريال</small></h2>
                        </div>
                        <div class="text-success fs-2">
                            <i class="bi bi-arrow-up-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-1 text-danger text-uppercase">المصروفات (هذا الشهر)</h6>
                            <h2 class="card-title mb-0 fw-bold"><?php e($financial_stats['total_expenses']); ?> <small class="fs-6 text-muted">ريال</small></h2>
                        </div>
                        <div class="text-danger fs-2">
                            <i class="bi bi-arrow-down-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body">
                     <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-1 text-primary text-uppercase">صافي الربح</h6>
                            <h2 class="card-title mb-0 fw-bold"><?php e($financial_stats['net_profit']); ?> <small class="fs-6 text-muted">ريال</small></h2>
                        </div>
                        <div class="text-primary fs-2">
                            <i class="bi bi-pie-chart-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-1 text-warning text-uppercase">الإيجارات المتأخرة</h6>
                            <h2 class="card-title mb-0 fw-bold"><?php e($financial_stats['overdue_amount']); ?> <small class="fs-6 text-muted">ريال</small></h2>
                        </div>
                         <div class="text-warning fs-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- صف الجداول والوصول السريع -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">الفواتير المتأخرة</h5>
                    <a href="?url=invoices" class="btn btn-sm btn-outline-primary">عرض كل الفواتير</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>المستأجر</th>
                                    <th>الوحدة</th>
                                    <th>المبلغ المستحق</th>
                                    <th>تاريخ الاستحقاق</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($overdue_invoices)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-4">لا توجد فواتير متأخرة حالياً. عمل رائع!</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($overdue_invoices as $invoice): ?>
                                    <tr>
                                        <td><?php e($invoice['tenant_name']); ?></td>
                                        <td><?php e($invoice['unit_number']); ?></td>
                                        <td class="fw-bold"><?php e($invoice['amount']); ?> ريال</td>
                                        <td class="text-danger"><?php e($invoice['due_date']); ?></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">عرض الفاتورة</a>
                                            <a href="#" class="btn btn-sm btn-success">تسجيل دفعة</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';

