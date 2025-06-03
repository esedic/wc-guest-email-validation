/**
 * WooCommerce Guest Email Validation - Frontend Script
 * 
 * This file should be saved as: assets/js/checkout-validation.js
 */

(function($) {
    'use strict';
    
    var WC_GEV_Checkout = {
        
        /**
         * Initialize
         */
        init: function() {
            this.emailCheckTimeout = null;
            this.bindEvents();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;
            
            // For WooCommerce Blocks (new checkout)
            $(document).on('input blur', 'input[name="email"], input[id*="email"], input[type="email"]', function() {
                self.handleEmailInput($(this));
            });
            
            // For classic checkout
            if ($('form.woocommerce-checkout').length) {
                $('form.woocommerce-checkout').on('input blur', '#billing_email', function() {
                    self.handleEmailInput($(this));
                });
            }
            
            // Handle checkout updates (for block checkout)
            $(document.body).on('checkout_error updated_checkout', function() {
                self.clearAllErrors();
            });
        },
        
        /**
         * Handle email input
         */
        handleEmailInput: function(emailInput) {
            var self = this;
            var email = emailInput.val().trim();
            
            // Clear previous timeout
            clearTimeout(this.emailCheckTimeout);
            
            // Remove previous error messages
            this.clearErrorForInput(emailInput);
            
            // Validate email format first
            if (!email || !this.isValidEmail(email)) {
                return;
            }
            
            // Debounce the check
            this.emailCheckTimeout = setTimeout(function() {
                self.checkEmailExists(email, emailInput);
            }, 800);
        },
        
        /**
         * Check if email exists via AJAX
         */
        checkEmailExists: function(email, inputElement) {
            var self = this;
            
            $.ajax({
                url: wc_gev_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wc_gev_check_email',
                    email: email,
                    nonce: wc_gev_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.exists) {
                        self.showEmailError(inputElement, response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('WC Guest Email Validation: AJAX request failed', error);
                }
            });
        },
        
        /**
         * Show email error
         */
        showEmailError: function(inputElement, message) {
            var errorClass = 'wc-gev-email-error';
            var errorHtml;
            
            // Check if it's block checkout or classic checkout
            if (this.isBlockCheckout()) {
                errorHtml = '<div class="' + errorClass + ' wc-block-components-validation-error" style="color: #e2401c; font-size: 14px; margin-top: 4px; line-height: 1.4;">' + 
                           '<span>' + message + '</span>' + 
                           '</div>';
                
                // Find the appropriate container for block checkout
                var container = inputElement.closest('.wc-block-components-text-input, .wc-block-checkout__contact-fields');
                if (container.length) {
                    container.append(errorHtml);
                } else {
                    inputElement.after(errorHtml);
                }
            } else {
                errorHtml = '<div class="' + errorClass + '" style="color: #e2401c; font-size: 14px; margin-top: 4px; line-height: 1.4;">' + 
                           message + 
                           '</div>';
                inputElement.after(errorHtml);
            }
            
            // Add visual indication to input
            inputElement.addClass('wc-gev-error');
            inputElement.css('border-color', '#e2401c');
        },
        
        /**
         * Clear error for specific input
         */
        clearErrorForInput: function(inputElement) {
            inputElement.removeClass('wc-gev-error');
            inputElement.css('border-color', '');
            
            // Remove error messages
            if (this.isBlockCheckout()) {
                inputElement.closest('.wc-block-components-text-input, .wc-block-checkout__contact-fields')
                          .find('.wc-gev-email-error').remove();
            } else {
                inputElement.siblings('.wc-gev-email-error').remove();
            }
        },
        
        /**
         * Clear all errors
         */
        clearAllErrors: function() {
            $('.wc-gev-email-error').remove();
            $('.wc-gev-error').removeClass('wc-gev-error').css('border-color', '');
        },
        
        /**
         * Check if it's block checkout
         */
        isBlockCheckout: function() {
            return $('.wc-block-checkout').length > 0 || $('.wp-block-woocommerce-checkout').length > 0;
        },
        
        /**
         * Validate email format
         */
        isValidEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    };
    
    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        WC_GEV_Checkout.init();
    });
    
    /**
     * Re-initialize for dynamic content (block checkout)
     */
    $(document.body).on('updated_checkout checkout_error', function() {
        setTimeout(function() {
            WC_GEV_Checkout.init();
        }, 100);
    });
    
})(jQuery);