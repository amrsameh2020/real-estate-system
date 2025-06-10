<?php
// File: app/views/partials/_alerts.php

/**
 * ===================================================================
 * ملف عرض التنبيهات (_alerts.php)
 * ===================================================================
 * هذا الملف مسؤول عن عرض التنبيهات المنبثقة باستخدام مكتبة SweetAlert2.
 * يقوم بالتحقق من وجود رسائل "flash" (التي تظهر لمرة واحدة) في الجلسة
 * ويعرضها بتصميم جذاب. يتم تضمين هذا الملف في نهاية القوالب الرئيسية
 * (layouts) بعد تضمين مكتبة SweetAlert2 JS.
 */

// جلب رسائل الـ flash من الجلسة. دالة flash() تقوم بجلب الرسالة وحذفها مباشرة.
$success_message = Session::flash('success');
$error_message = Session::flash('error');
$warning_message = Session::flash('warning');

?>

<?php if ($success_message): ?>
    <!-- عرض رسالة النجاح -->
    <script>
        // التأكد من أن DOM قد تم تحميله بالكامل قبل تنفيذ السكربت
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'نجاح!',
                text: '<?php echo addslashes($success_message); ?>', // استخدام addslashes لضمان سلامة النص داخل JS
                icon: 'success',
                confirmButtonText: 'حسناً',
                timer: 3000, // إغلاق الرسالة تلقائياً بعد 3 ثواني
                timerProgressBar: true,
                toast: true, // عرض الرسالة كتنبيه صغير
                position: 'top-start', // موقع ظهور الرسالة
                showConfirmButton: false // إخفاء زر التأكيد
            });
        });
    </script>
<?php endif; ?>

<?php if ($error_message): ?>
     <!-- عرض رسالة الخطأ -->
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'خطأ!',
                text: '<?php echo addslashes($error_message); ?>',
                icon: 'error',
                confirmButtonText: 'محاولة مرة أخرى'
            });
        });
    </script>
<?php endif; ?>

<?php if ($warning_message): ?>
     <!-- عرض رسالة التحذير -->
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'تنبيه!',
                text: '<?php echo addslashes($warning_message); ?>',
                icon: 'warning',
                confirmButtonText: 'حسناً'
            });
        });
    </script>
<?php endif; ?>
