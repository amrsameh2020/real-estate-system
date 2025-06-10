<?php
// File: app/scripts/leases/handle_renew.php

/**
 * ===================================================================
 * ملف معالجة تجديد عقد إيجار (Renew Lease Handler)
 * ===================================================================
 * هذا الملف هو الواجهة الخلفية لنموذج تجديد عقد موجود.
 * يستقبل البيانات، يقوم بإنهاء العقد القديم، ينشئ عقداً جديداً،
 * ويقوم بتوليد الفواتير الخاصة به.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية والأمان
Auth::requireRole('SystemAdmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('public/index.php?url=leases');
}

Security::validateCsrfToken($_POST['csrf_token'] ?? '');

// الخطوة 3: تنظيف واستقبال بيانات النموذج
$old_lease_id = filter_input(INPUT_POST, 'old_lease_id', FILTER_VALIDATE_INT);
$new_data = [
    'start_date'        => Security::sanitizeInput($_POST['start_date'] ?? ''),
    'end_date'          => Security::sanitizeInput($_POST['end_date'] ?? ''),
    'rent_amount'       => filter_input(INPUT_POST, 'rent_amount', FILTER_VALIDATE_FLOAT),
];

// جلب بيانات العقد القديم للحصول على معرف الوحدة والمستأجر
$old_lease = Lease::findById($old_lease_id);
if (!$old_lease) {
    Session::flash('error', 'العقد الأصلي الذي تحاول تجديده غير موجود.');
    redirect('public/index.php?url=leases');
}

// الخطوة 4: التحقق من صحة المدخلات
$errors = [];
if (empty($new_data['start_date']) || !DateTime::createFromFormat('Y-m-d', $new_data['start_date'])) {
    $errors[] = 'تاريخ البدء الجديد غير صالح.';
}
if (empty($new_data['end_date']) || !DateTime::createFromFormat('Y-m-d', $new_data['end_date'])) {
    $errors[] = 'تاريخ الانتهاء الجديد غير صالح.';
} elseif (new DateTime($new_data['start_date']) >= new DateTime($new_data['end_date'])) {
    $errors[] = 'تاريخ الانتهاء الجديد يجب أن يكون بعد تاريخ البدء.';
}
if (empty($new_data['rent_amount']) || $new_data['rent_amount'] <= 0) {
    $errors[] = 'الرجاء إدخال قيمة إيجار صالحة.';
}

if (!empty($errors)) {
    Session::flash('error', implode('<br>', $errors));
    redirect('public/index.php?url=leases/renew/' . $old_lease_id);
}

// دمج البيانات الجديدة مع البيانات القديمة لإنشاء العقد الجديد
$lease_creation_data = array_merge($new_data, [
    'unit_id'           => $old_lease['unit_id'],
    'tenant_user_id'    => $old_lease['tenant_user_id'],
    'payment_frequency' => $old_lease['payment_frequency'], // افتراض أن دورية الدفع لا تتغير
    'security_deposit'  => $old_lease['security_deposit'], // افتراض أن التأمين ينتقل للعقد الجديد
    'status'            => 'Active'
]);

// الخطوة 5: بدء معاملة (Transaction)
$db = Database::getInstance()->pdo;
try {
    $db->beginTransaction();

    // 5.1: تحديث حالة العقد القديم إلى "منتهي"
    Lease::updateStatus($old_lease_id, 'Expired');

    // 5.2: إنشاء العقد الجديد
    $new_lease_id = Lease::create($lease_creation_data);
    if (!$new_lease_id) {
        throw new Exception("فشل إنشاء سجل العقد الجديد.");
    }
    
    // 5.3: توليد الفواتير المستقبلية للعقد الجديد
    $start_date = new DateTime($new_data['start_date']);
    $end_date = new DateTime($new_data['end_date']);
    $interval_map = ['Monthly' => 'P1M', 'Quarterly' => 'P3M', 'Annually' => 'P1Y'];
    $payment_count_map = ['Monthly' => 12, 'Quarterly' => 4, 'Annually' => 1];
    
    $interval = new DateInterval($interval_map[$lease_creation_data['payment_frequency']]);
    $invoice_amount = $new_data['rent_amount'] / $payment_count_map[$lease_creation_data['payment_frequency']];

    $current_due_date = clone $start_date;
    while ($current_due_date < $end_date) {
        Invoice::create([
            'lease_id' => $new_lease_id,
            'amount' => $invoice_amount,
            'due_date' => $current_due_date->format('Y-m-d')
        ]);
        $current_due_date->add($interval);
    }
    
    // 5.4: تأكيد جميع العمليات
    $db->commit();

    // الخطوة 6: إعادة التوجيه مع رسالة نجاح
    Session::flash('success', 'تم تجديد العقد بنجاح!');
    redirect('public/index.php?url=leases/view/' . $new_lease_id);

} catch (Exception $e) {
    // في حالة حدوث أي خطأ، يتم التراجع عن جميع التغييرات
    $db->rollBack();
    Session::flash('error', 'حدث خطأ أثناء تجديد العقد: ' . $e->getMessage());
    redirect('public/index.php?url=leases/renew/' . $old_lease_id);
}
