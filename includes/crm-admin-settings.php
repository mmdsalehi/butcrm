<?php
// افزودن صفحه تنظیمات به منوی مدیریت وردپرس
function crm_add_settings_page() {
    add_options_page(
        'تنظیمات CRM',
        'تنظیمات CRM',
        'manage_options',
        'crm-settings',
        'crm_render_settings_page'
    );
}
//add_action('admin_menu', 'crm_add_settings_page');

// رندر کردن صفحه تنظیمات
function crm_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>تنظیمات CRM</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('crm_plugin_settings_group');
            do_settings_sections('crm-settings');
            submit_button();
            ?>
        </form>
        <form method="post" action="">
            <h2>پاک کردن داده‌های افزونه</h2>
            <p>با کلیک بر روی دکمه زیر، تمامی داده‌های افزونه از پایگاه داده پاک خواهد شد.</p>
            <input type="hidden" name="crm_clear_data" value="1">
            <?php submit_button('پاک کردن اطلاعات', 'delete', 'crm_clear_data_button'); ?>
        </form>
    </div>
    <?php
}

// ثبت تنظیمات افزونه
function crm_register_settings() {
    register_setting('crm_plugin_settings_group', 'crm_plugin_settings');

    add_settings_section(
        'crm_main_settings_section',
        'تنظیمات اصلی',
        'crm_main_settings_section_callback',
        'crm-settings'
    );

    add_settings_field(
        'delete_data_on_uninstall',
        'حذف داده‌ها در زمان حذف افزونه',
        'crm_delete_data_on_uninstall_render',
        'crm-settings',
        'crm_main_settings_section'
    );
}
add_action('admin_init', 'crm_register_settings');

function crm_main_settings_section_callback() {
    echo 'تنظیمات اصلی افزونه را در اینجا پیکربندی کنید.';
}

function crm_delete_data_on_uninstall_render() {
    $options = get_option('crm_plugin_settings');
    ?>
    <input type="checkbox" name="crm_plugin_settings[delete_data_on_uninstall]" value="1" <?php checked(1, isset($options['delete_data_on_uninstall']) ? $options['delete_data_on_uninstall'] : 0); ?>>
    <label for="delete_data_on_uninstall">اگر فعال باشد، هنگام حذف افزونه، تمام داده‌ها حذف خواهند شد.</label>
    <?php
}

// پردازش فرم پاک کردن داده‌ها
function crm_process_clear_data_form() {
    if (isset($_POST['crm_clear_data']) && $_POST['crm_clear_data'] == '1') {
        crm_clear_data();
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>تمامی داده‌های افزونه با موفقیت پاک شد.</p></div>';
        });
    }
}
add_action('admin_init', 'crm_process_clear_data_form');
?>
