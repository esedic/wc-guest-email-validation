<?php
/**
 * Admin Settings Template
 * 
 * This file should be saved as: templates/admin-settings.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$enable_validation = isset($settings['enable_validation']) ? $settings['enable_validation'] : 'yes';
$show_login_link = isset($settings['show_login_link']) ? $settings['show_login_link'] : 'yes';
$enable_realtime_check = isset($settings['enable_realtime_check']) ? $settings['enable_realtime_check'] : 'yes';
$custom_error_message = isset($settings['custom_error_message']) ? $settings['custom_error_message'] : '';
?>

<div class="wrap">
    <h1><?php echo esc_html__('WooCommerce Guest Email Validation Settings', WC_GEV_TEXT_DOMAIN); ?></h1>
    
    <div class="wc-gev-admin-wrapper">
        <div class="wc-gev-main-content">
            <form method="post" action="">
                <?php wp_nonce_field('wc_gev_save_settings', 'wc_gev_nonce'); ?>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="enable_validation">
                                    <?php echo esc_html__('Enable Validation', WC_GEV_TEXT_DOMAIN); ?>
                                </label>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="enable_validation">
                                        <input 
                                            type="checkbox" 
                                            id="enable_validation" 
                                            name="enable_validation" 
                                            value="yes" 
                                            <?php checked($enable_validation, 'yes'); ?>
                                        />
                                        <?php echo esc_html__('Prevent guest checkout with existing customer emails', WC_GEV_TEXT_DOMAIN); ?>
                                    </label>
                                </fieldset>
                                <p class="description">
                                    <?php echo esc_html__('When enabled, guests will not be able to place orders using email addresses that belong to existing customers.', WC_GEV_TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="show_login_link">
                                    <?php echo esc_html__('Show Login Link', WC_GEV_TEXT_DOMAIN); ?>
                                </label>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="show_login_link">
                                        <input 
                                            type="checkbox" 
                                            id="show_login_link" 
                                            name="show_login_link" 
                                            value="yes" 
                                            <?php checked($show_login_link, 'yes'); ?>
                                        />
                                        <?php echo esc_html__('Include login link in error message', WC_GEV_TEXT_DOMAIN); ?>
                                    </label>
                                </fieldset>
                                <p class="description">
                                    <?php echo esc_html__('When enabled, the error message will include a link to the login page.', WC_GEV_TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="enable_realtime_check">
                                    <?php echo esc_html__('Real-time Validation', WC_GEV_TEXT_DOMAIN); ?>
                                </label>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="enable_realtime_check">
                                        <input 
                                            type="checkbox" 
                                            id="enable_realtime_check" 
                                            name="enable_realtime_check" 
                                            value="yes" 
                                            <?php checked($enable_realtime_check, 'yes'); ?>
                                        />
                                        <?php echo esc_html__('Enable real-time email validation', WC_GEV_TEXT_DOMAIN); ?>
                                    </label>
                                </fieldset>
                                <p class="description">
                                    <?php echo esc_html__('When enabled, email addresses will be validated in real-time as the user types (with debouncing).', WC_GEV_TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="custom_error_message">
                                    <?php echo esc_html__('Custom Error Message', WC_GEV_TEXT_DOMAIN); ?>
                                </label>
                            </th>
                            <td>
                                <textarea 
                                    id="custom_error_message" 
                                    name="custom_error_message" 
                                    rows="3" 
                                    cols="50" 
                                    class="large-text"
                                    placeholder="<?php echo esc_attr__('Leave empty to use default message', WC_GEV_TEXT_DOMAIN); ?>"
                                ><?php echo esc_textarea($custom_error_message); ?></textarea>
                                <p class="description">
                                    <?php echo esc_html__('Custom error message to display when validation fails. Leave empty to use the default message. HTML is allowed.', WC_GEV_TEXT_DOMAIN); ?>
                                </p>
                                <p class="description">
                                    <strong><?php echo esc_html__('Default message:', WC_GEV_TEXT_DOMAIN); ?></strong><br>
                                    <?php echo esc_html__('An account with this email address already exists. Please log in to continue with your order.', WC_GEV_TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php submit_button(__('Save Settings', WC_GEV_TEXT_DOMAIN)); ?>
            </form>
        </div>
        
        <div class="wc-gev-sidebar">
            <div class="postbox">
                <h3 class="hndle"><?php echo esc_html__('Plugin Information', WC_GEV_TEXT_DOMAIN); ?></h3>
                <div class="inside">
                    <p><strong><?php echo esc_html__('Version:', WC_GEV_TEXT_DOMAIN); ?></strong> <?php echo WC_GEV_VERSION; ?></p>
                    <p><strong><?php echo esc_html__('Compatibility:', WC_GEV_TEXT_DOMAIN); ?></strong></p>
                    <ul>
                        <li>✓ <?php echo esc_html__('WooCommerce Block Checkout', WC_GEV_TEXT_DOMAIN); ?></li>
                        <li>✓ <?php echo esc_html__('Classic Checkout', WC_GEV_TEXT_DOMAIN); ?></li>
                        <li>✓ <?php echo esc_html__('Multilingual Sites', WC_GEV_TEXT_DOMAIN); ?></li>
                        <li>✓ <?php echo esc_html__('HPOS Compatible', WC_GEV_TEXT_DOMAIN); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="postbox">
                <h3 class="hndle"><?php echo esc_html__('How It Works', WC_GEV_TEXT_DOMAIN); ?></h3>
                <div class="inside">
                    <ol>
                        <li><?php echo esc_html__('Guest user enters email at checkout', WC_GEV_TEXT_DOMAIN); ?></li>
                        <li><?php echo esc_html__('Plugin checks if email belongs to existing customer', WC_GEV_TEXT_DOMAIN); ?></li>
                        <li><?php echo esc_html__('If match found, checkout is prevented', WC_GEV_TEXT_DOMAIN); ?></li>
                        <li><?php echo esc_html__('User is prompted to log in instead', WC_GEV_TEXT_DOMAIN); ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wc-gev-admin-wrapper {
    display: flex;
    gap: 20px;
    max-width: 1200px;
}

.wc-gev-main-content {
    flex: 2;
}

.wc-gev-sidebar {
    flex: 1;
    max-width: 300px;
}

.wc-gev-sidebar .postbox {
    margin-bottom: 20px;
}

.wc-gev-sidebar .postbox h3 {
    padding: 8px 12px;
    margin: 0;
    background: #f1f1f1;
    border-bottom: 1px solid #dfdfdf;
}

.wc-gev-sidebar .postbox .inside {
    padding: 12px;
}

.wc-gev-sidebar ul {
    margin: 0;
    padding-left: 20px;
}

.wc-gev-sidebar ol {
    margin: 0;
    padding-left: 20px;
}

@media (max-width: 782px) {
    .wc-gev-admin-wrapper {
        flex-direction: column;
    }
    
    .wc-gev-sidebar {
        max-width: none;
    }
}
</style>