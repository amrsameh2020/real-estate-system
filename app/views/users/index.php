<?php
// File: app/views/users/index.php

/**
 * ===================================================================
 * صفحة إدارة المستخدمين (Users Management Page)
 * ===================================================================
 * تعرض هذه الصفحة قائمة بالمستخدمين في النظام، مع خيارات لعرض التفاصيل،
 * التعديل، والحذف (للمسؤولين فقط).
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مسؤول نظام)
Auth::requireRole('SystemAdmin');

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'إدارة المستخدمين';

// الخطوة 4: جلب قائمة المستخدمين من قاعدة البيانات
$userModel = new User();
$users = $userModel->getAllUsers(); // افترض أن لديك دالة لجلب جميع المستخدمين في موديل User

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($users) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">قائمة المستخدمين</h1>
            <a href="?url=users/create" class="btn btn-success">إضافة مستخدم جديد</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">المستخدمون</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الدور</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($user['created_at']))); ?></td>
                                        <td>
                                            <a href="?url=users/view&id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-info btn-sm">عرض</a>
                                            <a href="?url=users/edit&id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-primary btn-sm">تعديل</a>
                                            <button type="button" class="btn btn-danger btn-sm delete-user-btn" data-id="<?php echo htmlspecialchars($user['id']); ?>" data-name="<?php echo htmlspecialchars($user['full_name']); ?>">حذف</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">لا يوجد مستخدمون حالياً.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">تأكيد الحذف</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    هل أنت متأكد أنك تريد حذف المستخدم "<strong id="userNameToDelete"></strong>"؟
                    <form id="deleteUserForm" action="app/scripts/users/handle_delete.php" method="POST" class="d-inline">
                        <?php Security::generateCSRF(); // توليد توكن CSRF ?>
                        <input type="hidden" name="user_id" id="userIdToDelete">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" form="deleteUserForm" class="btn btn-danger">حذف</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تفعيل وظيفة DataTables للجدول إذا كانت مكتبة DataTables مدمجة
            // إذا لم تكن مدمجة، سيعمل الجدول كجدول HTML عادي
            if (typeof $().DataTable === 'function') {
                $('#usersTable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json"
                    }
                });
            }

            // التعامل مع زر الحذف لفتح المودال
            const deleteButtons = document.querySelectorAll('.delete-user-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.dataset.id;
                    const userName = this.dataset.name;
                    document.getElementById('userIdToDelete').value = userId;
                    document.getElementById('userNameToDelete').textContent = userName;
                    $('#deleteUserModal').modal('show');
                });
            });
        });
    </script>
<?php
};

// الخطوة 6: تضمين القالب العام للمسؤول
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>