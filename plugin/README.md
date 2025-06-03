# WooCommerce Guest Email Validation Plugin

A professional WordPress plugin that prevents guest checkout when the email address already belongs to an existing customer. Fully compatible with WooCommerce Block Checkout and multilingual sites.

## Features

- ✅ **Block Checkout Compatible** - Works with the new WooCommerce block-based checkout
- ✅ **Classic Checkout Support** - Also works with traditional WooCommerce checkout
- ✅ **Real-time Validation** - Optional AJAX-based email checking as users type
- ✅ **Multilingual Ready** - Full internationalization support with .pot file included
- ✅ **HPOS Compatible** - Works with WooCommerce High-Performance Order Storage
- ✅ **Professional Architecture** - Object-oriented code following WordPress best practices
- ✅ **Customizable Messages** - Admin can set custom error messages
- ✅ **Security Focused** - Proper nonce verification and data sanitization

## Installation

### Manual Installation

1. Download the plugin files
2. Create the following directory structure in your WordPress plugins folder:

```
wp-content/plugins/wc-guest-email-validation/
├── wc-guest-email-validation.php (main plugin file)
├── assets/
│   └── js/
│       └── checkout-validation.js
├── includes/
│   ├── class-wc-guest-email-validation-admin.php
│   ├── class-wc-guest-email-validation-frontend.php
│   ├── class-wc-guest-email-validation-ajax.php
│   ├── class-wc-guest-email-validation-validator.php
│   └── class-wc-guest-email-validation-settings.php
├── templates/
│   └── admin-settings.php
├── languages/
│   └── wc-guest-email-validation.pot
└── README.md
```

3. Upload all files to their respective directories
4. Activate the plugin through the WordPress admin panel

### Via WordPress Admin

1. Go to **Plugins > Add New**
2. Upload the plugin zip file
3. Activate the plugin

## Configuration

### Admin Settings

Navigate to **WooCommerce > Settings > Email Validation** or **WooCommerce > Email Validation** to configure:

#### Basic Settings
- **Enable Validation**: Turn the email validation on/off
- **Show Login Link**: Include a login link in error messages
- **Real-time Validation**: Enable AJAX checking as users type
- **Custom Error Message**: Override default error message

#### Advanced Settings
- **Debug Mode**: Enable logging for troubleshooting
- **AJAX Timeout**: Set timeout for validation requests
- **Debounce Delay**: Control how quickly validation triggers

### Default Settings

The plugin comes with sensible defaults:
- Validation: **Enabled**
- Login Link: **Enabled**
- Real-time Check: **Enabled**
- Debounce Delay: **800ms**

## How It Works

1. **Guest User Process**:
   - Guest enters email at checkout
   - Plugin checks if email belongs to existing customer
   - If match found, checkout is prevented
   - User sees error message with login prompt

2. **Logged-in User Process**:
   - Validation is bypassed for authenticated users
   - Normal checkout process continues

3. **Real-time Validation** (optional):
   - Email checked via AJAX as user types
   - Debounced to prevent excessive requests
   - Visual feedback provided immediately

## Multilingual Support

The plugin is fully internationalized and ready for translation:

1. **Included Languages**: English (default)
2. **Translation Ready**: Complete .pot file provided
3. **Compatible With**:
   - WPML
   - Polylang
   - WordPress native multilingual features

### Adding Translations

1. Use the provided `.pot` file in the `languages/` directory
2. Create `.po` and `.mo` files for your language
3. Place them in the `languages/` directory
4. Follow WordPress translation naming conventions

## Developer Information

### Hooks and Filters

#### Actions
- `wc_gev_email_validated` - Fired when email validation occurs
- `wc_gev_validation_failed` - Fired when validation fails

#### Filters
- `wc_gev_settings` - Modify plugin settings
- `wc_gev_error_message` - Customize error messages
- `wc_gev_bypass_validation` - Conditionally bypass validation

### Code Examples

#### Custom Error Message
```php
add_filter('wc_gev_error_message', function($message, $email) {
    return sprintf('Email %s is already registered. Please log in.', $email);
}, 10, 2);
```

#### Bypass Validation for Specific Cases
```php
add_filter('wc_gev_bypass_validation', function($bypass, $email) {
    // Skip validation for admin emails
    if (strpos($email, '@admin.com') !== false) {
        return true;
    }
    return $bypass;
}, 10, 2);
```

## Troubleshooting

### Common Issues

1. **Validation Not Working**
   - Check if plugin is activated
   - Verify WooCommerce is installed and active
   - Check settings are enabled

2. **JavaScript Errors**
   - Ensure jQuery is loaded
   - Check for theme conflicts
   - Enable debug mode for detailed logging

3. **AJAX Not Working**
   - Verify AJAX URL is correct
   - Check nonce verification
   - Look for server-side errors

### Debug Mode

Enable debug mode in settings to log validation events:
- Logs are written to WooCommerce logs
- Check **WooCommerce > Status > Logs**
- Look for files prefixed with `wc-guest-email-validation`

## Compatibility

### WordPress
- **Minimum**: WordPress 5.0+
- **Tested up to**: WordPress 6.5+
- **PHP**: 7.4+

### WooCommerce
- **Minimum**: WooCommerce 5.0+
- **Tested up to**: WooCommerce 8.5+
- **Block Checkout**: Full support
- **Classic Checkout**: Full support
- **HPOS**: Compatible

### Themes
- Works with any properly coded WooCommerce theme
- Tested with Storefront, Astra, OceanWP
- Custom checkout pages supported

## Performance

- **Lightweight**: Minimal impact on page load times
- **Optimized AJAX**: Debounced requests prevent server overload
- **Conditional Loading**: Scripts only load on checkout pages
- **Database Efficient**: Uses WordPress native user lookup functions

## Security

- **Nonce Verification**: All AJAX requests verified
- **Data Sanitization**: All input properly sanitized
- **SQL Injection**: Protected via WordPress functions
- **XSS Prevention**: All output escaped

## License

GPL v2 or later

## Support

For support and feature requests:
1. Check this README for common solutions
2. Enable debug mode to identify issues
3. Review WooCommerce logs for errors
4. Contact plugin author with detailed error information

## Changelog

### Version 1.0.0
- Initial release
- WooCommerce Block Checkout support
- Classic checkout support
- Real-time AJAX validation
- Multilingual support
- HPOS compatibility
- Admin settings panel