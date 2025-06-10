<?php
// File: app/views/finance/expenses.php

/**
 * ===================================================================
 * صفحة إدارة المصروفات (Expenses Index)
 * ===================================================================
 * تعرض هذه الصفحة جدولاً بجميع المصروفات المسجلة في النظام،
 * مع توفير أدوات لإضافة مصروف جديد وتتبع حالة الموافقة عليه.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
if (!Auth::hasRole('SystemAdmin') && !Auth::hasRole('Accountant')) {
    Auth::requireRole('SystemAdmin');
}

// الخطوة 3: جلب البيانات لعرضها
// $expenses = Expense::getAll();
// للتوضيح، سنستخدم بيانات وهمية حالياً
$expenses = [
    ['id' => 21, 'expense_category' => 'صيانة المصاعد', 'amount' => '2500.00', 'expense_date' => '2025-05-28', 'property_name' => 'برج النخيل', 'approval_status' => 'Approved'],
    ['id' => 22, 'expense_category' => 'فواتير كهرباء', 'amount' => '1230.50', 'expense_date' => '2025-05-25', 'property_name' => 'مجمع الياسمين', 'approval_status' => 'Approved'],
    ['id' => 23, 'expense_category' => 'أعمال سباكة طارئة', 'amount' => '850.00', 'expense_date' => '2025-05-22', 'property_name' => 'برج النخيل', 'approval_status' => 'Pending'],
    ['id' => 24, 'expense_category' => 'رواتب الحراسة', 'amount' => '12000.00', 'expense_date' => '2025-04-30', 'property_name' => 'مصروفات عامة', 'approval_status' => 'Approved'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة المصروفات';

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($expenses) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0 fw-bold">قائمة المصروفات</h5>
            <a href="?url=finance/create_expense" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> تسجيل مصروف جديد
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">فئة المصروف</th>
                            <th scope="col">المبلغ</th>
                            <th scope="col">تاريخ الصرف</th>
                            <th scope="col">العقار</th>
                            <th scope="col" class="text-center">حالة الموافقة</th>
                            <th scope="col" class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($expenses)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted p-4">لم يتم تسجيل أي مصروفات بعد.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?php e($expense['id']); ?></td>
                                    <td><strong><?php e($expense['expense_category']); ?></strong></td>
                                    <td class="fw-bold"><?php e(number_format($expense['amount'], 2)); ?> ريال</td>
                                    <td><?php e($expense['expense_date']); ?></td>
                                    <td><?php e($expense['property_name']); ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $status_map = [
                                                'Approved' => ['class' => 'bg-success', 'text' => 'تمت الموافقة'],
                                                'Pending' => ['class' => 'bg-warning text-dark', 'text' => 'بانتظار الموافقة'],
                                                'Rejected' => ['class' => 'bg-danger', 'text' => 'مرفوض'],
                                            ];
                                            $status_info = $status_map[$expense['approval_status']] ?? ['class' => 'bg-secondary', 'text' => 'غير معروف'];
                                        ?>
                                        <span class="badge <?php echo $status_info['class']; ?>"><?php e($status_info['text']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="#" class="btn btn-outline-secondary" title="عرض التفاصيل والإيصال">
                                                <i class="bi bi-paperclip"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline-danger" title="حذف المصروف">
                                                 <i class="bi bi-trash-fill"></i>
                                            </a>
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
            <p class="text-muted mb-0 small">إجمالي عدد المصروفات: <?php echo count($expenses); ?></p>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
