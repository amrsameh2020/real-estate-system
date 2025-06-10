<?php
// File: app/views/partials/_navbar.php

/**
 * ===================================================================
 * الشريط العلوي (Navbar)
 * ===================================================================
 * هذا الملف مسؤول عن عرض الشريط العلوي في لوحة التحكم.
 * يحتوي على زر لطي الشريط الجانبي، عنوان الصفحة الحالي،
 * وبعض العناصر التفاعلية مثل البحث والإشعارات.
 *
 * @var string $page_title عنوان الصفحة الحالية الذي يتم تمريره من القالب.
 */
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-3 shadow-sm mb-4 p-3">
        <div class="container-fluid">
            <!-- Sidebar Toggle Button -->
            <button class="btn btn-light me-3" id="sidebar-toggle" type="button" aria-label="Toggle sidebar">
                <i class="bi bi-list fs-5"></i>
            </button>

            <!-- Page Title -->
            <h5 class="navbar-brand my-0 fw-bold text-dark"><?php e($page_title ?? 'لوحة التحكم'); ?></h5>

            <!-- Navbar items on the left -->
            <div class="ms-auto d-flex align-items-center">
                <!-- Search Form (Optional) -->
                <form class="d-none d-md-flex me-3" role="search">
                    <div class="input-group input-group-sm">
                        <input class="form-control" type="search" placeholder="بحث..." aria-label="Search">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>

                <!-- Notifications (Example) -->
                <div class="dropdown">
                    <a href="#" class="btn btn-light position-relative" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <i class="bi bi-bell-fill"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6em;">
                            3
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 280px;">
                        <li class="p-2">
                            <h6 class="dropdown-header px-2">الإشعارات</h6>
                        </li>
                        <li><a class="dropdown-item d-flex align-items-start py-2" href="#">
                            <i class="bi bi-file-earmark-check-fill text-success me-2"></i>
                            <div>
                                <small class="fw-bold d-block">تم الموافقة على العقد #1024</small>
                                <small class="text-muted">منذ 5 دقائق</small>
                            </div>
                        </a></li>
                        <li><a class="dropdown-item d-flex align-items-start py-2" href="#">
                            <i class="bi bi-tools text-warning me-2"></i>
                             <div>
                                <small class="fw-bold d-block">طلب صيانة جديد</small>
                                <small class="text-muted">منذ 30 دقيقة</small>
                            </div>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center text-primary" href="#">عرض كل الإشعارات</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>
