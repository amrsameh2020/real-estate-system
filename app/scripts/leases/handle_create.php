<?php
// File: app/scripts/leases/handle_create.php

/**
 * ===================================================================
 * ملف معالجة إنشاء عقد إيجار جديد (Create Lease Handler)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج إنشاء عقد جديد.
 * يستقبل البيانات، يقوم بالتحقق منها، ينشئ العقد والفوترة الأولية،
 * يحدث حالة الوحدة، ثم يعيد توجيه المستخدم.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=leases/create');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال بيانات النموذج
$data = [
    'unit_id'           => filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT),
    'tenant_user_id'    => filter_input(INPUT_POST, 'tenant_user_id', FILTER_VALIDATE_INT),
    'start_date'        => Security::sanitizeInput($_POST['start_date'] ?? ''),
    'end_date'          => Security::sanitizeInput($_POST['end_date'] ?? ''),
    'rent_amount'       => filter_input(INPUT_POST, 'rent_amount', FILTER_VALIDATE_FLOAT),
    'payment_frequency' => Security::sanitizeInput($_POST['payment_frequency'] ?? ''),
    'security_deposit'  => filter_input(INPUT_POST, 'security_deposit', FILTER_VALIDATE_FLOAT, ['options' => ['default' => 0]]),
    'status'            => 'Active' // تفعيل العقد مباشرة عند الإنشاء
];

// الخطوة 4: التحقق من صحة المدخلات
$errors = [];
if (empty($data['unit_id'])) {
    $errors[] = 'الرجاء اختيار وحدة صالحة.';
}
if (empty($data['tenant_user_id'])) {
    $errors[] = 'الرجاء اختيار مستأجر صالح.';
}
if (empty($data['start_date']) || !DateTime::createFromFormat('Y-m-d', $data['start_date'])) {
    $errors[] = 'تاريخ البدء غير صالح.';
}
if (empty($data['end_date']) || !DateTime::createFromFormat('Y-m-d', $data['end_date'])) {
    $errors[] = 'تاريخ الانتهاء غير صالح.';
} elseif (new DateTime($data['start_date']) >= new DateTime($data['end_date'])) {
    $errors[] = 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء.';
}
if (empty($data['rent_amount']) || $data['rent_amount'] <= 0) {
    $errors[] = 'الرجاء إدخال قيمة إيجار صالحة.';
}

// إذا وجدت أخطاء، قم بحفظها في رسالة flash وأعد التوجيه
if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=leases/create');
}

// الخطوة 5: بدء معاملة (Transaction) لضمان تنفيذ جميع العمليات معاً
$db = Database::getInstance()->pdo;
try {
    $db->beginTransaction();

    // 5.1: إنشاء العقد الأساسي
    $lease_id = Lease::create($data);
    if (!$lease_id) {
        throw new Exception("فشل إنشاء سجل العقد الأساسي.");
    }
    
    // 5.2: تحديث حالة الوحدة إلى "مؤجرة"
    Unit::update($data['unit_id'], ['status' => 'Occupied']);

    // 5.3: توليد الفواتير المستقبلية للعقد
    // (هذا منطق معقد، يمكن تبسيطه أو وضعه في كلاس خاص لاحقاً)
    $start_date = new DateTime($data['start_date']);
    $end_date = new DateTime($data['end_date']);
    $interval_map = ['Monthly' => 'P1M', 'Quarterly' => 'P3M', 'Annually' => 'P1Y'];
    $payment_count_map = ['Monthly' => 12, 'Quarterly' => 4, 'Annually' => 1];
    
    $interval = new DateInterval($interval_map[$data['payment_frequency']]);
    $invoice_amount = $data['rent_amount'] / $payment_count_map[$data['payment_frequency']];

    $current_due_date = clone $start_date;
    while ($current_due_date < $end_date) {
        Invoice::create([
            'lease_id' => $lease_id,
            'amount' => $invoice_amount,
            'due_date' => $current_due_date->format('Y-m-d')
        ]);
        $current_due_date->add($interval);
    }

    // 5.4: تأكيد جميع العمليات
    $db->commit();

    // الخطوة 6: إعادة التوجيه مع رسالة نجاح
    Session::flash('success', 'تم إنشاء العقد وتوليد الفواتير بنجاح!');
    redirect('public/index.php?url=leases/view/' . $lease_id);

} catch (Exception $e) {
    // في حالة حدوث أي خطأ، يتم التراجع عن جميع التغييرات
    $db->rollBack();
    Session::flash('error', 'حدث خطأ أثناء إنشاء العقد: ' . $e->getMessage());
    redirect('public/index.php?url=leases/create');
}

