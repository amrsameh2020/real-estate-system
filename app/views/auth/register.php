<?php
// File: app/views/auth/register.php

/**
 * ===================================================================
 * صفحة إنشاء حساب جديد (Register Page)
 * ===================================================================
 * هذه هي واجهة المستخدم التي تعرض نموذج التسجيل العام (للمستأجرين مثلاً).
 * تستخدم القالب العام للزوار (guest_layout.php) لعرض نفسها بشكل متناسق.
 * تقوم بإرسال بيانات النموذج إلى الرابط 'handle-register' للمعالجة.
 */

// تضمين ملف التشغيل الأساسي للوصول إلى الدوال والكلاسات
require_once __DIR__ . '/../../core/bootstrap.php';

// تحديد عنوان الصفحة
$page_title = 'إنشاء حساب جديد';

// تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() {
?>
    <h5 class="card-title text-center mb-4 fw-light fs-5">إنشاء حساب جديد</h5>
    
    <form action="?url=handle-register" method="POST" novalidate>
        
        <!-- حقل مخفي لتوكن CSRF للحماية -->
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="الاسم الكامل" required>
            <label for="full_name">الاسم الكامل</label>
        </div>

        <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
            <label for="email">البريد الإلكتروني</label>
        </div>
        
        <div class="form-floating mb-3">
            <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="رقم الهاتف" required>
            <label for="phone_number">رقم الهاتف</label>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password">كلمة المرور</label>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            <label for="confirm_password">تأكيد كلمة المرور</label>
        </div>

        <div class="d-grid mb-2">
            <button class="btn btn-success btn-lg fw-bold" type="submit">إنشاء الحساب</button>
        </div>
        
        <div class="text-center">
             <small class="text-muted">لديك حساب بالفعل؟</small>
             <a href="?url=login" class="text-decoration-none fw-bold">
                تسجيل الدخول
            </a>
        </div>

    </form>
<?php
};

// تضمين وعرض القالب العام للزوار، مع تمرير عنوان الصفحة ومحتواها
require_once APP_ROOT . '/app/views/layouts/guest_layout.php';
