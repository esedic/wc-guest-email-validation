<?php
/**
 * Plugin Name: WooCommerce Guest Email Validation
 * Plugin URI: https://spletodrom.si/
 * Description: Prevents guest checkout when email address already belongs to an existing customer.
 * Version: 1.0.0
 * Author: Elvis Sedić
 * Author URI: https://spletodrom.si/
 * Text Domain: wc-guest-email-validation
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_GEV_VERSION', '1.0.0');
define('WC_GEV_PLUGIN_FILE', __FILE__);
define('WC_GEV_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WC_GEV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_GEV_TEXT_DOMAIN', 'wc-guest-email-validation');

/**
 * Main plugin class
 */
class WC_Guest_Email_Validation {
    
    /**
     * Single instance of the class
     *
     * @var WC_Guest_Email_Validation
     */
    private static $instance = null;
    
    /**
     * Admin class instance
     *
     * @var WC_Guest_Email_Validation_Admin
     */
    public $admin;
    
    /**
     * Frontend class instance
     *
     * @var WC_Guest_Email_Validation_Frontend
     */
    public $frontend;
    
    /**
     * AJAX class instance
     *
     * @var WC_Guest_Email_Validation_Ajax
     */
    public $ajax;
    
    /**
     * Get single instance
     *
     * @return WC_Guest_Email_Validation
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        
        // Check if WooCommerce is active
        add_action('admin_init', array($this, 'check_woocommerce_dependency'));
        
        // Plugin activation/deactivation hooks
        register_activation_hook(WC_GEV_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WC_GEV_PLUGIN_FILE, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        if (!$this->is_woocommerce_active()) {
            return;
        }
        
        $this->load_dependencies();
        $this->init_classes();
    }
    
    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            WC_GEV_TEXT_DOMAIN,
            false,
            dirname(plugin_basename(WC_GEV_PLUGIN_FILE)) . '/languages/'
        );
    }
    
    /**
     * Check if WooCommerce is active
     */
    public function check_woocommerce_dependency() {
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            deactivate_plugins(plugin_basename(WC_GEV_PLUGIN_FILE));
        }
    }
    
    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo sprintf(
            /* translators: %s: WooCommerce plugin name */
            esc_html__('WooCommerce Guest Email Validation requires %s to be installed and active.', WC_GEV_TEXT_DOMAIN),
            '<strong>' . esc_html__('WooCommerce', WC_GEV_TEXT_DOMAIN) . '</strong>'
        );
        echo '</p></div>';
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once WC_GEV_PLUGIN_PATH . 'includes/class-wc-guest-email-validation-admin.php';
        require_once WC_GEV_PLUGIN_PATH . 'includes/class-wc-guest-email-validation-frontend.php';
        require_once WC_GEV_PLUGIN_PATH . 'includes/class-wc-guest-email-validation-ajax.php';
        require_once WC_GEV_PLUGIN_PATH . 'includes/class-wc-guest-email-validation-validator.php';
    }
    
    /**
     * Initialize classes
     */
    private function init_classes() {
        $this->admin = new WC_Guest_Email_Validation_Admin();
        $this->frontend = new WC_Guest_Email_Validation_Frontend();
        $this->ajax = new WC_Guest_Email_Validation_Ajax();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_options = array(
            'enable_validation' => 'yes',
            'show_login_link' => 'yes',
            'enable_realtime_check' => 'yes',
            'custom_error_message' => '',
        );
        
        add_option('wc_gev_settings', $default_options);
        
        // Create plugin tables if needed
        $this->create_tables();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }
    
    /**
     * Create plugin tables
     */
    private function create_tables() {
        // Add any custom tables if needed in future versions
    }
    
    /**
     * Get plugin option
     *
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function get_option($key, $default = '') {
        $options = get_option('wc_gev_settings', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    /**
     * Update plugin option
     *
     * @param string $key Option key
     * @param mixed $value Option value
     */
    public static function update_option($key, $value) {
        $options = get_option('wc_gev_settings', array());
        $options[$key] = $value;
        update_option('wc_gev_settings', $options);
    }
}

// Initialize the plugin
function wc_guest_email_validation() {
    return WC_Guest_Email_Validation::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'wc_guest_email_validation', 20);

// HPOS compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', WC_GEV_PLUGIN_FILE, true);
    }
});