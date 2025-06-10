<?php
// File: app/views/tenant/maintenance.php

/**
 * ===================================================================
 * صفحة طلبات صيانة المستأجر (Tenant Maintenance Requests Page)
 * ===================================================================
 * تتيح هذه الصفحة للمستأجرين عرض سجل طلبات الصيانة الخاصة بهم
 * وإنشاء طلبات صيانة جديدة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مستأجراً)
Auth::requireRole('Tenant');

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'طلبات الصيانة الخاصة بي';

// جلب معرف المستأجر الحالي
$tenant_id = Auth::getUserId();

// جلب طلبات الصيانة الخاصة بهذا المستأجر
$maintenanceRequestModel = new MaintenanceRequest();
$maintenanceRequests = $maintenanceRequestModel->getRequestsByTenantId($tenant_id); // افترض وجود هذه الدالة

// جلب الوحدات المرتبطة بالمستأجر (من خلال عقود الإيجار)
$leaseModel = new Lease();
$tenantLeases = $leaseModel->getLeasesByTenantId($tenant_id); // افترض وجود هذه الدالة

// استخراج الوحدات من العقود
$tenantUnits = [];
foreach ($tenantLeases as $lease) {
    // افترض أن LeaseModel يجلب Unit ID و Unit Number
    if (isset($lease['unit_id']) && isset($lease['unit_number'])) {
        $tenantUnits[$lease['unit_id']] = [
            'id' => $lease['unit_id'],
            'unit_number' => $lease['unit_number'],
            'property_name' => $lease['property_name'] // افترض أن يتم جلب اسم العقار أيضاً
        ];
    }
}


// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($maintenanceRequests, $tenantUnits) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">طلبات الصيانة الخاصة بي</h1>
            <a href="?url=tenant/maintenance/create" class="btn btn-success">إرسال طلب صيانة جديد</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">سجل طلبات الصيانة</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($maintenanceRequests)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="maintenanceRequestsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الوصف</th>
                                    <th>الوحدة</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الطلب</th>
                                    <th>آخر تحديث</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($maintenanceRequests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['id']); ?></td>
                                        <td><?php echo htmlspecialchars($request['description']); ?></td>
                                        <td><?php echo htmlspecialchars($request['unit_number']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php
                                                if ($request['status'] == 'Pending') echo 'warning';
                                                else if ($request['status'] == 'In Progress') echo 'info';
                                                else if ($request['status'] == 'Completed') echo 'success';
                                                else if ($request['status'] == 'Cancelled') echo 'secondary';
                                                else if ($request['status'] == 'Rejected') echo 'danger';
                                            ?>">
                                                <?php echo htmlspecialchars($request['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($request['request_date']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($request['updated_at']))); ?></td>
                                        <td>
                                            <a href="?url=maintenance/view&id=<?php echo htmlspecialchars($request['id']); ?>" class="btn btn-info btn-sm">عرض التفاصيل</a>
                                            <?php if ($request['status'] == 'Pending'): // يمكن للمستأجر إلغاء الطلب إذا كان معلقاً فقط ?>
                                                <button type="button" class="btn btn-danger btn-sm cancel-request-btn" data-id="<?php echo htmlspecialchars($request['id']); ?>">إلغاء</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>لم تقم بإرسال أي طلبات صيانة بعد.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal fade" id="cancelRequestModal" tabindex="-1" role="dialog" aria-labelledby="cancelRequestModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelRequestModalLabel">تأكيد الإلغاء</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        هل أنت متأكد أنك تريد إلغاء طلب الصيانة رقم <strong id="requestIdToCancel"></strong>؟
                        <form id="cancelRequestForm" action="app/scripts/maintenance/handle_cancel_request.php" method="POST" class="d-inline">
                            <?php Security::generateCSRF(); ?>
                            <input type="hidden" name="request_id" id="hiddenRequestIdToCancel">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" form="cancelRequestForm" class="btn btn-danger">تأكيد الإلغاء</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DataTables Initialization
            if (typeof $().DataTable === 'function') {
                $('#maintenanceRequestsTable').DataTable({
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" }
                });
            }

            // Handle "Cancel Request" button click
            document.querySelectorAll('.cancel-request-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const requestId = this.dataset.id;
                    document.getElementById('requestIdToCancel').textContent = requestId;
                    document.getElementById('hiddenRequestIdToCancel').value = requestId;
                    $('#cancelRequestModal').modal('show');
                });
            });
        });
    </script>
<?php
};

// الخطوة 5: تضمين القالب العام للمستأجر
require_once APP_ROOT . '/app/views/layouts/tenant_layout.php';
?>