<?php
// File: app/views/tenant/documents.php

/**
 * ===================================================================
 * صفحة مستندات المستأجر (Tenant Documents Page)
 * ===================================================================
 * تتيح هذه الصفحة للمستأجرين عرض المستندات المتعلقة بعقود إيجارهم
 * ووحداتهم، مثل نسخ العقود، سياسات المبنى، أو إشعارات هامة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مستأجراً)
Auth::requireRole('Tenant');

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'مستنداتي';

// جلب معرف المستأجر الحالي
$tenant_id = Auth::getUserId();

// جلب المستندات المرتبطة بهذا المستأجر (مثل عقود الإيجار)
$documentModel = new Document();
$tenantDocuments = $documentModel->getDocumentsByUserId($tenant_id); // افترض وجود هذه الدالة

// بالإضافة إلى ذلك، يمكن جلب المستندات العامة أو المستندات المرتبطة بوحدته/عقاره
// هذا يتطلب معرفة الوحدات أو العقارات المرتبطة بالمستأجر أولاً.
$leaseModel = new Lease();
$tenantLeases = $leaseModel->getLeasesByTenantId($tenant_id);

$relatedPropertyIds = [];
$relatedUnitIds = [];
foreach ($tenantLeases as $lease) {
    if (!empty($lease['property_id'])) {
        $relatedPropertyIds[] = $lease['property_id'];
    }
    if (!empty($lease['unit_id'])) {
        $relatedUnitIds[] = $lease['unit_id'];
    }
}
$relatedPropertyIds = array_unique($relatedPropertyIds);
$relatedUnitIds = array_unique($relatedUnitIds);

// جلب المستندات المرتبطة بالعقارات والوحدات
$propertyDocuments = [];
if (!empty($relatedPropertyIds)) {
    foreach ($relatedPropertyIds as $propId) {
        $docs = $documentModel->getDocumentsByPropertyId($propId); // افترض هذه الدالة
        $propertyDocuments = array_merge($propertyDocuments, $docs);
    }
}

$unitDocuments = [];
if (!empty($relatedUnitIds)) {
    foreach ($relatedUnitIds as $unitId) {
        $docs = $documentModel->getDocumentsByUnitId($unitId); // افترض هذه الدالة
        $unitDocuments = array_merge($unitDocuments, $docs);
    }
}

// دمج جميع المستندات وإزالة المكررات (إذا لزم الأمر)
$allDocuments = array_merge($tenantDocuments, $propertyDocuments, $unitDocuments);
// يمكنك استخدام معرفات فريدة لإزالة التكرارات إذا كان ذلك محتملاً
$uniqueDocuments = [];
foreach ($allDocuments as $doc) {
    $uniqueDocuments[$doc['id']] = $doc;
}
$allDocuments = array_values($uniqueDocuments); // إعادة فهرسة الصفيف


// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($allDocuments) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">مستنداتي</h1>
            <a href="?url=tenant/dashboard" class="btn btn-secondary">العودة إلى لوحة التحكم</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">المستندات المتاحة لي</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($allDocuments)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="documentsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم المستند</th>
                                    <th>النوع</th>
                                    <th>مرتبط بـ</th>
                                    <th>تاريخ الرفع</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allDocuments as $document): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($document['id']); ?></td>
                                        <td><?php echo htmlspecialchars($document['name']); ?></td>
                                        <td><?php echo htmlspecialchars($document['type']); ?></td>
                                        <td>
                                            <?php
                                            $related_to = [];
                                            if (!empty($document['user_id'])) {
                                                $related_to[] = 'حسابي';
                                            }
                                            if (!empty($document['property_id'])) {
                                                $related_to[] = 'عقار: ' . htmlspecialchars($document['property_name'] ?? 'غير معروف'); // افترض جلب اسم العقار
                                            }
                                            if (!empty($document['unit_id'])) {
                                                $related_to[] = 'وحدة: ' . htmlspecialchars($document['unit_number'] ?? 'غير معروف'); // افترض جلب رقم الوحدة
                                            }
                                            echo empty($related_to) ? 'عام' : implode(', ', $related_to);
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($document['upload_date']))); ?></td>
                                        <td>
                                            <?php if (!empty($document['file_path'])): ?>
                                                <a href="<?php echo BASE_URL . '/' . htmlspecialchars($document['file_path']); ?>" class="btn btn-primary btn-sm" target="_blank" download>
                                                    <i class="fas fa-download"></i> تحميل
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">ملف غير متاح</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>لا توجد مستندات متاحة لك حالياً.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DataTables Initialization
            if (typeof $().DataTable === 'function') {
                $('#documentsTable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json"
                    }
                });
            }
        });
    </script>
<?php
};

// الخطوة 5: تضمين القالب العام للمستأجر
require_once APP_ROOT . '/app/views/layouts/tenant_layout.php';
?>