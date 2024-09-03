<?php
// افزودن منو اصلی و زیرمنوها به منوی مدیریت وردپرس
function crm_add_menus() {
    add_menu_page(
        'ماژول مدیریت مشتریان',  // عنوان منو
        'مدیریت مشتریان',         // عنوان نمایش در منو
        'manage_options',          // سطح دسترسی
        'crm-main-menu',           // شناسه منو
        'crm_render_customers_page', // تابعی که برای رندر شدن صفحه منوی اصلی فراخوانی می‌شود
        'dashicons-admin-users',    // آیکون منو
        6                           // موقعیت منو در پنل مدیریت
    );

    add_submenu_page(
        'crm-main-menu',            // شناسه منوی والد
        'مدیریت سرویس‌ها',          // عنوان زیرمنو
        'مدیریت سرویس‌ها',          // عنوان نمایش در منو
        'manage_options',           // سطح دسترسی
        'crm-services',             // شناسه زیرمنو
        'crm_render_services_page'  // تابعی که برای رندر شدن صفحه زیرمنو فراخوانی می‌شود
    );

    add_submenu_page(
        'crm-main-menu',
        'گزارش‌ها',
        'گزارش‌ها',
        'manage_options',
        'crm-reports',
        'crm_render_reports_page'
    );

    add_submenu_page(
        'crm-main-menu',
        'تنظیمات',
        'تنظیمات',
        'manage_options',
        'crm-settings',
        'crm_render_settings_page'
    );
}
add_action('admin_menu', 'crm_add_menus');

// حذف منوی اضافی 'Beauty CRM'
function remove_unwanted_menu() {
    remove_menu_page('beauty-crm');
}
add_action('admin_menu', 'remove_unwanted_menu', 999);

// رندر کردن صفحه مدیریت مشتریان (فعلاً خالی)
function crm_render_customers_page() {
    ?>
    <div class="wrap">
        <h1>مدیریت مشتریان</h1>
        <p>این صفحه در حال حاضر خالی است.</p>
    </div>
    <?php
}

