<?php
// File: app/views/owner/approvals.php

/**
 * ===================================================================
 * صفحة الموافقات للمالك (Owner Approvals Page)
 * ===================================================================
 * تعرض هذه الصفحة لمالك العقار طلبات تحتاج إلى موافقته، مثل:
 * - طلبات صيانة تتجاوز مبلغ معين.
 * - طلبات تجديد عقود إيجار.
 * - أي إجراءات أخرى تتطلب موافقة المالك.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مالك عقار أو مسؤول نظام)
Auth::requireRole(['Owner', 'SystemAdmin']);

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'الموافقات المطلوبة';

// جلب معرف المالك الحالي
$owner_id = Auth::getUserId();

// جلب طلبات الصيانة التي تحتاج إلى موافقة المالك (كمثال)
// افترض وجود دالة في MaintenanceRequestModel لجلب الطلبات التي تحتاج إلى موافقة المالك
$maintenanceRequestModel = new MaintenanceRequest();
$pendingApprovals = $maintenanceRequestModel->getPendingOwnerApprovals($owner_id); // افترض وجود هذه الدالة

// جلب طلبات تجديد العقود التي تحتاج إلى موافقة المالك (كمثال آخر)
$leaseModel = new Lease();
$pendingLeaseRenewals = $leaseModel->getPendingOwnerRenewals($owner_id); // افترض وجود هذه الدالة

// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($pendingApprovals, $pendingLeaseRenewals) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">الموافقات المطلوبة</h1>
            <a href="?url=owner/dashboard" class="btn btn-secondary">العودة إلى لوحة التحكم</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">طلبات الصيانة المعلقة للموافقة</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingApprovals)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="maintenanceApprovalsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الوصف</th>
                                    <th>الوحدة</th>
                                    <th>المبلغ المقدر</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الطلب</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingApprovals as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['id']); ?></td>
                                        <td><?php echo htmlspecialchars($request['description']); ?></td>
                                        <td><?php echo htmlspecialchars($request['unit_number']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($request['estimated_cost'], 2)); ?> ج.م</td>
                                        <td><span class="badge badge-warning"><?php echo htmlspecialchars($request['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($request['request_date']))); ?></td>
                                        <td>
                                            <a href="?url=maintenance/view&id=<?php echo htmlspecialchars($request['id']); ?>" class="btn btn-info btn-sm">عرض التفاصيل</a>
                                            <button type="button" class="btn btn-success btn-sm approve-request-btn" data-id="<?php echo htmlspecialchars($request['id']); ?>">موافقة</button>
                                            <button type="button" class="btn btn-danger btn-sm reject-request-btn" data-id="<?php echo htmlspecialchars($request['id']); ?>">رفض</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>لا توجد طلبات صيانة معلقة للموافقة حالياً.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow mb-4 mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">طلبات تجديد العقود المعلقة للموافقة</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingLeaseRenewals)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="leaseRenewalsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستأجر</th>
                                    <th>الوحدة</th>
                                    <th>تاريخ الانتهاء الحالي</th>
                                    <th>تاريخ التجديد المقترح</th>
                                    <th>الإيجار الجديد المقترح</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingLeaseRenewals as $lease): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lease['id']); ?></td>
                                        <td><?php echo htmlspecialchars($lease['tenant_name']); ?></td>
                                        <td><?php echo htmlspecialchars($lease['unit_number']); ?></td>
                                        <td><?php echo htmlspecialchars($lease['end_date']); ?></td>
                                        <td><?php echo htmlspecialchars($lease['proposed_end_date'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($lease['proposed_rent'] ?? $lease['monthly_rent'], 2)); ?> ج.م</td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($lease['renewal_status']); ?></span></td>
                                        <td>
                                            <a href="?url=leases/view&id=<?php echo htmlspecialchars($lease['id']); ?>" class="btn btn-info btn-sm">عرض العقد</a>
                                            <button type="button" class="btn btn-success btn-sm approve-lease-renewal-btn" data-id="<?php echo htmlspecialchars($lease['id']); ?>">موافقة</button>
                                            <button type="button" class="btn btn-danger btn-sm reject-lease-renewal-btn" data-id="<?php echo htmlspecialchars($lease['id']); ?>">رفض</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>لا توجد طلبات تجديد عقود معلقة للموافقة حالياً.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal fade" id="maintenanceApprovalModal" tabindex="-1" role="dialog" aria-labelledby="maintenanceApprovalModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="maintenanceApprovalModalLabel">تأكيد الإجراء</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        هل أنت متأكد أنك تريد <strong id="actionText"></strong> طلب الصيانة رقم <strong id="requestId"></strong>؟
                        <form id="maintenanceApprovalForm" action="" method="POST" class="d-inline">
                            <?php Security::generateCSRF(); ?>
                            <input type="hidden" name="request_id" id="hiddenRequestId">
                            <input type="hidden" name="action_type" id="hiddenActionType"> </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" form="maintenanceApprovalForm" class="btn btn-primary" id="confirmActionButton">تأكيد</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="leaseRenewalModal" tabindex="-1" role="dialog" aria-labelledby="leaseRenewalModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaseRenewalModalLabel">تأكيد إجراء تجديد العقد</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        هل أنت متأكد أنك تريد <strong id="leaseActionText"></strong> طلب تجديد العقد رقم <strong id="leaseId"></strong>؟
                        <form id="leaseRenewalForm" action="" method="POST" class="d-inline">
                            <?php Security::generateCSRF(); ?>
                            <input type="hidden" name="lease_id" id="hiddenLeaseId">
                            <input type="hidden" name="action_type" id="hiddenLeaseActionType"> </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" form="leaseRenewalForm" class="btn btn-primary" id="confirmLeaseActionButton">تأكيد</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DataTables Initialization
            if (typeof $().DataTable === 'function') {
                $('#maintenanceApprovalsTable').DataTable({
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" }
                });
                $('#leaseRenewalsTable').DataTable({
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" }
                });
            }

            // Maintenance Request Approval/Rejection Logic
            document.querySelectorAll('.approve-request-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const requestId = this.dataset.id;
                    document.getElementById('requestId').textContent = requestId;
                    document.getElementById('actionText').textContent = 'الموافقة على';
                    document.getElementById('hiddenRequestId').value = requestId;
                    document.getElementById('hiddenActionType').value = 'approve';
                    document.getElementById('maintenanceApprovalForm').action = 'app/scripts/maintenance/handle_owner_approval.php'; // افترض وجود هذا السكريبت
                    document.getElementById('confirmActionButton').classList.remove('btn-danger');
                    document.getElementById('confirmActionButton').classList.add('btn-success');
                    document.getElementById('confirmActionButton').textContent = 'موافق';
                    $('#maintenanceApprovalModal').modal('show');
                });
            });

            document.querySelectorAll('.reject-request-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const requestId = this.dataset.id;
                    document.getElementById('requestId').textContent = requestId;
                    document.getElementById('actionText').textContent = 'رفض';
                    document.getElementById('hiddenRequestId').value = requestId;
                    document.getElementById('hiddenActionType').value = 'reject';
                    document.getElementById('maintenanceApprovalForm').action = 'app/scripts/maintenance/handle_owner_approval.php'; // افترض وجود هذا السكريبت
                    document.getElementById('confirmActionButton').classList.remove('btn-success');
                    document.getElementById('confirmActionButton').classList.add('btn-danger');
                    document.getElementById('confirmActionButton').textContent = 'رفض';
                    $('#maintenanceApprovalModal').modal('show');
                });
            });

            // Lease Renewal Approval/Rejection Logic
            document.querySelectorAll('.approve-lease-renewal-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const leaseId = this.dataset.id;
                    document.getElementById('leaseId').textContent = leaseId;
                    document.getElementById('leaseActionText').textContent = 'الموافقة على';
                    document.getElementById('hiddenLeaseId').value = leaseId;
                    document.getElementById('hiddenLeaseActionType').value = 'approve';
                    document.getElementById('leaseRenewalForm').action = 'app/scripts/leases/handle_owner_renewal_approval.php'; // افترض وجود هذا السكريبت
                    document.getElementById('confirmLeaseActionButton').classList.remove('btn-danger');
                    document.getElementById('confirmLeaseActionButton').classList.add('btn-success');
                    document.getElementById('confirmLeaseActionButton').textContent = 'موافق';
                    $('#leaseRenewalModal').modal('show');
                });
            });

            document.querySelectorAll('.reject-lease-renewal-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const leaseId = this.dataset.id;
                    document.getElementById('leaseId').textContent = leaseId;
                    document.getElementById('leaseActionText').textContent = 'رفض';
                    document.getElementById('hiddenLeaseId').value = leaseId;
                    document.getElementById('hiddenLeaseActionType').value = 'reject';
                    document.getElementById('leaseRenewalForm').action = 'app/scripts/leases/handle_owner_renewal_approval.php'; // افترض وجود هذا السكريبت
                    document.getElementById('confirmLeaseActionButton').classList.remove('btn-success');
                    document.getElementById('confirmLeaseActionButton').classList.add('btn-danger');
                    document.getElementById('confirmLeaseActionButton').textContent = 'رفض';
                    $('#leaseRenewalModal').modal('show');
                });
            });
        });
    </script>
<?php
};

// الخطوة 5: تضمين القالب العام للمالك
require_once APP_ROOT . '/app/views/layouts/owner_layout.php';
?>