<?php
// File: app/views/owner/reports.php

/**
 * ===================================================================
 * صفحة تقارير المالك (Owner Reports Page)
 * ===================================================================
 * تتيح هذه الصفحة للملاك عرض تقارير مالية محددة لعقاراتهم.
 * يمكنهم تحديد أنواع التقارير والفترة الزمنية.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مالك عقار أو مسؤول نظام)
Auth::requireRole(['Owner', 'SystemAdmin']);

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'تقارير المالك';

// جلب قائمة العقارات التي يملكها المستخدم الحالي
$propertyModel = new Property();
$user_id = Auth::getUserId();
$ownerProperties = $propertyModel->getPropertiesByOwnerId($user_id); // افترض وجود هذه الدالة

// جلب أي بيانات نموذج سابقة في حالة إعادة التوجيه بعد فشل التحقق
$form_data = Session::get('form_data', []);
Session::remove('form_data'); // إزالة البيانات بعد جلبها

// جلب نتائج التقرير إذا كانت موجودة في الجلسة بعد توليد تقرير
$report_results = Session::get('report_results', null);
Session::remove('report_results'); // إزالة النتائج بعد عرضها

// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($form_data, $ownerProperties, $report_results) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">تقارير المالك</h1>
            <a href="?url=owner/dashboard" class="btn btn-secondary">العودة إلى لوحة التحكم</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">توليد تقرير جديد</h6>
            </div>
            <div class="card-body">
                <form action="app/scripts/finance/handle_generate_report.php" method="POST">
                    <?php Security::generateCSRF(); // توليد توكن CSRF ?>

                    <div class="form-group">
                        <label for="report_type">نوع التقرير:</label>
                        <select class="form-control" id="report_type" name="report_type" required>
                            <option value="">-- اختر نوع التقرير --</option>
                            <option value="owner_payouts" <?php echo (isset($form_data['report_type']) && $form_data['report_type'] == 'owner_payouts') ? 'selected' : ''; ?>>كشوفات حساب المالك (الإيرادات والمصروفات)</option>
                            <option value="expenses" <?php echo (isset($form_data['report_type']) && $form_data['report_type'] == 'expenses') ? 'selected' : ''; ?>>المصروفات</option>
                            <option value="income" <?php echo (isset($form_data['report_type']) && $form_data['report_type'] == 'income') ? 'selected' : ''; ?>>الإيرادات (المدفوعات)</option>
                            <option value="tenant_invoices" <?php echo (isset($form_data['report_type']) && $form_data['report_type'] == 'tenant_invoices') ? 'selected' : ''; ?>>فواتير المستأجرين</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="property_id">العقار المرتبط (اختياري، لتقارير عقار محدد):</label>
                        <select class="form-control" id="property_id" name="property_id">
                            <option value="">-- كل العقارات --</option>
                            <?php foreach ($ownerProperties as $property): ?>
                                <option value="<?php echo htmlspecialchars($property['id']); ?>" <?php echo (isset($form_data['property_id']) && $form_data['property_id'] == $property['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($property['name']); ?> (<?php echo htmlspecialchars($property['address']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="start_date">تاريخ البدء:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($form_data['start_date'] ?? date('Y-m-01')); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="end_date">تاريخ الانتهاء:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($form_data['end_date'] ?? date('Y-m-t')); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">توليد التقرير</button>
                </form>
            </div>
        </div>

        <?php if ($report_results): ?>
            <div class="card shadow mb-4 mt-5">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">نتائج التقرير: <?php echo htmlspecialchars($report_results['title']); ?></h6>
                    <p class="mb-0 text-muted">الفترة: من <?php echo htmlspecialchars($report_results['start_date']); ?> إلى <?php echo htmlspecialchars($report_results['end_date']); ?></p>
                </div>
                <div class="card-body">
                    <?php if (!empty($report_results['data'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="reportResultsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <?php if ($report_results['type'] == 'expenses'): ?>
                                            <th>الوصف</th>
                                            <th>المبلغ</th>
                                            <th>الفئة</th>
                                            <th>تاريخ المصروف</th>
                                            <th>العقار</th>
                                        <?php elseif ($report_results['type'] == 'income'): ?>
                                            <th>المبلغ</th>
                                            <th>تاريخ الدفعة</th>
                                            <th>طريقة الدفع</th>
                                            <th>رقم الفاتورة</th>
                                            <th>المستأجر</th>
                                        <?php elseif ($report_results['type'] == 'owner_payouts'): ?>
                                            <th>الفترة</th>
                                            <th>إجمالي الإيرادات</th>
                                            <th>إجمالي المصروفات</th>
                                            <th>صافي الربح/الخسارة</th>
                                            <th>حالة الدفع للمالك</th>
                                        <?php elseif ($report_results['type'] == 'tenant_invoices'): ?>
                                            <th>رقم الفاتورة</th>
                                            <th>المستأجر</th>
                                            <th>الوصف</th>
                                            <th>المبلغ</th>
                                            <th>تاريخ الاستحقاق</th>
                                            <th>الحالة</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_results['data'] as $row): ?>
                                        <tr>
                                            <?php if ($report_results['type'] == 'expenses'): ?>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                <td><?php echo htmlspecialchars(number_format($row['amount'], 2)); ?> ج.م</td>
                                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                                <td><?php echo htmlspecialchars($row['expense_date']); ?></td>
                                                <td><?php echo htmlspecialchars($row['property_name'] ?? 'غير محدد'); ?></td>
                                            <?php elseif ($report_results['type'] == 'income'): ?>
                                                <td><?php echo htmlspecialchars(number_format($row['amount'], 2)); ?> ج.م</td>
                                                <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                                                <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                                <td><?php echo htmlspecialchars($row['invoice_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['tenant_name'] ?? 'غير محدد'); ?></td>
                                            <?php elseif ($report_results['type'] == 'owner_payouts'): ?>
                                                <td><?php echo htmlspecialchars($row['period'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars(number_format($row['total_income'] ?? 0, 2)); ?> ج.م</td>
                                                <td><?php echo htmlspecialchars(number_format($row['total_expenses'] ?? 0, 2)); ?> ج.م</td>
                                                <td><?php echo htmlspecialchars(number_format(($row['total_income'] ?? 0) - ($row['total_expenses'] ?? 0), 2)); ?> ج.م</td>
                                                <td><?php echo htmlspecialchars($row['payout_status'] ?? 'N/A'); ?></td>
                                            <?php elseif ($report_results['type'] == 'tenant_invoices'): ?>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['tenant_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                <td><?php echo htmlspecialchars(number_format($row['amount'], 2)); ?> ج.م</td>
                                                <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                                                <td><span class="badge badge-<?php echo ($row['status'] == 'Paid' ? 'success' : ($row['status'] == 'Unpaid' ? 'danger' : 'warning')); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>لا توجد بيانات متاحة لهذا التقرير بالفترة المحددة.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $().DataTable === 'function') {
                $('#reportResultsTable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json"
                    }
                });
            }
        });
    </script>
<?php
};

// الخطوة 5: تضمين القالب العام للمالك
require_once APP_ROOT . '/app/views/layouts/owner_layout.php';
?>