<?php
// File: app/views/layouts/admin_layout.php

/**
 * ===================================================================
 * القالب الرئيسي للوحة التحكم (Admin Layout)
 * ===================================================================
 * هذا هو الهيكل البصري لجميع صفحات لوحة التحكم الداخلية (لمدير النظام،
 * المحاسب، إلخ). يقوم بتضمين الأجزاء المشتركة مثل الشريط العلوي والجانبي
 * والتذييل، ويعرض محتوى الصفحة الديناميكي في المنتصف.
 *
 * @var string $page_title عنوان الصفحة الذي يتم تمريره من ملف العرض
 * @var callable $content_callback دالة تقوم بطباعة المحتوى الخاص بالصفحة
 */

// التأكد من أن المستخدم مسجل دخوله، وإلا يتم تحويله لصفحة الدخول
// هذا السطر بمثابة حماية إضافية على مستوى القالب
if (!Auth::check()) {
    redirect('public/index.php?url=login');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php e($page_title ?? 'لوحة التحكم'); ?> - <?php echo APP_NAME; ?></title>

    <!-- Bootstrap CSS (RTL version) from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet" xintegrity="sha384-dpuaG1suU0eT09BZpEylU3+eVMwsjFz2lfFPD2GZ/bYIcnE7CgMCONTzhFPAOEA_">
    
    <!-- Bootstrap Icons from CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS for dashboard layout -->
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa; /* لون خلفية ناعم */
        }
        .sidebar {
            width: 280px;
            min-height: 100vh;
            box-shadow: 0 0 1rem rgba(0,0,0,.05);
            transition: margin-right .3s ease-in-out;
        }
        .content-wrapper {
            transition: margin-right .3s ease-in-out;
            width: 100%;
            /* margin-right: 280px; */
        }
        /* Style for collapsed sidebar */
        body.sidebar-collapsed .sidebar {
            margin-right: -280px;
        }
        body.sidebar-collapsed .content-wrapper {
            margin-right: 0;
        }
        @media (max-width: 992px) {
            .sidebar {
                margin-right: -280px;
            }
            .content-wrapper {
                margin-right: 0;
            }
            body.sidebar-collapsed .sidebar {
                 margin-right: 0;
            }
        }
    </style>
</head>
<body>

    <div class="d-flex">
        <?php 
        // تضمين الشريط الجانبي الديناميكي
        // يفترض أن هذا الملف موجود ويحتوي على منطق عرض القائمة حسب دور المستخدم
        require_once APP_ROOT . '/app/views/partials/_sidebar.php'; 
        ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            
             <?php
            // تضمين الشريط العلوي (Navbar)
            require_once APP_ROOT . '/app/views/partials/_navbar.php'; 
            ?>
            
            <!-- Main Page Content -->
            <main class="p-4">
                <?php
                // تضمين نظام عرض التنبيهات المنبثقة
                require_once APP_ROOT . '/app/views/partials/_alerts.php';
                ?>
                
                <?php
                // استدعاء وعرض المحتوى الخاص بالصفحة الحالية
                if (isset($content_callback) && is_callable($content_callback)) {
                    call_user_func($content_callback);
                }
                ?>
            </main>
        </div>
    </div>


    <!-- Bootstrap JS Bundle from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"></script>
    
    <!-- SweetAlert2 from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Your Custom JS File -->
    <script src="<?php echo asset('js/custom.js'); ?>"></script>
    
    <!-- Inline script for layout functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            if(sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.body.classList.toggle('sidebar-collapsed');
                });
            }
        });
    </script>
</body>
</html>
