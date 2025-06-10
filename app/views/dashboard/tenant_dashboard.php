<?php
// File: app/views/dashboard/tenant_dashboard.php

/**
 * ===================================================================
 * لوحة تحكم المستأجر (Tenant Dashboard)
 * ===================================================================
 * هذه هي الصفحة الرئيسية التي يراها المستأجر بعد تسجيل الدخول.
 * تعرض ملخصاً لحالة إيجاره، الفواتير المستحقة، وطلبات الصيانة الخاصة به.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
// يجب أن يكون المستخدم مستأجراً أو مدير نظام للوصول لهذه الصفحة
if (!Auth::hasRole('Tenant') && !Auth::hasRole('SystemAdmin')) {
    Session::flash('error', 'غير مصرح لك بالوصول لهذه الصفحة.');
    redirect('public/index.php?url=dashboard');
}

// الخطوة 3: جلب بيانات المستأجر
$current_user = Auth::user();
// في تطبيق حقيقي، سيتم جلب هذه البيانات من الـ Models المختلفة
// $active_lease = Lease::findActiveByTenantId($current_user['id']);
// $outstanding_invoice = Invoice::getOutstandingByTenantId($current_user['id']);
// $last_maintenance = MaintenanceRequest::findLastByTenantId($current_user['id']);

// بيانات وهمية للعرض حالياً
$lease_info = [
    'property_name' => 'برج النخيل',
    'unit_number' => 'A-101',
    'rent_amount' => '25,000',
    'next_due_date' => '2025-07-01',
];

$outstanding_invoice = [
    'id' => 512,
    'total_amount' => '2,083.33',
    'due_date' => '2025-06-01',
    'status' => 'Overdue',
];

$maintenance_request = [
    'id' => 87,
    'title' => 'تسريب في صنبور المطبخ',
    'status' => 'InProgress',
];


// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'بوابة المستأجر';

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($lease_info, $outstanding_invoice, $maintenance_request) {
?>
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <div>
            أهلاً بك في بوابتك الخاصة! من هنا يمكنك إدارة كل ما يتعلق بوحدتك السكنية بسهولة.
        </div>
    </div>
    
    <!-- صف البطاقات الرئيسية -->
    <div class="row g-4 mb-4">
        <!-- بطاقة تفاصيل الإيجار -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-file-earmark-text-fill me-2"></i>عقد الإيجار الحالي</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">العقار</dt>
                        <dd class="col-sm-8"><?php e($lease_info['property_name']); ?></dd>

                        <dt class="col-sm-4 text-muted">رقم الوحدة</dt>
                        <dd class="col-sm-8"><?php e($lease_info['unit_number']); ?></dd>

                        <dt class="col-sm-4 text-muted">قيمة الإيجار السنوي</dt>
                        <dd class="col-sm-8 fw-bold"><?php e($lease_info['rent_amount']); ?> ريال</dd>
                        
                        <dt class="col-sm-4 text-muted">تاريخ الدفعة القادمة</dt>
                        <dd class="col-sm-8 text-success fw-bold"><?php e($lease_info['next_due_date']); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- بطاقة الفاتورة المستحقة -->
        <div class="col-md-6">
             <div class="card shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-receipt-cutoff me-2"></i>الفاتورة المستحقة</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($outstanding_invoice): ?>
                        <p class="text-muted mb-1">لديك فاتورة مستحقة الدفع</p>
                        <h2 class="display-5 fw-bold"><?php e($outstanding_invoice['total_amount']); ?> <small class="h4">ريال</small></h2>
                        <p class="text-danger mb-3"><small>تاريخ الاستحقاق: <?php e($outstanding_invoice['due_date']); ?></small></p>
                        <a href="?url=tenant/payments" class="btn btn-lg btn-success">
                            <i class="bi bi-credit-card-fill me-2"></i> ادفع الآن
                        </a>
                    <?php else: ?>
                        <div class="d-flex flex-column justify-content-center h-100">
                             <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                             <p class="mt-2 mb-0 fw-bold">لا توجد فواتير مستحقة.</p>
                             <p class="text-muted">سجلاتك المالية محدثة!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- صف طلبات الصيانة والوصول السريع -->
    <div class="row g-4">
        <div class="col-lg-12">
             <div class="card shadow-sm">
                 <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">طلبات الصيانة</h5>
                    <a href="?url=tenant/maintenance/create" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle-fill me-1"></i> طلب صيانة جديد
                    </a>
                 </div>
                 <div class="card-body">
                    <?php if ($maintenance_request): ?>
                        <p><strong>آخر طلب:</strong> "<?php e($maintenance_request['title']); ?>"</p>
                        <p><strong>الحالة الحالية:</strong> <span class="badge bg-warning"><?php e($maintenance_request['status']); ?></span></p>
                        <a href="?url=tenant/maintenance" class="btn btn-outline-secondary btn-sm">عرض كل طلباتي</a>
                    <?php else: ?>
                        <p class="text-muted">لا توجد طلبات صيانة حالية.</p>
                    <?php endif; ?>
                 </div>
             </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي لبوابة المستأجر
require_once APP_ROOT . '/app/views/layouts/tenant_layout.php';
