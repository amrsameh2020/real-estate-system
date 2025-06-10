<?php
// File: app/views/finance/reports.php

/**
 * ===================================================================
 * صفحة التقارير المالية (Financial Reports)
 * ===================================================================
 * تتيح هذه الصفحة للمحاسب ومدير النظام توليد مجموعة متنوعة من التقارير
 * المالية بناءً على فترة زمنية محددة، مثل تقرير الأرباح والخسائر،
 * وتقرير أعمار الديون.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
if (!Auth::hasRole('SystemAdmin') && !Auth::hasRole('Accountant')) {
    Auth::requireRole('SystemAdmin');
}

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'التقارير المالية';

// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() {
?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">توليد تقرير مالي</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">اختر نوع التقرير والفترة الزمنية المطلوبة لعرض البيانات.</p>
            
            <!-- ملاحظة: سيتم معالجة هذا النموذج في ملف handle_generate_report.php لاحقاً -->
            <form action="?url=scripts/finance/handle_generate_report" method="POST" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="report_type" class="form-label">نوع التقرير</label>
                    <select class="form-select" id="report_type" name="report_type" required>
                        <option value="" selected disabled>-- اختر نوع التقرير --</option>
                        <option value="profit_loss">تقرير الأرباح والخسائر</option>
                        <option value="aging_report">تقرير أعمار الديون</option>
                        <option value="rent_roll">تقرير قائمة الإيجارات (Rent Roll)</option>
                        <option value="vat_report">تقرير ضريبة القيمة المضافة</option>
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
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-diagram-3-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- منطقة عرض التقرير -->
    <div id="report-display-area">
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted p-5">
                <i class="bi bi-search fs-1"></i>
                <h5 class="mt-3">الرجاء اختيار نوع التقرير والفترة الزمنية لعرض البيانات.</h5>
            </div>
        </div>
    </div>

    <script>
        // في تطبيق حقيقي، سيتم استخدام AJAX هنا
        // عند إرسال النموذج، بدلاً من إعادة تحميل الصفحة،
        // يتم إرسال طلب AJAX إلى ملف المعالجة
        // ثم يتم عرض النتائج في <div id="report-display-area">
    </script>
<?php
};

// الخطوة 5: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
