<?php
// File: app/views/auth/register.php

/**
 * ===================================================================
 * صفحة إنشاء حساب جديد (Register Page)
 * ===================================================================
 * تعرض هذه الصفحة نموذج التسجيل العام (للمستأجرين أو الملاك الجدد).
 * تستخدم القالب العام للزوار (guest_layout.php).
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: تحديد عنوان الصفحة
$page_title = 'إنشاء حساب جديد';

// الخطوة 3: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() {
?>
    <h5 class="card-title text-center mb-4 fw-light fs-5">إنشاء حساب جديد</h5>
    
    <form action="?url=handle-register" method="POST" class="needs-validation" novalidate>
        
        <!-- حقل مخفي لتوكن CSRF للحماية -->
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="الاسم الكامل" required>
            <label for="full_name">الاسم الكامل</label>
            <div class="invalid-feedback">الاسم الكامل مطلوب.</div>
        </div>

        <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
            <label for="email">البريد الإلكتروني</label>
            <div class="invalid-feedback">الرجاء إدخال بريد إلكتروني صالح.</div>
        </div>
        
        <div class="form-floating mb-3">
            <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="رقم الهاتف" required>
            <label for="phone_number">رقم الهاتف</label>
            <div class="invalid-feedback">رقم الهاتف مطلوب.</div>
        </div>

        <div class="mb-3">
             <label class="form-label">تسجيل كـ:</label>
             <div class="form-check">
               <input class="form-check-input" type="radio" name="role_id" id="roleTenant" value="4" checked>
               <label class="form-check-label" for="roleTenant">مستأجر</label>
             </div>
             <div class="form-check">
               <input class="form-check-input" type="radio" name="role_id" id="roleOwner" value="3">
               <label class="form-check-label" for="roleOwner">مالك</label>
             </div>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="8">
            <label for="password">كلمة المرور</label>
            <div class="invalid-feedback">كلمة المرور يجب أن لا تقل عن 8 أحرف.</div>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            <label for="confirm_password">تأكيد كلمة المرور</label>
            <div class="invalid-feedback">الرجاء تأكيد كلمة المرور.</div>
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
    <script>
        // Bootstrap form validation
        (function () {
          'use strict'
          var forms = document.querySelectorAll('.needs-validation')
          Array.prototype.slice.call(forms)
            .forEach(function (form) {
              form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                  event.preventDefault()
                  event.stopPropagation()
                }
                form.classList.add('was-validated')
              }, false)
            })
        })()
    </script>
<?php
};

// الخطوة 4: تضمين وعرض القالب العام للزوار
require_once APP_ROOT . '/app/views/layouts/guest_layout.php';