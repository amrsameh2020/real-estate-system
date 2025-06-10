<?php
// File: app/models/Payment.php

/**
 * ===================================================================
 * موديل الدفعة (Payment Model)
 * ===================================================================
 * هذا الكلاس مسؤول عن جميع عمليات قاعدة البيانات المتعلقة بجدول `payments`.
 * يوفر دوال لتسجيل الدفعات وربطها بالفواتير، والأهم من ذلك، يقوم بتحديث
 * حالة الفاتورة تلقائياً بعد تسجيل أي دفعة جديدة.
 */
class Payment {

    /**
     * تسجيل دفعة جديدة في قاعدة البيانات وتحديث حالة الفاتورة المرتبطة بها.
     * @param array $data بيانات الدفعة (invoice_id, amount_paid, payment_date, etc.).
     * @return string|false The ID of the new payment or false on failure.
     */
    public static function create($data) {
        $db = Database::getInstance();
        $pdo = $db->pdo;

        try {
            // بدء معاملة (Transaction) لضمان تنفيذ العمليتين معاً (إضافة الدفعة وتحديث الفاتورة)
            $pdo->beginTransaction();

            // الخطوة 1: إضافة سجل الدفعة الجديدة
            $sql_payment = "INSERT INTO payments (invoice_id, amount_paid, payment_date, payment_method, transaction_reference, received_by_user_id) 
                            VALUES (:invoice_id, :amount_paid, :payment_date, :payment_method, :transaction_reference, :received_by_user_id)";
            
            $params_payment = [
                ':invoice_id' => $data['invoice_id'],
                ':amount_paid' => $data['amount_paid'],
                ':payment_date' => $data['payment_date'],
                ':payment_method' => $data['payment_method'],
                ':transaction_reference' => $data['transaction_reference'] ?? null,
                ':received_by_user_id' => $data['received_by_user_id'] ?? null,
            ];
            
            $db->query($sql_payment, $params_payment);
            $paymentId = $pdo->lastInsertId();

            // الخطوة 2: تحديث حالة الفاتورة
            self::updateInvoiceStatusAfterPayment($data['invoice_id']);

            // تأكيد المعاملة (Commit) إذا نجحت كل العمليات
            $pdo->commit();
            
            return $paymentId;

        } catch (PDOException $e) {
            // في حالة حدوث أي خطأ، يتم التراجع عن جميع التغييرات (Rollback)
            $pdo->rollBack();
            // error_log($e->getMessage());
            return false;
        }
    }

    /**
     * البحث عن دفعة عن طريق معرفها (ID).
     * @param int $id The ID of the payment.
     * @return mixed The payment data as an associative array or false if not found.
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM payments WHERE id = ? LIMIT 1", [$id]);
        return $stmt->fetch();
    }
    
    /**
     * جلب جميع الدفعات الخاصة بفاتورة معينة.
     * @param int $invoice_id
     * @return array
     */
    public static function findByInvoiceId($invoice_id) {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date DESC", [$invoice_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * دالة مساعدة خاصة لتحديث حالة الفاتورة بعد تسجيل دفعة.
     * @param int $invoice_id
     */
    private static function updateInvoiceStatusAfterPayment($invoice_id) {
        $db = Database::getInstance();

        // جلب المبلغ الإجمالي للفاتورة
        $invoice = Invoice::findById($invoice_id);
        if (!$invoice) return; // الخروج إذا لم يتم العثور على الفاتورة

        $invoice_total = (float)$invoice['total_amount'];

        // حساب مجموع المبالغ المدفوعة لهذه الفاتورة
        $stmt_payments = $db->query("SELECT SUM(amount_paid) as total_paid FROM payments WHERE invoice_id = ?", [$invoice_id]);
        $total_paid = (float)$stmt_payments->fetch()['total_paid'];

        // تحديد الحالة الجديدة للفاتورة
        $new_status = 'PartiallyPaid'; // الحالة الافتراضية
        if ($total_paid >= $invoice_total) {
            $new_status = 'Paid';
        }

        // تحديث حالة الفاتورة في قاعدة البيانات
        Invoice::updateStatus($invoice_id, $new_status);
    }
}
