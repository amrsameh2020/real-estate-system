<?php
// File: app/views/tenant/maintenance/create.php

/**
 * ===================================================================
 * صفحة إنشاء طلب صيانة جديد للمستأجر (Create New Maintenance Request for Tenant)
 * ===================================================================
 * تتيح هذه الصفحة للمستأجرين إرسال طلبات صيانة جديدة لوحداتهم.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون مستأجراً)
Auth::requireRole('Tenant');

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'إرسال طلب صيانة جديد';

// جلب معرف المستأجر الحالي
$tenant_id = Auth::getUserId();

// جلب الوحدات المرتبطة بالمستأجر (من خلال عقود الإيجار)
$leaseModel = new Lease();
$tenantLeases = $leaseModel->getLeasesByTenantId($tenant_id); // افترض وجود هذه الدالة

// استخراج الوحدات من العقود لتعبئة القائمة المنسدلة
$tenantUnits = [];
foreach ($tenantLeases as $lease) {
    if (isset($lease['unit_id']) && isset($lease['unit_number']) && isset($lease['property_name'])) {
        $tenantUnits[$lease['unit_id']] = [
            'id' => $lease['unit_id'],
            'unit_number' => $lease['unit_number'],
            'property_name' => $lease['property_name']
        ];
    }
}

// جلب أي بيانات نموذج سابقة في حالة إعادة التوجيه بعد فشل التحقق
$form_data = Session::get('form_data', []);
Session::remove('form_data'); // إزالة البيانات بعد جلبها

// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($form_data, $tenantUnits) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">إرسال طلب صيانة جديد</h1>
            <a href="?url=tenant/maintenance" class="btn btn-secondary">العودة إلى طلبات الصيانة</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">تفاصيل طلب الصيانة</h6>
            </div>
            <div class="card-body">
                <form action="app/scripts/maintenance/handle_create.php" method="POST">
                    <?php Security::generateCSRF(); // توليد توكن CSRF ?>

                    <div class="form-group">
                        <label for="unit_id">الوحدة التي تحتاج صيانة:</label>
                        <select class="form-control" id="unit_id" name="unit_id" required>
                            <option value="">-- اختر وحدتك --</option>
                            <?php if (!empty($tenantUnits)): ?>
                                <?php foreach ($tenantUnits as $unit): ?>
                                    <option value="<?php echo htmlspecialchars($unit['id']); ?>" <?php echo (isset($form_data['unit_id']) && $form_data['unit_id'] == $unit['id']) ? 'selected' : ''; ?>>
                                        وحدة رقم <?php echo htmlspecialchars($unit['unit_number']); ?> (عقار: <?php echo htmlspecialchars($unit['property_name']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>لا توجد وحدات مرتبطة بك حالياً.</option>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($tenantUnits)): ?>
                            <small class="text-danger">لا يمكنك إرسال طلب صيانة لأنه لا توجد وحدات مرتبطة بحسابك.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="description">وصف المشكلة بالتفصيل:</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                        <small class="form-text text-muted">يرجى تقديم أكبر قدر ممكن من التفاصيل لمساعدتنا في معالجة طلبك بسرعة.</small>
                    </div>

                    <div class="form-group">
                        <label for="priority">الأولوية:</label>
                        <select class="form-control" id="priority" name="priority" required>
                            <option value="Low" <?php echo (isset($form_data['priority']) && $form_data['priority'] == 'Low') ? 'selected' : ''; ?>>منخفضة</option>
                            <option value="Medium" <?php echo (isset($form_data['priority']) && $form_data['priority'] == 'Medium') ? 'selected' : ''; ?>>متوسطة</option>
                            <option value="High" <?php echo (isset($form_data['priority']) && $form_data['priority'] == 'High') ? 'selected' : ''; ?>>عالية</option>
                            <option value="Urgent" <?php echo (isset($form_data['priority']) && $form_data['priority'] == 'Urgent') ? 'selected' : ''; ?>>عاجلة</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">رقم هاتف للتواصل (اختياري، إذا كان مختلفًا عن المسجل):</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($form_data['contact_number'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" <?php echo empty($tenantUnits) ? 'disabled' : ''; ?>>إرسال الطلب</button>
                </form>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 5: تضمين القالب العام للمستأجر
require_once APP_ROOT . '/app/views/layouts/tenant_layout.php';
?>