// رندر کردن صفحه مدیریت سرویس‌ها
function crm_render_services_page() {
    global $wpdb;

    // دریافت سرویس‌های موجود در Bookly
    $services = crm_get_bookly_services();

    ?>
    <div class="wrap">
        <h1>مدیریت سرویس‌ها</h1>
        <form method="post" action="">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>نام سرویس</th>
                        <th>دوره‌های تکرار</th>
                        <th>سرویس‌های مکمل</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($services): ?>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo esc_html($service->title); ?></td>
                            <td><?php echo esc_html(crm_get_repeat_intervals($service->id)); ?></td>
                            <td><?php echo esc_html(crm_get_complementary_services($service->id)); ?></td>
                            <td>
                                <!-- دکمه‌های جدید برای مدیریت دوره‌های تکرار و سرویس‌های مکمل -->
                                <a href="#" class="button-primary crm-manage-repeats" data-service-id="<?php echo esc_attr($service->id); ?>">دوره‌های تکرار</a>
                                <a href="#" class="button-primary crm-manage-complements" data-service-id="<?php echo esc_attr($service->id); ?>">سرویس‌های مکمل</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">هیچ سرویسی موجود نیست.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>

    <!-- پاپ‌آپ دوره‌های تکرار -->
    <div id="repeatPopup" class="crm-popup">
        <div class="crm-popup-content">
            <span class="crm-popup-close">&times;</span>
            <h2>افزودن دوره تکرار</h2>
            <p>لطفاً دوره تکرار جدید را به روزها وارد کنید:</p>
            <input type="number" id="repeatDays" min="1" placeholder="تعداد روزها">
            <button id="saveRepeat">ثبت</button>
            <button id="cancelRepeat">انصراف</button>
        </div>
    </div>

    <!-- پاپ‌آپ سرویس‌های مکمل -->
    <div id="complementPopup" class="crm-popup">
        <div class="crm-popup-content">
            <span class="crm-popup-close">&times;</span>
            <h2>افزودن سرویس مکمل</h2>
            <p>لطفاً یک سرویس مکمل انتخاب کنید:</p>
            <select id="complementService">
                <!-- سرویس‌های موجود به جز سرویس جاری و سرویس‌های مکمل قبلی -->
                <?php foreach ($services as $service): ?>
                    <option value="<?php echo esc_attr($service->id); ?>"><?php echo esc_html($service->title); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="complementDays" min="1" placeholder="تعداد روزها">
            <button id="saveComplement">ثبت</button>
            <button id="cancelComplement">انصراف</button>
        </div>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // نمایش و پنهان‌سازی پاپ‌آپ دوره‌های تکرار
            const repeatPopup = document.getElementById('repeatPopup');
            const repeatButtons = document.querySelectorAll('.crm-manage-repeats');
            const closeRepeatPopup = repeatPopup.querySelector('.crm-popup-close');

            repeatButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    repeatPopup.style.display = 'block';
                });
            });

            closeRepeatPopup.addEventListener('click', function() {
                repeatPopup.style.display = 'none';
            });

            document.getElementById('cancelRepeat').addEventListener('click', function() {
                repeatPopup.style.display = 'none';
            });

            // نمایش و پنهان‌سازی پاپ‌آپ سرویس‌های مکمل
            const complementPopup = document.getElementById('complementPopup');
            const complementButtons = document.querySelectorAll('.crm-manage-complements');
            const closeComplementPopup = complementPopup.querySelector('.crm-popup-close');

            complementButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    complementPopup.style.display = 'block';
                });
            });

            closeComplementPopup.addEventListener('click', function() {
                complementPopup.style.display = 'none';
            });

            document.getElementById('cancelComplement').addEventListener('click', function() {
                complementPopup.style.display = 'none';
            });

            // ثبت اطلاعات در پاپ‌آپ دوره‌های تکرار
            document.getElementById('saveRepeat').addEventListener('click', function() {
                const repeatDays = document.getElementById('repeatDays').value;
                if (repeatDays <= 0) {
                    alert('تعداد روزها باید بیشتر از 0 باشد.');
                } else {
                    // اینجا می‌توانید بررسی‌های لازم برای ثبت دوره را انجام دهید
                    alert('دوره تکرار با موفقیت ثبت شد.');
                    repeatPopup.style.display = 'none';
                }
            });

            // ثبت اطلاعات در پاپ‌آپ سرویس‌های مکمل
            document.getElementById('saveComplement').addEventListener('click', function() {
                const complementService = document.getElementById('complementService').value;
                const complementDays = document.getElementById('complementDays').value;
                if (complementDays <= 0) {
                    alert('تعداد روزها باید بیشتر از 0 باشد.');
                } else {
                    // اینجا می‌توانید بررسی‌های لازم برای ثبت سرویس مکمل را انجام دهید
                    alert('سرویس مکمل با موفقیت ثبت شد.');
                    complementPopup.style.display = 'none';
                }
            });
        });
    </script>

    <style>
        /* استایل‌های پاپ‌آپ برای نمایش به صورت مودال */
        .crm-popup {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 400px;
            background: #fff;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border-radius: 8px;
        }

        .crm-popup-content {
            position: relative;
        }

        .crm-popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
        }

        .crm-popup h2 {
            margin: 0 0 15px;
            font-size: 20px;
            text-align: center;
        }

        .crm-popup p {
            text-align: center;
        }

        .crm-popup input[type="number"],
        .crm-popup select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
        }

        .crm-popup button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }

        #saveRepeat, #saveComplement {
            background-color: #0073aa;
            color: #fff;
        }

        #cancelRepeat, #cancelComplement {
            background-color: #ccc;
            color: #333;
        }
    </style>
    <?php
}

// دریافت سرویس‌های موجود در Bookly
function crm_get_bookly_services() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bookly_services';
    return $wpdb->get_results("SELECT id, title FROM $table_name");
}

// دریافت دوره‌های تکرار برای یک سرویس
function crm_get_repeat_intervals($service_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crm_services_management';
    $result = $wpdb->get_var($wpdb->prepare("SELECT repeat_intervals FROM $table_name WHERE service_id = %d", $service_id));
    return $result ? implode(', ', json_decode($result, true)) : 'بدون دوره';
}

// دریافت سرویس‌های مکمل برای یک سرویس
function crm_get_complementary_services($service_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crm_services_management';
    $result = $wpdb->get_var($wpdb->prepare("SELECT complementary_services FROM $table_name WHERE service_id = %d", $service_id));
    return $result ? implode(', ', json_decode($result, true)) : 'بدون سرویس مکمل';
}

// رندر کردن صفحه گزارش‌ها
function crm_render_reports_page() {
    ?>
    <div class="wrap">
        <h1>گزارش‌ها</h1>
        <p>این صفحه برای نمایش گزارش‌های مربوط به مشتریان است.</p>
    </div>
    <?php
}

// فراخوانی صفحه تنظیمات از فایل crm-admin-settings.php
function crm_render_settings_page() {
    include_once plugin_dir_path(__FILE__) . 'crm-admin-settings.php';
}
?>