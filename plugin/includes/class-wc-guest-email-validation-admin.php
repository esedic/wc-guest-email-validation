<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class
 */
class WC_Guest_Email_Validation_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_filter('woocommerce_get_settings_pages', array($this, 'add_settings_page'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Guest Email Validation', WC_GEV_TEXT_DOMAIN),
            __('Email Validation', WC_GEV_TEXT_DOMAIN),
            'manage_woocommerce',
            'wc-guest-email-validation',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('wc_gev_settings', 'wc_gev_settings');
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $settings = get_option('wc_gev_settings', array());
        include WC_GEV_PLUGIN_PATH . 'templates/admin-settings.php';
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!wp_verify_nonce($_POST['wc_gev_nonce'], 'wc_gev_save_settings')) {
            return;
        }
        
        $settings = array(
            'enable_validation' => isset($_POST['enable_validation']) ? 'yes' : 'no',
            'show_login_link' => isset($_POST['show_login_link']) ? 'yes' : 'no',
            'enable_realtime_check' => isset($_POST['enable_realtime_check']) ? 'yes' : 'no',
            'custom_error_message' => sanitize_textarea_field($_POST['custom_error_message']),
        );
        
        update_option('wc_gev_settings', $settings);
        
        echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully.', WC_GEV_TEXT_DOMAIN) . '</p></div>';
    }
    
    /**
     * Add WooCommerce settings page
     */
    public function add_settings_page($settings) {
        $settings[] = include WC_GEV_PLUGIN_PATH . 'includes/class-wc-guest-email-validation-settings.php';
        return $settings;
    }
}
