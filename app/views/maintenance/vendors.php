<?php
// File: app/views/maintenance/vendors.php

/**
 * ===================================================================
 * صفحة إدارة الموردين وشركات الصيانة (Vendors)
 * ===================================================================
 * تعرض هذه الصفحة قائمة بجميع الموردين وشركات الصيانة المسجلين في النظام،
 * مع إمكانية إضافة مورد جديد أو تعديل بيانات مورد حالي.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $vendors = Vendor::getAll();
$vendors = [
    ['id' => 1, 'name' => 'شركة الصيانة الحديثة', 'contact_person' => 'أحمد خليل', 'phone' => '0501234567', 'specialty' => 'تكييف وسباكة'],
    ['id' => 2, 'name' => 'خدمات المصاعد المتقدمة', 'contact_person' => 'سليمان العلي', 'phone' => '0557654321', 'specialty' => 'صيانة مصاعد'],
    ['id' => 3, 'name' => 'ورشة النجارة العصرية', 'contact_person' => 'يوسف النجار', 'phone' => '0533344455', 'specialty' => 'أعمال خشبية'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة الموردين وشركات الصيانة';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($vendors) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">قائمة الموردين</h5>
            <a href="?url=maintenance/handle_add_vendor" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> إضافة مورد جديد
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>اسم الشركة/المورد</th>
                            <th>مسؤول التواصل</th>
                            <th>رقم الهاتف</th>
                            <th>التخصص</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td><?php e($vendor['id']); ?></td>
                                <td><strong><?php e($vendor['name']); ?></strong></td>
                                <td><?php e($vendor['contact_person']); ?></td>
                                <td><?php e($vendor['phone']); ?></td>
                                <td><span class="badge bg-info"><?php e($vendor['specialty']); ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary" title="تعديل"><i class="bi bi-pencil-fill"></i></button>
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