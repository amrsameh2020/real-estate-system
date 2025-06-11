<?php
// File: app/views/settings/templates.php

/**
 * ===================================================================
 * صفحة إدارة القوالب (Templates Management)
 * ===================================================================
 * تتيح هذه الصفحة لمدير النظام تعديل محتوى القوالب المستخدمة في
 * النظام، مثل قالب عقد الإيجار أو قالب رسائل البريد الإلكتروني.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $templates = Template::getAll();
$templates = [
    ['id' => 1, 'name' => 'قالب عقد الإيجار القياسي', 'type' => 'Contract', 'last_updated' => '2025-05-10'],
    ['id' => 2, 'name' => 'قالب تذكير دفع الإيجار (Email)', 'type' => 'Email', 'last_updated' => '2025-04-20'],
    ['id' => 3, 'name' => 'قالب رسالة الترحيب بالمستأجر الجديد (SMS)', 'type' => 'SMS', 'last_updated' => '2025-03-15'],
];


// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة القوالب';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($templates) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">قوالب النظام</h5>
        </div>
        <div class="card-body p-0">
             <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>اسم القالب</th>
                            <th>النوع</th>
                            <th>آخر تحديث</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td><strong><?php e($template['name']); ?></strong></td>
                                <td><?php e($template['type']); ?></td>
                                <td><?php e($template['last_updated']); ?></td>
                                <td class="text-center">
                                     <a href="?url=settings/edit_template/<?php e($template['id']); ?>" class="btn btn-sm btn-outline-secondary" title="تعديل المحتوى"><i class="bi bi-pencil-fill"></i></a>
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