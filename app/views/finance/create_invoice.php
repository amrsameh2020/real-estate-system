<?php
// File: app/views/finance/create_invoice.php

/**
 * ===================================================================
 * صفحة إنشاء فاتورة جديدة (Create New Invoice Page)
 * ===================================================================
 * تتيح هذه الصفحة للمحاسبين (أو المسؤولين) إنشاء فواتير جديدة للمستأجرين.
 * تتضمن نموذجًا لإدخال تفاصيل الفاتورة، بما في ذلك المستأجر والوحدة المرتبطة.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون محاسب أو مسؤول نظام)
Auth::requireRole(['Accountant', 'SystemAdmin']);

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'إنشاء فاتورة جديدة';

// جلب أي بيانات نموذج سابقة في حالة إعادة التوجيه بعد فشل التحقق
$form_data = Session::get('form_data', []);
Session::remove('form_data'); // إزالة البيانات بعد جلبها

// جلب قائمة بالمستأجرين والوحدات لعرضها في القائمة المنسدلة
$userModel = new User();
$tenantUsers = $userModel->getUsersByRole('Tenant'); // افترض وجود هذه الدالة

$unitModel = new Unit();
$availableUnits = $unitModel->getAllUnits(); // يمكنك تعديل هذه الدالة لجلب الوحدات المتاحة فقط أو الوحدات ذات الصلة


// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($form_data, $tenantUsers, $availableUnits) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">إنشاء فاتورة جديدة</h1>
            <a href="?url=finance/invoices" class="btn btn-secondary">العودة إلى الفواتير</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">تفاصيل الفاتورة</h6>
            </div>
            <div class="card-body">
                <form action="app/scripts/finance/handle_create_invoice.php" method="POST">
                    <?php Security::generateCSRF(); // توليد توكن CSRF ?>

                    <div class="form-group">
                        <label for="tenant_id">المستأجر:</label>
                        <select class="form-control" id="tenant_id" name="tenant_id" required>
                            <option value="">-- اختر مستأجراً --</option>
                            <?php foreach ($tenantUsers as $tenant): ?>
                                <option value="<?php echo htmlspecialchars($tenant['id']); ?>" <?php echo (isset($form_data['tenant_id']) && $form_data['tenant_id'] == $tenant['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tenant['full_name']); ?> (<?php echo htmlspecialchars($tenant['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="unit_id">الوحدة المرتبطة (اختياري، إذا كانت الفاتورة خاصة بوحدة محددة):</label>
                        <select class="form-control" id="unit_id" name="unit_id">
                            <option value="">-- لا توجد وحدة محددة --</option>
                            <?php foreach ($availableUnits as $unit): ?>
                                <option value="<?php echo htmlspecialchars($unit['id']); ?>" <?php echo (isset($form_data['unit_id']) && $form_data['unit_id'] == $unit['id']) ? 'selected' : ''; ?>>
                                    وحدة رقم <?php echo htmlspecialchars($unit['unit_number']); ?> (عقار: <?php echo htmlspecialchars($unit['property_name']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">وصف الفاتورة (مثال: إيجار شهر يونيو، رسوم صيانة):</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="amount">المبلغ الإجمالي:</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($form_data['amount'] ?? ''); ?>" required min="0.01">
                    </div>

                    <div class="form-group">
                        <label for="due_date">تاريخ الاستحقاق:</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo htmlspecialchars($form_data['due_date'] ?? date('Y-m-d', strtotime('+1 month'))); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="status">الحالة الأولية:</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="Unpaid" <?php echo (isset($form_data['status']) && $form_data['status'] == 'Unpaid') ? 'selected' : ''; ?>>غير مدفوعة</option>
                            <option value="Partial" <?php echo (isset($form_data['status']) && $form_data['status'] == 'Partial') ? 'selected' : ''; ?>>مدفوعة جزئياً</option>
                            <option value="Paid" <?php echo (isset($form_data['status']) && $form_data['status'] == 'Paid') ? 'selected' : ''; ?>>مدفوعة</option>
                            <option value="Cancelled" <?php echo (isset($form_data['status']) && $form_data['status'] == 'Cancelled') ? 'selected' : ''; ?>>ملغاة</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">إنشاء الفاتورة</button>
                </form>
            </div>
        </div>
    </div>
<?php
};

// الخطوة 5: تضمين القالب العام للمسؤول (أو المحاسب)
$current_user_role = Auth::getUserRole();
if ($current_user_role === 'Accountant') {
    require_once APP_ROOT . '/app/views/layouts/admin_layout.php'; // أو layout خاص بالمحاسب إذا كان موجود
} else { // SystemAdmin
    require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
}
?>