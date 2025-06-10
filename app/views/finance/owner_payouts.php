<?php
// File: app/views/finance/owner_payouts.php

/**
 * ===================================================================
 * صفحة إدارة تحويلات الملاك (Owner Payouts)
 * ===================================================================
 * تتيح هذه الصفحة للمحاسب توليد كشوف حسابات للملاك، حساب المستحقات،
 * وتسجيل التحويلات المالية التي تمت لهم.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
if (!Auth::hasRole('SystemAdmin') && !Auth::hasRole('Accountant')) {
    Auth::requireRole('SystemAdmin');
}

// الخطوة 3: جلب البيانات اللازمة
// $owners = User::findAllByRole('Owner');
// للتوضيح، سنستخدم بيانات وهمية حالياً
$owners = [
    ['id' => 3, 'full_name' => 'محمد عبدالله (مالك)'],
    ['id' => 5, 'full_name' => 'سارة إبراهيم (مالك)'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة تحويلات الملاك';

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($owners) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">توليد كشف حساب مالك</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">اختر المالك والفترة الزمنية لتوليد كشف حساب تفصيلي بالإيرادات والمصروفات وحساب المبلغ المستحق للتحويل.</p>
            
            <!-- ملاحظة: سيتم معالجة هذا النموذج في ملف handle_generate_report.php لاحقاً -->
            <form action="?url=scripts/finance/handle_generate_report" method="POST" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="owner_id" class="form-label">اختر المالك</label>
                    <select class="form-select" id="owner_id" name="owner_id" required>
                        <option value="" selected disabled>-- اختر مالكاً --</option>
                        <?php foreach ($owners as $owner): ?>
                            <option value="<?php e($owner['id']); ?>"><?php e($owner['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">توليد</button>
                </div>
            </form>
        </div>
    </div>

    <hr class="my-4">

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">سجل التحويلات الأخيرة</h5>
        </div>
        <div class="card-body p-0">
             <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>المالك</th>
                            <th>المبلغ المحول</th>
                            <th>تاريخ التحويل</th>
                            <th>الفترة</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- سيتم تعبئة هذا الجدول بالبيانات من قاعدة البيانات -->
                        <tr>
                            <td colspan="6" class="text-center text-muted p-4">لا توجد سجلات تحويل لعرضها.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
