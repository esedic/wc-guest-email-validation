<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validator class
 */
class WC_Guest_Email_Validation_Validator {
    
    /**
     * Check if email exists for any user
     *
     * @param string $email
     * @return bool
     */
    public function email_exists($email) {
        if (empty($email) || !is_email($email)) {
            return false;
        }
        
        $user = get_user_by('email', sanitize_email($email));
        return (bool) $user;
    }
    
    /**
     * Validate email format
     *
     * @param string $email
     * @return bool
     */
    public function is_valid_email($email) {
        return is_email($email);
    }
}