<?php
// File: app/views/crm/communications.php

/**
 * ===================================================================
 * صفحة مركز التواصل (Communications Center)
 * ===================================================================
 * تتيح هذه الصفحة لمدير النظام إرسال رسائل جماعية (SMS/Email)
 * إلى مجموعات محددة من العملاء.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: تحديد عنوان الصفحة
$page_title = 'مركز التواصل والإشعارات';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold">إرسال رسالة جماعية</h5>
        </div>
        <div class="card-body">
            <form action="?url=scripts/crm/handle_send_communication" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                
                <div class="mb-3">
                    <label for="target_group" class="form-label">إرسال إلى</label>
                    <select class="form-select" id="target_group" name="target_group" required>
                        <option value="all_tenants">جميع المستأجرين</option>
                        <option value="all_owners">جميع الملاك</option>
                        <option value="all_users">جميع المستخدمين</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="channel" class="form-label">عبر قناة</label>
                    <select class="form-select" id="channel" name="channel" required>
                        <option value="email">البريد الإلكتروني</option>
                        <option value="sms">رسالة نصية (SMS)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="subject" class="form-label">الموضوع (للبريد الإلكتروني)</label>
                    <input type="text" class="form-control" id="subject" name="subject">
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">نص الرسالة</label>
                    <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">إرسال الرسالة</button>
            </form>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>