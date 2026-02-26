<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend class - Updated for better classic checkout support
 */
class WC_Guest_Email_Validation_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Block checkout validation (Store API)
        add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'validate_guest_email_store_api'), 10, 2);
        
        // Classic checkout validation - multiple hooks for better coverage
        add_action('woocommerce_after_checkout_validation', array($this, 'validate_guest_email_checkout'), 10, 2);
        add_action('woocommerce_checkout_process', array($this, 'validate_guest_email_checkout_process'));
        
        // Additional validation for edge cases
        add_action('woocommerce_checkout_order_processed', array($this, 'final_email_validation'), 5, 3);
        
        // Add checkout field validation classes
        add_filter('woocommerce_checkout_fields', array($this, 'add_checkout_field_classes'));
        
        // Handle AJAX checkout (some themes use custom AJAX)
        add_action('wp_ajax_woocommerce_checkout', array($this, 'validate_ajax_checkout'), 5);
        add_action('wp_ajax_nopriv_woocommerce_checkout', array($this, 'validate_ajax_checkout'), 5);
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (!is_checkout()) {
            return;
        }
        
        // Always enqueue CSS for better styling
        // wp_enqueue_style(
        //     'wc-gev-checkout-validation',
        //     WC_GEV_PLUGIN_URL . 'assets/css/checkout-validation.css',
        //     array(),
        //     WC_GEV_VERSION
        // );
        
        // Only enqueue JS if real-time checking is enabled
        if (WC_Guest_Email_Validation::get_option('enable_realtime_check') !== 'yes') {
            return;
        }
        
        wp_enqueue_script(
            'wc-gev-checkout-validation',
            WC_GEV_PLUGIN_URL . 'assets/js/checkout-validation.js',
            array('jquery'),
            WC_GEV_VERSION,
            true
        );
        
        wp_localize_script('wc-gev-checkout-validation', 'wc_gev_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc_gev_check_email'),
            'messages' => array(
                'email_exists' => $this->get_error_message(),
            ),
            'is_block_checkout' => $this->is_block_checkout(),
            'settings' => array(
                'enable_validation' => WC_Guest_Email_Validation::get_option('enable_validation'),
                'show_login_link' => WC_Guest_Email_Validation::get_option('show_login_link'),
                'enable_realtime_check' => WC_Guest_Email_Validation::get_option('enable_realtime_check'),
            ),
        ));
    }
    
    /**
     * Add validation classes to checkout fields
     */
    public function add_checkout_field_classes($fields) {
        if (isset($fields['billing']['billing_email'])) {
            $fields['billing']['billing_email']['class'][] = 'wc-gev-email-field';
            $fields['billing']['billing_email']['custom_attributes']['data-wc-gev-validate'] = 'true';
        }
        return $fields;
    }
    
    /**
     * Check if current page is using block checkout
     */
    private function is_block_checkout() {
        if (!is_checkout()) {
            return false;
        }
        
        // Check for block checkout indicators
        if (has_block('woocommerce/checkout') || 
            has_block('woocommerce/cart-checkout-block') ||
            get_option('woocommerce_feature_custom_order_tables_enabled') === 'yes') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Validate guest email for Store API (Block Checkout)
     *
     * @param WC_Order $order
     * @param WP_REST_Request $request
     * @throws Exception
     */
    public function validate_guest_email_store_api($order, $request) {
        if (WC_Guest_Email_Validation::get_option('enable_validation') !== 'yes') {
            return;
        }
        
        if (is_user_logged_in()) {
            return;
        }
        
        $billing_email = $request['billing_address']['email'] ?? '';
        
        if (empty($billing_email)) {
            return;
        }
        
        $validator = new WC_Guest_Email_Validation_Validator();
        if ($validator->email_exists($billing_email)) {
            throw new Exception($this->get_error_message());
        }
    }
    
    /**
     * Validate guest email for classic checkout
     *
     * @param array $data
     * @param WP_Error $errors
     */
    public function validate_guest_email_checkout($data, $errors) {
        if (WC_Guest_Email_Validation::get_option('enable_validation') !== 'yes') {
            return;
        }
        
        if (is_user_logged_in()) {
            return;
        }
        
        $billing_email = $data['billing_email'] ?? '';
        
        if (empty($billing_email)) {
            return;
        }
        
        $validator = new WC_Guest_Email_Validation_Validator();
        if ($validator->email_exists($billing_email)) {
            $errors->add('billing_email', $this->get_error_message());
        }
    }
    
    /**
     * Additional validation during checkout process
     */
    public function validate_guest_email_checkout_process() {
        if (WC_Guest_Email_Validation::get_option('enable_validation') !== 'yes') {
            return;
        }
        
        if (is_user_logged_in()) {
            return;
        }
        
        $billing_email = $_POST['billing_email'] ?? '';
        
        if (empty($billing_email)) {
            return;
        }
        
        $validator = new WC_Guest_Email_Validation_Validator();
        if ($validator->email_exists($billing_email)) {
            wc_add_notice($this->get_error_message(), 'error');
        }
    }
    
    /**
     * Final validation before order is fully processed
     * This is a safety net to catch any edge cases
     *
     * @param int $order_id
     * @param array $posted_data
     * @param WC_Order $order
     */
    public function final_email_validation($order_id, $posted_data, $order) {
        if (WC_Guest_Email_Validation::get_option('enable_validation') !== 'yes') {
            return;
        }
        
        if (is_user_logged_in()) {
            return;
        }
        
        $billing_email = $order->get_billing_email();
        
        if (empty($billing_email)) {
            return;
        }
        
        $validator = new WC_Guest_Email_Validation_Validator();
        if ($validator->email_exists($billing_email)) {
            // If we reach this point, something went wrong with earlier validations
            // Log the issue and prevent order completion
            error_log('WC Guest Email Validation: Email validation bypassed, stopping order: ' . $billing_email);
            
            // Cancel the order
            $order->update_status('cancelled', __('Order cancelled due to email validation failure.', WC_GEV_TEXT_DOMAIN));
            
            // Redirect back to checkout with error
            wc_add_notice($this->get_error_message(), 'error');
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }
    }
    
    /**
     * Handle AJAX checkout validation (for themes with custom AJAX)
     */
    public function validate_ajax_checkout() {
        if (WC_Guest_Email_Validation::get_option('enable_validation') !== 'yes') {
            return;
        }
        
        if (is_user_logged_in()) {
            return;
        }
        
        $billing_email = $_POST['billing_email'] ?? '';
        
        if (empty($billing_email)) {
            return;
        }
        
        $validator = new WC_Guest_Email_Validation_Validator();
        if ($validator->email_exists($billing_email)) {
            // Add error to WC notices
            wc_add_notice($this->get_error_message(), 'error');
            
            // Also add to the response if this is an AJAX request
            if (wp_doing_ajax()) {
                wp_send_json_error(array(
                    'message' => $this->get_error_message(),
                    'field' => 'billing_email'
                ));
            }
        }
    }
    
    /**
     * Get error message
     *
     * @return string
     */
    private function get_error_message() {
        $custom_message = WC_Guest_Email_Validation::get_option('custom_error_message');
        
        if (!empty($custom_message)) {
            return wp_kses_post($custom_message);
        }
        
        if (WC_Guest_Email_Validation::get_option('show_login_link') === 'yes') {
            return sprintf(
                /* translators: %s: login URL */
                __('An account with this email address already exists. Please <a href="%s">log in</a> to continue with your order.', WC_GEV_TEXT_DOMAIN),
                wp_login_url(wc_get_checkout_url())
            );
        }
        
        return __('An account with this email address already exists. Please log in to continue.', WC_GEV_TEXT_DOMAIN);
    }
}