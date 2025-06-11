<?php
// File: app/views/owner/properties/view.php

/**
 * ===================================================================
 * صفحة عرض تفاصيل العقار للمالك (Owner's View Property Details Page)
 * ===================================================================
 * تتيح هذه الصفحة لمالك العقار عرض تفاصيل عقار معين يملكه،
 * بالإضافة إلى الوحدات التابعة له، عقود الإيجار، والمصروفات/الإيرادات المتعلقة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مالك عقار أو مسؤول نظام)
Auth::requireRole(['Owner', 'SystemAdmin']);

// الخطوة 3: جلب معرف العقار من الرابط
$property_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$property_id) {
    Session::set('error_message', 'معرف العقار غير صالح.');
    redirect('dashboard'); // أو صفحة قائمة العقارات الخاصة بالمالك
}

// الخطوة 4: جلب بيانات العقار من قاعدة البيانات
$propertyModel = new Property();
$property_data = $propertyModel->getPropertyById($property_id);

if (!$property_data) {
    Session::set('error_message', 'العقار غير موجود.');
    redirect('dashboard'); // أو صفحة قائمة العقارات الخاصة بالمالك
}

// الخطوة 5: التحقق من أن المالك الحالي يمتلك هذا العقار (إذا لم يكن مسؤول نظام)
if (Auth::getUserRole() === 'Owner' && $property_data['owner_id'] !== Auth::getUserId()) {
    Session::set('error_message', 'لا تملك صلاحية عرض هذا العقار.');
    redirect('dashboard'); // إعادة التوجيه إلى لوحة التحكم الخاصة بالمالك
}

// جلب الوحدات المرتبطة بهذا العقار
$unitModel = new Unit();
$units = $unitModel->getUnitsByPropertyId($property_id); // افترض وجود هذه الدالة

// جلب عقود الإيجار المرتبطة بالوحدات في هذا العقار
$leaseModel = new Lease();
$leases = $leaseModel->getLeasesByPropertyId($property_id); // افترض وجود هذه الدالة

// جلب المصروفات المرتبطة بهذا العقار
$expenseModel = new Expense();
$expenses = $expenseModel->getExpensesByPropertyId($property_id); // افترض وجود هذه الدالة

// جلب الدفعات (الإيرادات) المرتبطة بهذا العقار (من خلال الفواتير والوحدات)
$paymentModel = new Payment();
$payments = $paymentModel->getPaymentsByPropertyId($property_id); // افترض وجود هذه الدالة

// الخطوة 6: تحديد عنوان الصفحة
$page_title = 'عرض العقار: ' . htmlspecialchars($property_data['name']);

// الخطوة 7: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($property_data, $units, $leases, $expenses, $payments) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">تفاصيل العقار: <?php echo htmlspecialchars($property_data['name']); ?></h1>
            <div>
                <?php if (Auth::getUserRole() === 'SystemAdmin'): // خيار التعديل للمسؤول فقط في هذا السياق، أو يمكن للمالك أن يمتلكه أيضا ?>
                    <a href="?url=properties/edit&id=<?php echo htmlspecialchars($property_data['id']); ?>" class="btn btn-primary mr-2">تعديل العقار</a>
                <?php endif; ?>
                <a href="?url=owner/dashboard" class="btn btn-secondary">العودة إلى لوحة التحكم</a>
            </div>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">معلومات العقار</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>الاسم:</strong> <?php echo htmlspecialchars($property_data['name']); ?></p>
                        <p><strong>العنوان:</strong> <?php echo htmlspecialchars($property_data['address']); ?></p>
                        <p><strong>النوع:</strong> <?php echo htmlspecialchars($property_data['type']); ?></p>
                        <p><strong>عدد الوحدات:</strong> <?php echo htmlspecialchars($property_data['number_of_units']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>تاريخ الإنشاء:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($property_data['created_at']))); ?></p>
                        <p><strong>تاريخ آخر تحديث:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($property_data['updated_at']))); ?></p>
                        <p><strong>ملاحظات:</strong> <?php echo nl2br(htmlspecialchars($property_data['notes'] ?: 'لا توجد ملاحظات')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">الوحدات</h6>
                <?php if (Auth::getUserRole() === 'SystemAdmin' || Auth::getUserRole() === 'Owner'): // يمكن للمالك إضافة وحدات أيضاً ?>
                    <a href="?url=units/create&property_id=<?php echo htmlspecialchars($property_data['id']); ?>" class="btn btn-sm btn-success">إضافة وحدة جديدة</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($units)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="unitsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>رقم الوحدة</th>
                                <th>النوع</th>
                                <th>الإيجار الشهري</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($units as $unit): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($unit['unit_number']); ?></td>
                                    <td><?php echo htmlspecialchars($unit['type']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($unit['rent_amount'], 2)); ?> ج.م</td>
                                    <td>
                                        <span class="badge badge-<?php echo ($unit['status'] == 'Occupied' ? 'danger' : ($unit['status'] == 'Vacant' ? 'success' : 'warning')); ?>">
                                            <?php echo htmlspecialchars($unit['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?url=units/view&id=<?php echo htmlspecialchars($unit['id']); ?>" class="btn btn-info btn-sm">عرض</a>
                                        <?php if (Auth::getUserRole() === 'SystemAdmin' || Auth::getUserRole() === 'Owner'): ?>
                                            <a href="?url=units/edit&id=<?php echo htmlspecialchars($unit['id']); ?>" class="btn btn-primary btn-sm">تعديل</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p>لا توجد وحدات مسجلة لهذا العقار بعد.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">عقود الإيجار</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($leases)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="leasesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>المستأجر</th>
                                <th>الوحدة</th>
                                <th>تاريخ البدء</th>
                                <th>تاريخ الانتهاء</th>
                                <th>الإيجار الشهري</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leases as $lease): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($lease['tenant_name']); ?></td>
                                    <td><?php echo htmlspecialchars($lease['unit_number']); ?></td>
                                    <td><?php echo htmlspecialchars($lease['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($lease['end_date']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($lease['monthly_rent'], 2)); ?> ج.م</td>
                                    <td>
                                        <span class="badge badge-<?php
                                            if ($lease['status'] == 'Active') echo 'success';
                                            else if ($lease['status'] == 'Expired') echo 'danger';
                                            else if ($lease['status'] == 'Terminated') echo 'warning';
                                            else echo 'secondary';
                                        ?>">
                                            <?php echo htmlspecialchars($lease['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?url=leases/view&id=<?php echo htmlspecialchars($lease['id']); ?>" class="btn btn-info btn-sm">عرض</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p>لا توجد عقود إيجار نشطة أو سابقة مرتبطة بهذا العقار.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">الملخص المالي لهذا العقار</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>المصروفات الأخيرة</h5>
                        <?php if (!empty($expenses)): ?>
                            <ul class="list-group">
                                <?php foreach ($expenses as $expense): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($expense['description']); ?> (<?php echo htmlspecialchars($expense['category']); ?>)
                                        <span class="badge badge-danger badge-pill">- <?php echo htmlspecialchars(number_format($expense['amount'], 2)); ?> ج.م</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>لا توجد مصروفات مسجلة لهذا العقار بعد.</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h5>الإيرادات الأخيرة (المدفوعات)</h5>
                        <?php if (!empty($payments)): ?>
                            <ul class="list-group">
                                <?php foreach ($payments as $payment): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        دفعة لفاتورة #<?php echo htmlspecialchars($payment['invoice_id']); ?> (<?php echo htmlspecialchars(date('Y-m-d', strtotime($payment['payment_date']))); ?>)
                                        <span class="badge badge-success badge-pill">+ <?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?> ج.م</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>لا توجد إيرادات مسجلة لهذا العقار بعد.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
                <p class="text-right">
                    <a href="?url=finance/reports" class="btn btn-info btn-sm">عرض تقارير مالية مفصلة</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تفعيل وظيفة DataTables للجدول إذا كانت مكتبة DataTables مدمجة
            if (typeof $().DataTable === 'function') {
                $('#unitsTable').DataTable({
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" }
                });
                $('#leasesTable').DataTable({
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" }
                });
            }
        });
    </script>
<?php
};

// الخطوة 8: تضمين القالب العام للمالك أو المسؤول
$current_user_role = Auth::getUserRole();
if ($current_user_role === 'Owner') {
    require_once APP_ROOT . '/app/views/layouts/owner_layout.php';
} else { // SystemAdmin
    require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
}
?>