<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX class
 */
class WC_Guest_Email_Validation_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_wc_gev_check_email', array($this, 'check_email_exists'));
        add_action('wp_ajax_nopriv_wc_gev_check_email', array($this, 'check_email_exists'));
    }
    
    /**
     * AJAX handler for email checking
     */
    public function check_email_exists() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'wc_gev_check_email')) {
            wp_send_json_error(__('Invalid nonce.', WC_GEV_TEXT_DOMAIN));
        }
        
        if (WC_Guest_Email_Validation::get_option('enable_validation') !== 'yes') {
            wp_send_json_success(array('exists' => false));
        }
        
        if (is_user_logged_in()) {
            wp_send_json_success(array('exists' => false));
        }
        
        $email = sanitize_email($_POST['email'] ?? '');
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(__('Invalid email address.', WC_GEV_TEXT_DOMAIN));
        }
        
        $validator = new WC_Guest_Email_Validation_Validator();
        $exists = $validator->email_exists($email);
        
        wp_send_json_success(array(
            'exists' => $exists,
            'message' => $exists ? $this->get_ajax_error_message() : '',
        ));
    }
    
    /**
     * Get AJAX error message
     *
     * @return string
     */
    private function get_ajax_error_message() {
        $custom_message = WC_Guest_Email_Validation::get_option('custom_error_message');
        
        if (!empty($custom_message)) {
            return wp_kses_post($custom_message);
        }
        
        return __('This email is already registered. Please log in.', WC_GEV_TEXT_DOMAIN);
    }
}