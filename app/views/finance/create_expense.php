<?php
// File: app/views/finance/create_expense.php

/**
 * ===================================================================
 * صفحة إنشاء مصروف جديد (Create New Expense Page)
 * ===================================================================
 * تتيح هذه الصفحة للمحاسبين (أو المسؤولين) تسجيل مصروفات جديدة في النظام.
 * تتضمن نموذجًا لإدخال تفاصيل المصروف.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من صلاحيات المستخدم (يجب أن يكون محاسب أو مسؤول نظام)
Auth::requireRole(['Accountant', 'SystemAdmin']);

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'تسجيل مصروف جديد';

// جلب أي بيانات نموذج سابقة في حالة إعادة التوجيه بعد فشل التحقق
$form_data = Session::get('form_data', []);
Session::remove('form_data'); // إزالة البيانات بعد جلبها

// (اختياري) جلب قائمة بالعقارات أو الوحدات إذا كان المصروف مرتبطًا بها
$propertyModel = new Property();
$properties = $propertyModel->getAllProperties(); // افترض وجود هذه الدالة

// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() use ($form_data, $properties) {
?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">تسجيل مصروف جديد</h1>
            <a href="?url=finance/expenses" class="btn btn-secondary">العودة إلى المصروفات</a>
        </div>

        <?php include APP_ROOT . '/app/views/partials/_alerts.php'; // تضمين تنبيهات الرسائل ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">تفاصيل المصروف</h6>
            </div>
            <div class="card-body">
                <form action="app/scripts/finance/handle_create_expense.php" method="POST">
                    <?php Security::generateCSRF(); // توليد توكن CSRF ?>

                    <div class="form-group">
                        <label for="description">الوصف:</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="amount">المبلغ:</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($form_data['amount'] ?? ''); ?>" required min="0.01">
                    </div>

                    <div class="form-group">
                        <label for="expense_date">تاريخ المصروف:</label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo htmlspecialchars($form_data['expense_date'] ?? date('Y-m-d')); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category">الفئة:</label>
                        <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($form_data['category'] ?? ''); ?>" placeholder="مثال: صيانة، فواتير، رواتب" required>
                    </div>

                    <div class="form-group">
                        <label for="property_id">العقار المرتبط (اختياري):</label>
                        <select class="form-control" id="property_id" name="property_id">
                            <option value="">-- لا يوجد عقار محدد --</option>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?php echo htmlspecialchars($property['id']); ?>" <?php echo (isset($form_data['property_id']) && $form_data['property_id'] == $property['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($property['name']); ?> (<?php echo htmlspecialchars($property['address']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">تسجيل المصروف</button>
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