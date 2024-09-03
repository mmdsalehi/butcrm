<?php
/*
Plugin Name: Beauty CRM Plugin
Description: CRM for beauty salons to manage repeat services and complementary services. Compatible with Bookly and Digits.
Version: 1.0
Author: َAtiGamNet
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/crm-admin.php';
include_once plugin_dir_path(__FILE__) . 'includes/crm-database.php';

// Activation Hook: Initialize Database
register_activation_hook(__FILE__, 'crm_initialize_database');
function crm_initialize_database() {
    crm_create_tables();
}

// Add Admin Menu
//add_action('admin_menu', 'crm_add_admin_pages');
function crm_add_admin_pages() {
    add_menu_page(
        'CRM Management',
        'Beauty CRM',
        'manage_options',
        'beauty-crm',
        'crm_admin_page',
        'dashicons-admin-generic',
        6
    );
}
?>
