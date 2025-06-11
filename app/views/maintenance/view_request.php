<?php
// File: app/views/maintenance/view_request.php

/**
 * ===================================================================
 * صفحة عرض تفاصيل طلب صيانة (View Maintenance Request)
 * ===================================================================
 * تعرض هذه الصفحة جميع التفاصيل المتعلقة بطلب صيانة معين، بما في ذلك
 * سجل المحادثات والتحديثات.
 */

// الخطوة 1: تحميل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
$request_id = $_GET['id'] ?? null;
if (!$request_id) { abort404('لم يتم تحديد معرف الطلب.'); }

// $request = MaintenanceRequest::findById($request_id);
// if (!$request) { abort404('الطلب المطلوب غير موجود.'); }

$request = ['id' => $request_id, 'title' => 'تسريب في صنبور المطبخ', 'description' => 'يوجد تسريب مستمر من صنبور المياه الحارة في المطبخ، يرجى إرسال فني لإصلاحه.', 'status' => 'InProgress', 'priority' => 'High', 'property_name' => 'برج النخيل', 'unit_number' => 'A-101', 'tenant_name' => 'علي أحمد', 'technician_name' => 'محمد الفني'];

$updates = [
    ['user' => 'علي أحمد', 'action' => 'أنشأ الطلب', 'timestamp' => '2025-06-10 09:00 ص'],
    ['user' => 'مدير النظام', 'action' => 'غيّر الحالة إلى "Assigned"', 'timestamp' => '2025-06-10 09:30 ص'],
    ['user' => 'مدير النظام', 'action' => 'عيّن الطلب إلى "محمد الفني"', 'timestamp' => '2025-06-10 09:31 ص'],
    ['user' => 'محمد الفني', 'action' => 'غيّر الحالة إلى "InProgress"', 'timestamp' => '2025-06-11 10:00 ص'],
    ['user' => 'محمد الفني', 'action' => 'أضاف تعليقاً: "تم الوصول للموقع، جاري فحص المشكلة."', 'timestamp' => '2025-06-11 10:05 ص'],
];


// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'تفاصيل طلب الصيانة #' . $request['id'];

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($request, $updates) {
?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 fw-bold"><?php e($GLOBALS['page_title']); ?></h4>
        <a href="?url=maintenance/board" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i> العودة للوحة الطلبات</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0 fw-bold">وصف المشكلة</h5></div>
                <div class="card-body">
                    <h6 class="fw-bold"><?php e($request['title']); ?></h6>
                    <p class="text-muted"><?php e($request['description']); ?></p>
                </div>
            </div>
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white"><h5 class="mb-0 fw-bold">سجل التحديثات والمحادثات</h5></div>
                <div class="card-body">
                    <!-- Timeline -->
                    <ul class="list-unstyled">
                        <?php foreach (array_reverse($updates) as $update): ?>
                        <li class="d-flex mb-3">
                            <i class="bi bi-person-circle fs-3 text-muted me-3"></i>
                            <div>
                                <p class="mb-0"><strong><?php e($update['user']); ?></strong> <?php e($update['action']); ?></p>
                                <small class="text-muted"><?php e($update['timestamp']); ?></small>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <hr>
                    <!-- Add Comment Form -->
                    <form action="?url=scripts/maintenance/handle_add_comment" method="POST">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="أضف تعليقاً أو تحديثاً...">
                            <button class="btn btn-primary" type="submit">إرسال</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0 fw-bold">معلومات الطلب</h5></div>
                <div class="card-body">
                     <dl>
                        <dt class="text-muted">الحالة</dt>
                        <dd><span class="badge bg-warning text-dark fs-6"><?php e($request['status']); ?></span></dd>
                        <dt class="text-muted mt-3">الأولوية</dt>
                        <dd><span class="badge bg-danger fs-6"><?php e($request['priority']); ?></span></dd>
                        <dt class="text-muted mt-3">العقار</dt>
                        <dd><?php e($request['property_name']); ?></dd>
                        <dt class="text-muted mt-3">الوحدة</dt>
                        <dd><?php e($request['unit_number']); ?></dd>
                        <dt class="text-muted mt-3">مقدم الطلب</dt>
                        <dd><?php e($request['tenant_name']); ?></dd>
                         <dt class="text-muted mt-3">معيّن إلى</dt>
                        <dd><?php e($request['technician_name'] ?? 'لم يتم التعيين'); ?></dd>
                    </dl>
                    <hr>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success">تحديد كـ "مكتمل"</button>
                        <button class="btn btn-secondary">تعيين فني</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>
