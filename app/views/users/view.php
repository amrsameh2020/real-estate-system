<?php
// File: app/views/users/view.php

/**
 * ===================================================================
 * صفحة عرض تفاصيل المستخدم (View User Details Page)
 * ===================================================================
 * تعرض هذه الصفحة التفاصيل الكاملة لمستخدم معين في النظام.
 * - تتطلب معرف مستخدم صالح.
 * - تتحقق من صلاحيات المستخدم (SystemAdmin).
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مسؤول نظام)
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب معرف المستخدم من الرابط
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$user_id) {
    Session::set('error_message', 'معرف المستخدم غير صالح.');
    redirect('users');
}

// الخطوة 4: جلب بيانات المستخدم من قاعدة البيانات
$userModel = new User();
$user_data = $userModel->getUserById($user_id);

if (!$user_data) {
    Session::set('error_message', 'المستخدم غير موجود.');
    redirect('users');
}

// الخطوة 5: تحديد عنوان الصفحة
$page_title = 'عرض المستخدم: ' . htmlspecialchars($user_data['full_name']);

// الخطوة 6: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($user_data) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">تفاصيل المستخدم</h1>
            <div>
                <a href="?url=users/edit&id=<?php echo htmlspecialchars($user_data['id']); ?>" class="btn btn-primary mr-2">تعديل المستخدم</a>
                <a href="?url=users" class="btn btn-secondary">العودة إلى قائمة المستخدمين</a>
            </div>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">معلومات المستخدم</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>الاسم الكامل:</strong> <?php echo htmlspecialchars($user_data['full_name']); ?></p>
                        <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                        <p><strong>الدور:</strong> <?php echo htmlspecialchars($user_data['role']); ?></p>
                        <p><strong>رقم الهاتف:</strong> <?php echo htmlspecialchars($user_data['phone_number'] ?: 'غير متوفر'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>العنوان:</strong> <?php echo nl2br(htmlspecialchars($user_data['address'] ?: 'غير متوفر')); ?></p>
                        <p><strong>تاريخ الإنشاء:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($user_data['created_at']))); ?></p>
                        <p><strong>تاريخ آخر تحديث:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($user_data['updated_at']))); ?></p>
                    </div>
                </div>
                <hr>
                <?php if ($user_data['role'] == 'Owner'): ?>
                    <h5>العقارات المملوكة</h5>
                    <?php
                        $propertyModel = new Property();
                        $ownerProperties = $propertyModel->getPropertiesByOwnerId($user_data['id']); // افتراض وجود هذه الدالة
                    ?>
                    <?php if (!empty($ownerProperties)): ?>
                        <ul>
                            <?php foreach ($ownerProperties as $property): ?>
                                <li><a href="?url=properties/view&id=<?php echo htmlspecialchars($property['id']); ?>"><?php echo htmlspecialchars($property['name']); ?> (<?php echo htmlspecialchars($property['address']); ?>)</a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>لا يمتلك هذا المالك أي عقارات حالياً.</p>
                    <?php endif; ?>
                <?php elseif ($user_data['role'] == 'Tenant'): ?>
                    <h5>العقود الإيجارية</h5>
                    <?php
                        $leaseModel = new Lease();
                        $tenantLeases = $leaseModel->getLeasesByTenantId($user_data['id']); // افتراض وجود هذه الدالة
                    ?>
                    <?php if (!empty($tenantLeases)): ?>
                        <ul>
                            <?php foreach ($tenantLeases as $lease): ?>
                                <li>
                                    <a href="?url=leases/view&id=<?php echo htmlspecialchars($lease['id']); ?>">
                                        عقد للإيجار رقم <?php echo htmlspecialchars($lease['id']); ?> (وحدة: <?php echo htmlspecialchars($lease['unit_number']); ?>)
                                    </a>
                                    (من: <?php echo htmlspecialchars($lease['start_date']); ?> إلى: <?php echo htmlspecialchars($lease['end_date']); ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>لا يوجد لدى هذا المستأجر أي عقود إيجار حالياً.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 7: تضمين القالب العام للمسؤول
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>