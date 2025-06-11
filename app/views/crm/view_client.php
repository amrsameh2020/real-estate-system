<?php
// File: app/views/crm/view_client.php

/**
 * ===================================================================
 * صفحة عرض ملف العميل (View Client Profile)
 * ===================================================================
 * تعرض هذه الصفحة رؤية شاملة (360-Degree View) لعميل معين،
 * تتضمن معلوماته الشخصية، العقود المرتبطة به، الفواتير، وطلبات الصيانة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
$client_id = $_GET['id'] ?? null;
if (!$client_id) { abort404('لم يتم تحديد معرف العميل.'); }

// $client = User::findById($client_id);
// if (!$client) { abort404('العميل المطلوب غير موجود.'); }
// $leases = Lease::findByClientId($client_id);

// بيانات وهمية للعرض
$client = ['id' => $client_id, 'full_name' => 'علي أحمد', 'email' => 'ali@example.com', 'phone_number' => '0501231234', 'role_name' => 'Tenant', 'created_at' => '2024-01-15'];
$leases = [
    ['id' => 1, 'property_name' => 'برج النخيل', 'unit_number' => 'A-101', 'status' => 'Active'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'ملف العميل: ' . $client['full_name'];

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($client, $leases) {
?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 fw-bold"><?php e($GLOBALS['page_title']); ?></h4>
        <a href="?url=crm/clients" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i> العودة لقائمة العملاء</a>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm text-center p-4">
                <img src="https://i.pravatar.cc/150?u=<?php e($client['id']); ?>" class="rounded-circle mx-auto d-block mb-3" alt="صورة العميل">
                <h5 class="mb-0"><?php e($client['full_name']); ?></h5>
                <p class="text-muted"><?php e($client['email']); ?></p>
                <span class="badge <?php echo ($client['role_name'] === 'Owner') ? 'bg-primary' : 'bg-info'; ?>">
                    <?php echo ($client['role_name'] === 'Owner') ? 'مالك' : 'مستأجر'; ?>
                </span>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                 <div class="card-header"><h5 class="mb-0">المعلومات الأساسية</h5></div>
                 <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">رقم الهاتف</dt>
                        <dd class="col-sm-9"><?php e($client['phone_number']); ?></dd>
                        <dt class="col-sm-3">تاريخ التسجيل</dt>
                        <dd class="col-sm-9"><?php e($client['created_at']); ?></dd>
                    </dl>
                 </div>
            </div>
             <div class="card shadow-sm mt-3">
                 <div class="card-header"><h5 class="mb-0">السجلات المرتبطة</h5></div>
                 <div class="card-body">
                     <h6>عقود الإيجار</h6>
                     <ul class="list-group list-group-flush">
                        <?php foreach($leases as $lease): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php e($lease['property_name']); ?></strong> / <?php e($lease['unit_number']); ?>
                                </div>
                                <a href="?url=leases/view/<?php e($lease['id']); ?>" class="btn btn-sm btn-outline-primary">عرض العقد</a>
                            </li>
                        <?php endforeach; ?>
                     </ul>
                 </div>
             </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>