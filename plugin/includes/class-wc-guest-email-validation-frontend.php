<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend class
 */
class WC_Guest_Email_Validation_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'validate_guest_email_store_api'), 10, 2);
        add_action('woocommerce_after_checkout_validation', array($this, 'validate_guest_email_checkout'), 10, 2);
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        if (!is_checkout() || WC_Guest_Email_Validation::get_option('enable_realtime_check') !== 'yes') {
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
        ));
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
            $errors->add('existing_email_error', $this->get_error_message());
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