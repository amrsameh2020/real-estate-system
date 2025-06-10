<?php
// File: app/views/auth/login.php

/**
 * ===================================================================
 * صفحة تسجيل الدخول (Login Page)
 * ===================================================================
 * هذه هي واجهة المستخدم التي تعرض نموذج تسجيل الدخول.
 * تستخدم القالب العام للزوار (guest_layout.php) لعرض نفسها بشكل متناسق.
 * تقوم بإرسال بيانات النموذج إلى الرابط 'handle-login' الذي يعالجه
 * ملف index.php ويوجهه إلى ملف المعالجة الصحيح.
 */

// تضمين ملف التشغيل الأساسي للوصول إلى الدوال والكلاسات مثل Security و e()
require_once __DIR__ . '/../../core/bootstrap.php';

// تحديد عنوان الصفحة الذي سيظهر في وسم <title> وفي الشريط العلوي
$page_title = 'تسجيل الدخول';

// تعريف دالة المحتوى التي سيتم تمريرها للقالب
// هذا الأسلوب يسمح بإعادة استخدام القالب لصفحات مختلفة
$content_callback = function() {
?>
    <h5 class="card-title text-center mb-4 fw-light fs-5">تسجيل الدخول إلى حسابك</h5>
    
    <!-- يبدأ النموذج هنا. يتم إرسال البيانات باستخدام طريقة POST للأمان -->
    <form action="?url=handle-login" method="POST" novalidate>
        
        <!-- حقل مخفي لتوكن CSRF للحماية من هجمات تزوير الطلبات -->
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

        <div class="form-floating mb-3">
            <input 
                type="email" 
                class="form-control" 
                id="email" 
                name="email" 
                placeholder="name@example.com" 
                required 
                autofocus>
            <label for="email">البريد الإلكتروني</label>
        </div>
        
        <div class="form-floating mb-3">
            <input 
                type="password" 
                class="form-control" 
                id="password" 
                name="password" 
                placeholder="Password" 
                required>
            <label for="password">كلمة المرور</label>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
             <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                <label class="form-check-label" for="rememberMe">
                   <small> تذكرني</small>
                </label>
            </div>
            <a href="#" class="text-decoration-none"><small>هل نسيت كلمة المرور؟</small></a>
        </div>

        <div class="d-grid mb-2">
            <button class="btn btn-primary btn-lg fw-bold" type="submit">تسجيل الدخول</button>
        </div>
        
        <div class="text-center">
             <small class="text-muted">ليس لديك حساب؟</small>
             <a href="?url=register" class="text-decoration-none fw-bold">
                إنشاء حساب جديد
            </a>
        </div>

    </form>
<?php
};

// تضمين وعرض القالب العام للزوار، مع تمرير عنوان الصفحة ومحتواها
require_once APP_ROOT . '/app/views/layouts/guest_layout.php';
