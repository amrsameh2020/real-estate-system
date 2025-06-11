// File: public/assets/js/custom.js

/**
 * ===================================================================
 * ملف JavaScript المخصص (Custom JS)
 * ===================================================================
 * يحتوي هذا الملف على الكود الخاص بالتفاعلات الديناميكية في الواجهة الأمامية،
 * مثل طي الشريط الجانبي، طلبات AJAX، وتفعيل النوافذ المنبثقة.
 */

// التأكد من أن DOM قد تم تحميله بالكامل قبل تنفيذ أي كود
document.addEventListener('DOMContentLoaded', function() {

    // --- Sidebar Toggle Functionality ---
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            // إضافة أو إزالة كلاس من body للتحكم في حالة الشريط الجانبي
            document.body.classList.toggle('sidebar-collapsed');
        });
    }

    // --- Bootstrap Tooltip Initialization ---
    // تفعيل التلميحات (Tooltips) المستخدمة في الأزرار
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // --- AJAX Example Function ---
    // يمكنك استخدام هذه الدالة كقالب لإرسال طلبات AJAX
    async function performAjaxRequest(url, method = 'GET', data = null) {
        try {
            const options = {
                method: method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest', // للإشارة بأن الطلب هو AJAX
                    'Content-Type': 'application/json'
                }
            };

            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('AJAX request failed:', error);
            // عرض رسالة خطأ للمستخدم
            Swal.fire('خطأ!', 'حدث خطأ أثناء الاتصال بالخادم.', 'error');
            return null;
        }
    }

    // مثال على كيفية استخدام الدالة
    // performAjaxRequest('?url=api/get_units&property_id=1').then(data => {
    //     if(data) {
    //         console.log(data);
    //     }
    // });

});