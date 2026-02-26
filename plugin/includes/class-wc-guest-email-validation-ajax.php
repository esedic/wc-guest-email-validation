<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX class - Updated for better classic checkout support
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
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'wc_gev_check_email')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', WC_GEV_TEXT_DOMAIN),
                'code' => 'invalid_nonce'
            ));
        }
        
        // Check if validation is enabled
        if (WC_Guest_Email_Validation::get_option('enable_validation') !== 'yes') {
            wp_send_json_success(array('exists' => false));
        }
        
        // Check if user is logged in
        if (is_user_logged_in()) {
            wp_send_json_success(array('exists' => false));
        }
        
        // Validate and sanitize email
        $email = sanitize_email($_POST['email'] ?? '');
        
        if (empty($email)) {
            wp_send_json_error(array(
                'message' => __('Email address is required.', WC_GEV_TEXT_DOMAIN),
                'code' => 'empty_email'
            ));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array(
                'message' => __('Please enter a valid email address.', WC_GEV_TEXT_DOMAIN),
                'code' => 'invalid_email_format'
            ));
        }
        
        // Check if email exists
        $validator = new WC_Guest_Email_Validation_Validator();
        $exists = $validator->email_exists($email);
        
        // Get user details if email exists (for better error messages)
        $user_data = null;
        if ($exists) {
            $user = get_user_by('email', $email);
            if ($user) {
                $user_data = array(
                    'user_login' => $user->user_login,
                    'display_name' => $user->display_name,
                    'user_registered' => $user->user_registered
                );
            }
        }
        
        wp_send_json_success(array(
            'exists' => $exists,
            'message' => $exists ? $this->get_ajax_error_message($user_data) : '',
            'email' => $email,
            'user_data' => $user_data,
            'show_login_link' => WC_Guest_Email_Validation::get_option('show_login_link') === 'yes'
        ));
    }
    
    /**
     * Get AJAX error message
     *
     * @param array|null $user_data User data if available
     * @return string
     */
    private function get_ajax_error_message($user_data = null) {
        $custom_message = WC_Guest_Email_Validation::get_option('custom_error_message');
        
        if (!empty($custom_message)) {
            return wp_kses_post($custom_message);
        }
        
        // Different message for AJAX (shorter, more direct)
        if (WC_Guest_Email_Validation::get_option('show_login_link') === 'yes') {
            $login_url = wp_login_url(wc_get_checkout_url());
            return sprintf(
                /* translators: %s: login URL */
                __('This email is already registered. <a href="%s" target="_blank">Please log in</a> to continue.', WC_GEV_TEXT_DOMAIN),
                esc_url($login_url)
            );
        }
        
        return __('This email address is already registered. Please log in or use a different email address.', WC_GEV_TEXT_DOMAIN);
    }
}