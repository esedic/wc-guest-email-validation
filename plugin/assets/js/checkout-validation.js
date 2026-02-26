/**
 * WooCommerce Guest Email Validation - Frontend Script
 * Updated for better Classic Checkout support
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
            this.initialized = true;
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;
            
            // For WooCommerce Blocks (new checkout)
            $(document).on('input blur', 'input[name="email"], input[id*="email"], input[type="email"]', function() {
                // Skip if it's the classic checkout billing email (handled separately)
                if ($(this).attr('id') === 'billing_email') {
                    return;
                }
                self.handleEmailInput($(this));
            });
            
            // For classic checkout - more specific targeting
            if ($('form.woocommerce-checkout').length || $('.woocommerce-checkout').length) {
                // Handle direct input on billing email
                $(document).on('input blur', '#billing_email', function() {
                    self.handleEmailInput($(this));
                });
                
                // Also handle checkout form updates
                $(document.body).on('updated_checkout', function() {
                    // Re-bind events after checkout update
                    setTimeout(function() {
                        $('#billing_email').off('input.wc_gev blur.wc_gev').on('input.wc_gev blur.wc_gev', function() {
                            self.handleEmailInput($(this));
                        });
                    }, 100);
                });
            }
            
            // Handle checkout updates and errors
            $(document.body).on('checkout_error updated_checkout update_checkout', function() {
                // Don't clear errors immediately on update_checkout to prevent flickering
                if (arguments[0].type !== 'update_checkout') {
                    self.clearAllErrors();
                }
            });
            
            // Clear errors when user starts typing after an error
            $(document).on('input', '#billing_email', function() {
                var $input = $(this);
                if ($input.hasClass('wc-gev-error')) {
                    // Small delay to allow user to see they're fixing the issue
                    setTimeout(function() {
                        self.clearErrorForInput($input);
                    }, 100);
                }
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
            
            // Validate email format first
            if (!email || !this.isValidEmail(email)) {
                this.clearErrorForInput(emailInput);
                return;
            }
            
            // Don't check if user is logged in (client-side check)
            if (this.isUserLoggedIn()) {
                return;
            }
            
            // Debounce the check - longer delay for classic checkout for better UX
            var delay = this.isBlockCheckout() ? 800 : 1200;
            this.emailCheckTimeout = setTimeout(function() {
                self.checkEmailExists(email, emailInput);
            }, delay);
        },
        
        /**
         * Check if email exists via AJAX
         */
        checkEmailExists: function(email, inputElement) {
            var self = this;
            
            // Add loading state
            inputElement.addClass('wc-gev-checking');
            
            $.ajax({
                url: wc_gev_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wc_gev_check_email',
                    email: email,
                    nonce: wc_gev_ajax.nonce
                },
                success: function(response) {
                    inputElement.removeClass('wc-gev-checking');
                    
                    if (response.success && response.data.exists) {
                        self.showEmailError(inputElement, response.data.message);
                        
                        // For classic checkout, also prevent form submission
                        if (!self.isBlockCheckout()) {
                            self.blockCheckoutSubmission(true);
                        }
                    } else {
                        // Clear any existing errors
                        self.clearErrorForInput(inputElement);
                        if (!self.isBlockCheckout()) {
                            self.blockCheckoutSubmission(false);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    inputElement.removeClass('wc-gev-checking');
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
            
            // Clear any existing errors first
            this.clearErrorForInput(inputElement);
            
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
                // Classic checkout - more robust error placement
                errorHtml = '<div class="' + errorClass + ' woocommerce-error" style="color: #e2401c; font-size: 14px; margin-top: 8px; margin-bottom: 16px; line-height: 1.4; border: 1px solid #e2401c; padding: 10px; background: #ffeaea; border-radius: 4px;">' + 
                           '<strong>Email Error:</strong> ' + message + 
                           '</div>';
                
                // Try to find the best place to insert the error
                var parentRow = inputElement.closest('.form-row, .woocommerce-billing-fields__field-wrapper');
                if (parentRow.length) {
                    parentRow.after(errorHtml);
                } else {
                    inputElement.after(errorHtml);
                }
            }
            
            // Add visual indication to input
            inputElement.addClass('wc-gev-error');
            inputElement.css({
                'border-color': '#e2401c',
                'box-shadow': '0 0 0 1px #e2401c'
            });
        },
        
        /**
         * Clear error for specific input
         */
        clearErrorForInput: function(inputElement) {
            inputElement.removeClass('wc-gev-error wc-gev-checking');
            inputElement.css({
                'border-color': '',
                'box-shadow': ''
            });
            
            // Remove error messages - more thorough cleanup
            $('.wc-gev-email-error').remove();
            
            // Also remove from different possible containers
            if (this.isBlockCheckout()) {
                inputElement.closest('.wc-block-components-text-input, .wc-block-checkout__contact-fields')
                          .find('.wc-gev-email-error').remove();
            }
        },
        
        /**
         * Clear all errors
         */
        clearAllErrors: function() {
            $('.wc-gev-email-error').remove();
            $('.wc-gev-error').removeClass('wc-gev-error wc-gev-checking').css({
                'border-color': '',
                'box-shadow': ''
            });
            
            // Unblock checkout if it was blocked
            if (!this.isBlockCheckout()) {
                this.blockCheckoutSubmission(false);
            }
        },
        
        /**
         * Block/unblock checkout submission for classic checkout
         */
        blockCheckoutSubmission: function(block) {
            var $checkoutForm = $('form.checkout, form.woocommerce-checkout');
            
            if (block) {
                $checkoutForm.addClass('wc-gev-blocked');
                // Store original submit handler if not already stored
                if (!$checkoutForm.data('wc-gev-original-submit')) {
                    $checkoutForm.data('wc-gev-original-submit', true);
                }
            } else {
                $checkoutForm.removeClass('wc-gev-blocked');
            }
        },
        
        /**
         * Check if it's block checkout
         */
        isBlockCheckout: function() {
            return $('.wc-block-checkout').length > 0 || 
                   $('.wp-block-woocommerce-checkout').length > 0 ||
                   $('body').hasClass('woocommerce-checkout-block');
        },
        
        /**
         * Check if user is logged in (client-side indicators)
         */
        isUserLoggedIn: function() {
            return $('body').hasClass('logged-in') || 
                   $('.woocommerce-account').length > 0 ||
                   $('#billing_email').length === 0; // No billing email field usually means logged in
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
        
        // Prevent form submission if there are email validation errors
        $(document.body).on('submit', 'form.checkout, form.woocommerce-checkout', function(e) {
            if ($(this).hasClass('wc-gev-blocked') || $('.wc-gev-email-error').length > 0) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    /**
     * Re-initialize for dynamic content
     */
    $(document.body).on('updated_checkout checkout_error', function() {
        setTimeout(function() {
            if (!WC_GEV_Checkout.initialized) {
                WC_GEV_Checkout.init();
            }
        }, 100);
    });
    
})(jQuery);