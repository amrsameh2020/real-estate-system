<?php
// File: app/views/settings/regional.php

/**
 * ===================================================================
 * صفحة الإعدادات الإقليمية والمالية
 * ===================================================================
 * تتيح لمدير النظام ضبط الإعدادات الخاصة بالدولة والعمليات المالية،
 * مثل العملة، الضريبة، وصيغة التاريخ.
 */

// الخطوة 1: تحميل وتشغيل قلب النظام
require_once __DIR__ . '/../../core/bootstrap.php';

// الخطوة 2: التحقق من الصلاحية
Auth::requireRole('SystemAdmin');

// الخطوة 3: جلب الإعدادات الحالية
// $settings = Setting::getAll();
$settings = [
    'currency_symbol' => 'ريال',
    'currency_code' => 'SAR',
    'vat_percentage' => '15',
    'date_format' => 'Y-m-d',
];

// الخطوة 4: تحديد عنوان الصفحة
$page_title = 'الإعدادات الإقليمية والمالية';

// الخطوة 5: تعريف دالة المحتوى
$content_callback = function() use ($settings) {
?>
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 fw-bold"><?php e($GLOBALS['page_title']); ?></h5>
        </div>
        <div class="card-body">
            <form action="?url=scripts/settings/handle_save_settings" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
                
                <h6 class="text-primary">إعدادات العملة</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label for="currency_symbol" class="form-label">رمز العملة</label><input type="text" class="form-control" name="settings[currency_symbol]" value="<?php e($settings['currency_symbol']); ?>"></div>
                    <div class="col-md-6"><label for="currency_code" class="form-label">كود العملة (ISO)</label><input type="text" class="form-control" name="settings[currency_code]" value="<?php e($settings['currency_code']); ?>"></div>
                </div>

                <h6 class="text-primary">إعدادات الضرائب</h6>
                <div class="row g-3 mb-4">
                     <div class="col-md-6"><label for="vat_percentage" class="form-label">نسبة ضريبة القيمة المضافة (%)</label><input type="number" step="0.01" class="form-control" name="settings[vat_percentage]" value="<?php e($settings['vat_percentage']); ?>"></div>
                </div>

                 <h6 class="text-primary">إعدادات التنسيق</h6>
                <div class="row g-3 mb-4">
                     <div class="col-md-6"><label for="date_format" class="form-label">صيغة التاريخ</label>
                        <select class="form-select" name="settings[date_format]">
                            <option value="Y-m-d" <?php echo ($settings['date_format'] === 'Y-m-d') ? 'selected' : ''; ?>>2025-06-12</option>
                            <option value="d-m-Y" <?php echo ($settings['date_format'] === 'd-m-Y') ? 'selected' : ''; ?>>12-06-2025</option>
                        </select>
                     </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-success">حفظ الإعدادات</button>
            </form>
        </div>
    </div>
<?php
};

// الخطوة 6: تضمين القالب الرئيسي
require_once APP_ROOT . '/app/views/layouts/admin_layout.php';
?>