<?php
// File: app/views/crm/clients.php

/**
 * ===================================================================
 * صفحة إدارة العملاء (Clients)
 * ===================================================================
 * تعرض هذه الصفحة قائمة بجميع العملاء في النظام (ملاك ومستأجرين).
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $clients = User::getAll(); // يمكن فلترتها لإظهار الملاك والمستأجرين فقط
$clients = [
    ['id' => 1, 'full_name' => 'علي أحمد', 'email' => 'ali@example.com', 'phone_number' => '0501231234', 'role_name' => 'Tenant'],
    ['id' => 2, 'full_name' => 'فاطمة الزهراء', 'email' => 'fatima@example.com', 'phone_number' => '0554324321', 'role_name' => 'Tenant'],
    ['id' => 3, 'full_name' => 'محمد عبدالله', 'email' => 'mohammed@example.com', 'phone_number' => '0533213210', 'role_name' => 'Owner'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة العملاء';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($clients) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">قائمة العملاء</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الاسم الكامل</th>
                            <th>البريد الإلكتروني</th>
                            <th>رقم الهاتف</th>
                            <th>الدور</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php e($client['id']); ?></td>
                                <td><strong><?php e($client['full_name']); ?></strong></td>
                                <td><?php e($client['email']); ?></td>
                                <td><?php e($client['phone_number']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($client['role_name'] === 'Owner') ? 'bg-primary' : 'bg-info'; ?>">
                                        <?php echo ($client['role_name'] === 'Owner') ? 'مالك' : 'مستأجر'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="?url=crm/view_client/<?php e($client['id']); ?>" class="btn btn-sm btn-outline-info" title="عرض الملف الشخصي"><i class="bi bi-person-lines-fill"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>
