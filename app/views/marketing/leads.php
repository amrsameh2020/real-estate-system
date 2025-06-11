<?php
// File: app/views/marketing/leads.php

/**
 * ===================================================================
 * صفحة إدارة العملاء المحتملين (Leads)
 * ===================================================================
 * تعرض هذه الصفحة قائمة بجميع العملاء المحتملين المهتمين بالاستئجار،
 * مع حالتهم في قمع المبيعات.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات
// $leads = Lead::getAll();
$leads = [
    ['id' => 1, 'full_name' => 'سامي خالد', 'email' => 'sami@example.com', 'phone_number' => '0509876543', 'source' => 'Website', 'status' => 'Contacted'],
    ['id' => 2, 'full_name' => 'نورة عبدالله', 'email' => 'noura@example.com', 'phone_number' => '0551122334', 'source' => 'PropertyFinder', 'status' => 'New'],
    ['id' => 3, 'full_name' => 'ياسر محمد', 'email' => 'yasser@example.com', 'phone_number' => '0538765432', 'source' => 'Walk-in', 'status' => 'ViewingScheduled'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة العملاء المحتملين';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($leads) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">قائمة العملاء المحتملين</h5>
             <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                <i class="bi bi-person-plus-fill me-1"></i> إضافة عميل محتمل
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الاسم الكامل</th>
                            <th>البريد/الهاتف</th>
                            <th>المصدر</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td><?php e($lead['id']); ?></td>
                                <td><strong><?php e($lead['full_name']); ?></strong></td>
                                <td>
                                    <small class="d-block"><?php e($lead['email']); ?></small>
                                    <small class="text-muted"><?php e($lead['phone_number']); ?></small>
                                </td>
                                <td><?php e($lead['source']); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?php e($lead['status']); ?></span>
                                </td>
                                <td class="text-center">
                                     <button class="btn btn-sm btn-outline-secondary" title="تحديث الحالة"><i class="bi bi-pencil-fill"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Lead Modal -->
    <div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addLeadModalLabel">إضافة عميل محتمل جديد</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="?url=scripts/marketing/handle_add_lead" method="POST">
             <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                <div class="mb-3"><label for="full_name" class="form-label">الاسم الكامل</label><input type="text" class="form-control" name="full_name" required></div>
                <div class="mb-3"><label for="email" class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email"></div>
                <div class="mb-3"><label for="phone_number" class="form-label">رقم الهاتف</label><input type="tel" class="form-control" name="phone_number" required></div>
                <div class="mb-3"><label for="source" class="form-label">المصدر</label><input type="text" class="form-control" name="source"></div>
             </div>
             <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="submit" class="btn btn-primary">حفظ العميل</button>
             </div>
          </form>
        </div>
      </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>