<?php
// File: app/views/layouts/guest_layout.php

/**
 * ===================================================================
 * القالب الرئيسي للزوار (Guest Layout)
 * ===================================================================
 * هذا الملف هو القالب المرئي المستخدم لجميع الصفحات التي لا تتطلب تسجيل دخول
 * (مثل صفحة تسجيل الدخول وصفحة التسجيل العام). يوفر هيكلاً متناسقاً وبسيطاً
 * لهذه الصفحات.
 *
 * @var string $page_title عنوان الصفحة الذي يتم تمريره من ملف العرض
 * @var callable $content_callback دالة تقوم بطباعة المحتوى الخاص بالصفحة
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php e($page_title ?? 'مرحباً بك'); ?> - <?php echo APP_NAME; ?></title>

    <!-- Bootstrap CSS (RTL version) from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet" xintegrity="sha384-dpuaG1suU0eT09BZpEylU3+eVMwsjFz2lfFPD2GZ/bYIcnE7CgMCONTzhFPAOEA_">

    <!-- Bootstrap Icons from CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom Styles for a clean look -->
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #F9FAFB; /* خلفية شبه بيضاء مريحة للعين */
            color: #1F2937; /* لون نص أساسي داكن */
        }
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .auth-card {
            max-width: 450px;
            width: 100%;
            border: none;
            border-radius: 0.75rem; /* زوايا دائرية أكثر */
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }
        .auth-header .logo {
             color: #2563EB; /* اللون الأساسي المقترح */
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="auth-card">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-4 auth-header">
                    <i class="bi bi-buildings-fill logo fs-1"></i>
                    <h1 class="h3 fw-bold mt-2"><?php echo APP_NAME; ?></h1>
                </div>

                <?php
                // تضمين نظام عرض التنبيهات المنبثقة
                // (سيتم إنشاء هذا الملف لاحقاً)
                // require_once APP_ROOT . '/app/views/partials/_alerts.php';
                ?>
                
                <?php
                // استدعاء وعرض المحتوى الخاص بالصفحة الحالية (مثل نموذج تسجيل الدخول)
                // هذا يسمح بإعادة استخدام هذا القالب لصفحات مختلفة
                if (isset($content_callback) && is_callable($content_callback)) {
                    call_user_func($content_callback);
                }
                ?>
            </div>
            <div class="card-footer p-3 text-center bg-transparent border-top-0">
                 <small class="text-muted">
                    &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. جميع الحقوق محفوظة.
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"></script>
    
    <!-- SweetAlert2 from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Your Custom JS File -->
    <script src="<?php echo asset('js/custom.js'); ?>"></script>
</body>
</html>
