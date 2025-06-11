<?php
// File: app/views/marketing/listings.php

/**
 * ===================================================================
 * صفحة إدارة الوحدات الشاغرة (Listings)
 * ===================================================================
 * تعرض هذه الصفحة قائمة بجميع الوحدات الشاغرة والمتاحة للتأجير،
 * مع أدوات للتحكم في نشرها على البوابات العقارية.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $listings = Unit::findVacant();
$listings = [
    ['id' => 102, 'unit_number' => 'A-102', 'property_name' => 'برج النخيل', 'market_rent' => '35000', 'status' => 'Published'],
    ['id' => 104, 'unit_number' => 'A-104', 'property_name' => 'برج النخيل', 'market_rent' => '32000', 'status' => 'Unpublished'],
    ['id' => 205, 'unit_number' => 'فيلا 25', 'property_name' => 'مجمع الياسمين', 'market_rent' => '80000', 'status' => 'Published'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة الوحدات المعروضة للتأجير';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($listings) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">قائمة الوحدات الشاغرة</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>العقار / الوحدة</th>
                            <th>الإيجار السنوي المقدر</th>
                            <th class="text-center">حالة النشر</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listings as $listing): ?>
                            <tr>
                                <td><?php e($listing['id']); ?></td>
                                <td>
                                    <strong><?php e($listing['unit_number']); ?></strong>
                                    <small class="d-block text-muted"><?php e($listing['property_name']); ?></small>
                                </td>
                                <td><?php e(number_format($listing['market_rent'])); ?> ريال</td>
                                <td class="text-center">
                                    <span class="badge <?php echo ($listing['status'] === 'Published') ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ($listing['status'] === 'Published') ? 'منشور' : 'غير منشور'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                     <button class="btn btn-sm btn-outline-primary" title="تعديل معلومات العرض"><i class="bi bi-megaphone-fill"></i></button>
                                     <button class="btn btn-sm btn-outline-secondary" title="تعديل الوحدة"><i class="bi bi-pencil-fill"></i></button>
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