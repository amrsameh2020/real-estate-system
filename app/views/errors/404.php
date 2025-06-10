<?php
// File: app/views/errors/404.php

/**
 * ===================================================================
 * صفحة خطأ 404 (404 Not Found Page)
 * ===================================================================
 * هذه الصفحة تُعرض عندما يحاول المستخدم الوصول إلى رابط غير موجود.
 * توفر رسالة واضحة وتوجيه للمستخدم.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام (قد لا يكون ضرورياً جداً لصفحة خطأ، لكن للمحافظة على الاتساق)
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: تحديد عنوان الصفحة
$page_title = '404 - الصفحة غير موجودة';

// الخطوة 3: تعيين رمز حالة HTTP إلى 404
http_response_code(404);

// الخطوة 4: تعريف دالة المحتوى التي سيتم تمريرها للقالب
$content_callback = function() {
?>
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="text-center">
            <h1 class="display-1 fw-bold text-danger">404</h1>
            <p class="fs-3"> <span class="text-danger">عذراً!</span> الصفحة غير موجودة.</p>
            <p class="lead">
                الصفحة التي تبحث عنها قد تكون حذفت، أو غيرت اسمها، أو غير متوفرة بشكل مؤقت.
            </p>
            <a href="?url=dashboard" class="btn btn-primary">العودة إلى لوحة التحكم</a>
        </div>
    </div>
<?php
};

// الخطوة 5: تضمين وعرض القالب العام للزوار (أو أي قالب بسيط لا يتطلب مصادقة)
// يمكن استخدام guest_layout.php أو تصميم صفحة بسيطة جداً
require_once APP_ROOT . '/app/views/layouts/guest_layout.php';

?>