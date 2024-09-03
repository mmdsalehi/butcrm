<?php
global $wpdb;

function crm_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // ایجاد جدول مدیریت سرویس‌ها
    $services_table = $wpdb->prefix . 'crm_services_management';
    $sql_services = "CREATE TABLE IF NOT EXISTS $services_table (
        id INT(11) NOT NULL AUTO_INCREMENT,
        service_name VARCHAR(255) NOT NULL,
        repeat_intervals JSON,
        complementary_services JSON,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // ایجاد جدول یادداشت‌ها
    $notes_table = $wpdb->prefix . 'crm_service_notes';
    $sql_notes = "CREATE TABLE IF NOT EXISTS $notes_table (
        id INT(11) NOT NULL AUTO_INCREMENT,
        service_id INT(11) NOT NULL,
        customer_id INT(11) NOT NULL,
        note_text TEXT NOT NULL,
        note_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        user_display_name VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // اجرای دستورات SQL برای ایجاد جداول
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_services);
    dbDelta($sql_notes);
}

// فراخوانی تابع crm_create_tables در زمان فعال‌سازی افزونه
register_activation_hook(__FILE__, 'crm_create_tables');
?>