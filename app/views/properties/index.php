<?php
// File: app/views/properties/index.php

/**
 * ===================================================================
 * صفحة عرض قائمة العقارات (Properties Index)
 * ===================================================================
 * هذه الصفحة تعرض جدولاً بجميع العقارات الموجودة في النظام،
 * مع توفير أزرار للوصول السريع لإنشاء عقار جديد أو تعديل/عرض/حذف عقار موجود.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
// فقط مدير النظام يمكنه الوصول لهذه الصفحة
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب البيانات لعرضها
// في تطبيق حقيقي، سيتم جلب هذه البيانات من موديل العقارات
// $properties = Property::getAll();
// للتوضيح، سنستخدم بيانات وهمية حالياً
$properties = [
    ['id' => 1, 'name' => 'برج النخيل', 'property_type' => 'مبنى سكني', 'city' => 'الرياض', 'units_count' => 50, 'status' => 'Active'],
    ['id' => 2, 'name' => 'مجمع الياسمين', 'property_type' => 'مجمع فلل', 'city' => 'جدة', 'units_count' => 20, 'status' => 'Active'],
    ['id' => 3, 'name' => 'مركز الأعمال', 'property_type' => 'مبنى تجاري', 'city' => 'الدمام', 'units_count' => 80, 'status' => 'Archived'],
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'إدارة العقارات';

// الخطوة 5: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($properties) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">قائمة العقارات</h5>
            <a href="?url=properties/create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> إضافة عقار جديد
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">اسم العقار</th>
                            <th scope="col">النوع</th>
                            <th scope="col">المدينة</th>
                            <th scope="col">عدد الوحدات</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($properties)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted p-4">لم يتم إضافة أي عقارات بعد.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($properties as $property): ?>
                                <tr>
                                    <th scope="row"><?php e($property['id']); ?></th>
                                    <td><strong><?php e($property['name']); ?></strong></td>
                                    <td><?php e($property['property_type']); ?></td>
                                    <td><?php e($property['city']); ?></td>
                                    <td><?php e($property['units_count']); ?></td>
                                    <td>
                                        <?php if ($property['status'] === 'Active'): ?>
                                            <span class="badge bg-success">نشط</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">مؤرشف</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="?url=properties/view/<?php e($property['id']); ?>" class="btn btn-sm btn-outline-info" title="عرض">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="?url=properties/edit/<?php e($property['id']); ?>" class="btn btn-sm btn-outline-secondary" title="تعديل">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php e($property['id']); ?>)" title="حذف">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <!-- يمكن إضافة نظام تصفح (Pagination) هنا -->
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">السابق</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">التالي</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <script>
    // دالة لتأكيد الحذف باستخدام SweetAlert2
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "لن تتمكن من التراجع عن هذا الإجراء!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، قم بالحذف!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // في تطبيق حقيقي، سيتم إرسال طلب إلى ملف المعالجة
                // window.location.href = '?url=scripts/properties/handle_delete.php&id=' + id;
                Swal.fire(
                    'تم الحذف!',
                    'تم حذف سجل العقار بنجاح.',
                    'success'
                )
            }
        })
    }
    </script>
<?php
};

// الخطوة 6: تضمين وعرض القالب الرئيسي للوحة التحكم
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
