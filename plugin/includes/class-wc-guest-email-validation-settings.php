<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings class
 */
class WC_Guest_Email_Validation_Settings extends WC_Settings_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'guest_email_validation';
        $this->label = __('Email Validation', WC_GEV_TEXT_DOMAIN);
        
        parent::__construct();
    }
    
    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {
        $settings = array(
            array(
                'title' => __('Guest Email Validation Settings', WC_GEV_TEXT_DOMAIN),
                'type'  => 'title',
                'desc'  => __('Configure how guest email validation works on your checkout page.', WC_GEV_TEXT_DOMAIN),
                'id'    => 'wc_gev_settings_title'
            ),
            
            array(
                'title'   => __('Enable Validation', WC_GEV_TEXT_DOMAIN),
                'desc'    => __('Prevent guest checkout with existing customer emails', WC_GEV_TEXT_DOMAIN),
                'id'      => 'wc_gev_enable_validation',
                'default' => 'yes',
                'type'    => 'checkbox',
                'desc_tip' => __('When enabled, guests will not be able to place orders using email addresses that belong to existing customers.', WC_GEV_TEXT_DOMAIN),
            ),
            
            array(
                'title'   => __('Show Login Link', WC_GEV_TEXT_DOMAIN),
                'desc'    => __('Include login link in error message', WC_GEV_TEXT_DOMAIN),
                'id'      => 'wc_gev_show_login_link',
                'default' => 'yes',
                'type'    => 'checkbox',
                'desc_tip' => __('When enabled, the error message will include a link to the login page.', WC_GEV_TEXT_DOMAIN),
            ),
            
            array(
                'title'   => __('Real-time Validation', WC_GEV_TEXT_DOMAIN),
                'desc'    => __('Enable real-time email validation', WC_GEV_TEXT_DOMAIN),
                'id'      => 'wc_gev_enable_realtime_check',
                'default' => 'yes',
                'type'    => 'checkbox',
                'desc_tip' => __('When enabled, email addresses will be validated in real-time as the user types (with debouncing).', WC_GEV_TEXT_DOMAIN),
            ),
            
            array(
                'title'    => __('Custom Error Message', WC_GEV_TEXT_DOMAIN),
                'desc'     => __('Custom error message to display when validation fails. Leave empty to use the default message. HTML is allowed.', WC_GEV_TEXT_DOMAIN),
                'id'       => 'wc_gev_custom_error_message',
                'type'     => 'textarea',
                'css'      => 'width: 100%; height: 80px;',
                'placeholder' => __('Leave empty to use default message', WC_GEV_TEXT_DOMAIN),
                'desc_tip' => true,
            ),
            
            array(
                'type' => 'sectionend',
                'id'   => 'wc_gev_settings_end'
            ),
            
            // Advanced Settings Section
            array(
                'title' => __('Advanced Settings', WC_GEV_TEXT_DOMAIN),
                'type'  => 'title',
                'desc'  => __('Advanced configuration options for developers and power users.', WC_GEV_TEXT_DOMAIN),
                'id'    => 'wc_gev_advanced_settings_title'
            ),
            
            array(
                'title'   => __('Debug Mode', WC_GEV_TEXT_DOMAIN),
                'desc'    => __('Enable debug logging', WC_GEV_TEXT_DOMAIN),
                'id'      => 'wc_gev_debug_mode',
                'default' => 'no',
                'type'    => 'checkbox',
                'desc_tip' => __('When enabled, validation events will be logged for debugging purposes.', WC_GEV_TEXT_DOMAIN),
            ),
            
            array(
                'title'    => __('AJAX Timeout', WC_GEV_TEXT_DOMAIN),
                'desc'     => __('Timeout in milliseconds for real-time validation requests', WC_GEV_TEXT_DOMAIN),
                'id'       => 'wc_gev_ajax_timeout',
                'type'     => 'number',
                'default'  => '5000',
                'custom_attributes' => array(
                    'min'  => 1000,
                    'max'  => 30000,
                    'step' => 500,
                ),
                'desc_tip' => true,
            ),
            
            array(
                'title'    => __('Debounce Delay', WC_GEV_TEXT_DOMAIN),
                'desc'     => __('Delay in milliseconds before checking email (prevents too many requests)', WC_GEV_TEXT_DOMAIN),
                'id'       => 'wc_gev_debounce_delay',
                'type'     => 'number',
                'default'  => '800',
                'custom_attributes' => array(
                    'min'  => 100,
                    'max'  => 5000,
                    'step' => 100,
                ),
                'desc_tip' => true,
            ),
            
            array(
                'type' => 'sectionend',
                'id'   => 'wc_gev_advanced_settings_end'
            ),
        );
        
        return apply_filters('wc_gev_settings', $settings);
    }
}

return new WC_Guest_Email_Validation_Settings();