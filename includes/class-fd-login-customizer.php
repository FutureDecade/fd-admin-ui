<?php
/**
 * FD Login Customizer
 * Customizes the admin login page
 */

defined('ABSPATH') || exit;

class FD_Login_Customizer {

    /**
     * Initialize
     */
    public static function init() {
        // Login page styles
        add_action('login_enqueue_scripts', array(__CLASS__, 'enqueue_login_styles'));
        add_action('login_head', array(__CLASS__, 'output_login_styles'));

        // Custom logo link
        add_filter('login_headerurl', array(__CLASS__, 'custom_logo_url'));
        add_filter('login_headertext', array(__CLASS__, 'custom_logo_title'));

        // Custom logo HTML (using JavaScript)
        add_action('login_footer', array(__CLASS__, 'output_logo_script'));

        // Admin favicon - remove default and output custom
        add_action('admin_head', array(__CLASS__, 'output_admin_favicon'), 1); // Priority 1, output as early as possible
        add_action('login_head', array(__CLASS__, 'output_admin_favicon'), 1);
        add_action('wp_head', array(__CLASS__, 'remove_default_site_icon'), 1);
        add_action('admin_head', array(__CLASS__, 'remove_default_site_icon'), 1);
        add_action('login_head', array(__CLASS__, 'remove_default_site_icon'), 1);
    }

    /**
     * Get login page options
     */
    public static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Enqueue login page scripts (simplified version - uses system fonts)
     */
    public static function enqueue_login_styles() {
        // Simplified version does not need to load Google Fonts
    }

    /**
     * Output login page styles
     */
    public static function output_login_styles() {
        $options = self::get_options();

        // If login page customization is not enabled, return immediately
        if (empty($options['login_enable'])) {
            return;
        }

        // Get simplified style variables
        $bg_preset = $options['login_bg_preset'] ?? 'light';
        $bg_light_color = $options['login_bg_light_color'] ?? '#f0f0f1';
        $bg_gradient_start = $options['login_bg_gradient_start'] ?? '#667eea';
        $bg_gradient_end = $options['login_bg_gradient_end'] ?? '#764ba2';
        $bg_image = $options['login_bg_image'] ?? '';
        $logo_image = $options['login_logo_image'] ?? '';
        $logo_height = $options['login_logo_height'] ?? 84;
        $theme_color = $options['login_theme_color'] ?? '#2271b1';
        $form_width = $options['login_form_width'] ?? 360;
        $form_bg_opacity = $options['login_form_bg_opacity'] ?? 100;
        $text_color = $options['login_text_color'] ?? '';

        // Calculate background style based on preset
        $bg_style = '';
        switch ($bg_preset) {
            case 'light':
                $bg_style = "background-color: {$bg_light_color};";
                break;
            case 'gradient':
                $bg_style = "background: linear-gradient(135deg, {$bg_gradient_start} 0%, {$bg_gradient_end} 100%);";
                break;
            case 'custom':
                if ($bg_image) {
                    $bg_style = "background: url('{$bg_image}') no-repeat center center fixed; background-size: cover;";
                } else {
                    $bg_style = "background-color: {$bg_light_color};";
                }
                break;
        }

        // Calculate hover color (theme color darkened by 15%)
        $hover_color = self::darken_color($theme_color, 15);

        // Calculate focus shadow color (theme color at 15% opacity)
        $focus_shadow = self::hex_to_rgba($theme_color, 0.15);

        // Use custom text color or auto-detect
        if (empty($text_color)) {
            $text_color = self::get_text_color($bg_preset, $bg_light_color, $bg_gradient_start);
        }
        $text_shadow = $text_color === '#ffffff' ? '0 1px 2px rgba(0, 0, 0, 0.1)' : 'none';

        // Fixed best-practice values
        $form_bg_color = 'rgba(255, 255, 255, ' . ($form_bg_opacity / 100) . ')';
        $form_border_radius = 12;
        $input_bg_color = '#ffffff';
        $input_border_color = '#8c8f94';
        $input_text_color = '#1d2327';
        $input_border_radius = 6;
        $button_text_color = '#ffffff';
        $button_border_radius = 6;
        $link_color = '#50575e';
        $label_color = '#1d2327';
        $font_stack = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif';

        ?>
        <style id="fd-login-customizer">
            /* ========================================
               FD Login Customizer - Simplified Version
               Uses industry best practices to reduce configuration complexity
               ======================================== */

            /* Page background */
            body.login {
                <?php echo esc_attr( $bg_style ); ?>
                font-family: <?php echo esc_attr( $font_stack ); ?>;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                position: relative;
            }

            /* Background gradient overlay - enhances depth */
            body.login::before {
                content: '';
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: linear-gradient(135deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0) 50%, rgba(0,0,0,0.2) 100%);
                pointer-events: none;
            }

            /* Center glow - guides attention */
            body.login::after {
                content: '';
                position: fixed;
                top: 50%; left: 50%;
                transform: translate(-50%, -50%);
                width: 80vw; height: 80vh;
                background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
                pointer-events: none;
            }

            /* Login container */
            #login {
                padding: 0;
                width: <?php echo intval($form_width); ?>px;
                max-width: 90vw;
                position: relative;
                z-index: 10;
            }

            /* Logo area - auto-adapted size */
            #login h1 {
                margin: 0 auto 24px;
                text-align: center;
            }

            #login h1 a {
                display: inline-block;
                position: relative;
                background-image: none !important;
                overflow: hidden;
                width: auto !important;
                height: auto !important;
                margin: 0;
                padding: 0;
                text-indent: 0;
            }

            #login h1 a img {
                height: <?php echo intval($logo_height); ?>px;
                width: auto;
                max-width: 100%;
                object-fit: contain;
                object-position: center;
                display: block;
            }

            #login h1 a:focus {
                outline: none;
                box-shadow: none;
            }

            <?php if (!$logo_image) : ?>
            #login h1 {
                display: none;
            }
            <?php endif; ?>

            /* Password toggle button focus style */
            .login .button.wp-hide-pw:focus {
                outline: none;
                box-shadow: none;
                border-color: transparent;
            }

            /* Form container - fixed best-practice styles with opacity support */
            #loginform,
            #registerform,
            #lostpasswordform {
                background: <?php echo esc_attr($form_bg_color); ?>;
                border: none;
                border-radius: <?php echo intval($form_border_radius); ?>px;
                box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
                padding: 32px;
                margin-top: 0;
                <?php if ($form_bg_opacity < 100) : ?>
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                <?php endif; ?>
            }

            /* Form labels - smart color */
            #loginform label,
            #registerform label,
            #lostpasswordform label {
                color: <?php echo esc_attr($text_color); ?>;
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 6px;
                display: block;
                <?php if ($text_shadow !== 'none') : ?>
                text-shadow: <?php echo esc_attr( $text_shadow ); ?>;
                <?php endif; ?>
            }

            /* Input fields - background color follows form opacity */
            #loginform input[type="text"],
            #loginform input[type="password"],
            #registerform input[type="text"],
            #registerform input[type="email"],
            #lostpasswordform input[type="text"],
            .login input[type="text"],
            .login input[type="password"] {
                background: <?php echo esc_attr($form_bg_color); ?>;
                border: 1.5px solid <?php echo esc_attr($input_border_color); ?>;
                border-radius: <?php echo intval($input_border_radius); ?>px;
                color: <?php echo esc_attr($input_text_color); ?>;
                font-size: 15px;
                width: 100%;
                box-sizing: border-box;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
                opacity: <?php echo esc_attr($form_bg_opacity / 100); ?>;
            }

            #loginform input[type="text"]:focus,
            #loginform input[type="password"]:focus,
            #registerform input[type="text"]:focus,
            #registerform input[type="email"]:focus,
            #lostpasswordform input[type="text"]:focus,
            .login input[type="text"]:focus,
            .login input[type="password"]:focus {
                border-color: <?php echo esc_attr($theme_color); ?>;
                box-shadow: 0 0 0 3px <?php echo esc_attr( $focus_shadow ); ?>;
                outline: none;
                opacity: 1;
            }

            /* Remember me checkbox */
            .login .forgetmenot {
                margin-bottom: 16px;
            }

            .login .forgetmenot label {
                font-size: 13px;
                color: <?php echo esc_attr($text_color); ?>;
                display: flex;
                align-items: center;
                gap: 8px;
                <?php if ($text_shadow !== 'none') : ?>
                text-shadow: <?php echo esc_attr( $text_shadow ); ?>;
                <?php endif; ?>
            }

            .login input[type="checkbox"] {
                width: 18px;
                height: 18px;
                border-radius: 4px;
                margin: 0;
                opacity: <?php echo esc_attr($form_bg_opacity / 100); ?>;
            }

            .login input[type="checkbox"]:checked,
            .login input[type="checkbox"]:focus {
                opacity: 1;
            }

            /* Login button - uses theme color, reduced size */
            #wp-submit,
            .login .button-primary {
                background: <?php echo esc_attr($theme_color); ?>;
                border: none;
                border-radius: <?php echo intval($button_border_radius); ?>px;
                color: <?php echo esc_attr($button_text_color); ?>;
                padding: 8px 20px;
                font-size: 14px;
                font-weight: 600;
                width: 100%;
                height: auto;
                cursor: pointer;
                transition: background-color 0.2s ease, transform 0.1s ease, opacity 0.2s ease;
                text-shadow: none;
                box-shadow: none;
                opacity: <?php echo esc_attr($form_bg_opacity / 100); ?>;
            }

            #wp-submit:hover,
            #wp-submit:focus,
            .login .button-primary:hover,
            .login .button-primary:focus {
                background: <?php echo esc_attr($hover_color); ?>;
                color: <?php echo esc_attr($button_text_color); ?>;
                box-shadow: none;
                opacity: 1;
            }

            #wp-submit:active,
            .login .button-primary:active {
                transform: scale(0.98);
            }

            /* Submit row layout */
            .login .submit {
                margin-top: 8px;
            }

            /* Navigation links - smart color */
            #nav,
            #backtoblog {
                text-align: center;
                margin: 20px 0 0;
                padding: 0;
            }

            #nav a,
            #backtoblog a {
                color: <?php echo esc_attr($text_color); ?> !important;
                font-size: 13px;
                text-decoration: none;
                transition: color 0.2s ease;
            }

            #nav a:hover,
            #backtoblog a:hover {
                color: <?php echo esc_attr($theme_color); ?> !important;
            }

            /* Navigation link separator color */
            #nav {
                color: <?php echo esc_attr($text_color); ?> !important;
            }

            /* Errors and messages */
            .login #login_error,
            .login .message,
            .login .success {
                border-radius: 8px;
                padding: 14px 18px;
                margin-bottom: 20px;
                border-left-width: 4px;
            }

            .login #login_error {
                border-left-color: #d63638;
                background: #fcf0f1;
            }

            .login .message {
                border-left-color: #72aee6;
                background: #f0f6fc;
            }

            .login .success {
                border-left-color: #00a32a;
                background: #edfaef;
            }

            /* Privacy policy link */
            .login .privacy-policy-page-link {
                text-align: center;
                margin-top: 20px;
            }

            .login .privacy-policy-page-link a {
                color: <?php echo esc_attr($text_color); ?>;
                font-size: 12px;
                <?php if ($text_shadow !== 'none') : ?>
                text-shadow: <?php echo esc_attr( $text_shadow ); ?>;
                <?php endif; ?>
            }

            .login .privacy-policy-page-link a:hover {
                color: <?php echo esc_attr($theme_color); ?>;
            }

            /* Language switcher - hidden */
            .login .language-switcher {
                display: none !important;
            }

            /* Responsive */
            @media screen and (max-width: 480px) {
                #login {
                    width: 100%;
                    padding: 20px;
                }

                #loginform,
                #registerform,
                #lostpasswordform {
                    padding: 24px;
                }
            }

            /* Animation effects */
            @keyframes fd-login-fade-in {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            #login {
                animation: fd-login-fade-in 0.4s ease-out;
            }
        </style>
        <?php
    }

    /**
     * Calculate color brightness (0-255)
     */
    private static function get_brightness($hex) {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Use perceived brightness formula
        return ($r * 299 + $g * 587 + $b * 114) / 1000;
    }

    /**
     * Determine whether to use dark or light text based on background color
     */
    private static function should_use_dark_text($bg_color) {
        $brightness = self::get_brightness($bg_color);
        // If brightness > 128, use dark text; otherwise use light text
        return $brightness > 128;
    }

    /**
     * Get text color (auto-detect based on background)
     */
    private static function get_text_color($bg_preset, $bg_light_color, $bg_gradient_start) {
        switch ($bg_preset) {
            case 'light':
                // Light background, determine based on color brightness
                return self::should_use_dark_text($bg_light_color) ? '#1d2327' : '#ffffff';
            case 'gradient':
                // Gradient background, determine based on start color (gradients are usually darker)
                $brightness = self::get_brightness($bg_gradient_start);
                return $brightness > 150 ? '#1d2327' : '#ffffff';
            case 'custom':
                // Custom image, default to white (images are usually darker)
                return '#ffffff';
            default:
                return '#ffffff';
        }
    }

    /**
     * Darken color function
     */
    private static function darken_color($hex, $percent) {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r - ($r * $percent / 100)));
        $g = max(0, min(255, $g - ($g * $percent / 100)));
        $b = max(0, min(255, $b - ($b * $percent / 100)));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    /**
     * Hex to RGBA
     */
    private static function hex_to_rgba($hex, $alpha = 1) {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }

    /**
     * Custom logo URL
     */
    public static function custom_logo_url($url) {
        $options = self::get_options();

        if (!empty($options['login_enable']) && !empty($options['login_logo_url'])) {
            return esc_url($options['login_logo_url']);
        }

        return home_url('/');
    }

    /**
     * Custom logo title (simplified - automatically uses site name)
     */
    public static function custom_logo_title($title) {
        return get_bloginfo('name');
    }

    /**
     * Output logo replacement script
     */
    public static function output_logo_script() {
        $options = self::get_options();

        if (empty($options['login_enable']) || empty($options['login_logo_image'])) {
            return;
        }

        $logo_image = esc_url($options['login_logo_image']);
        $logo_height = intval($options['login_logo_height']);
        ?>
        <script>
        (function() {
            var logoLink = document.querySelector('#login h1 a');
            if (logoLink) {
                var img = document.createElement('img');
                img.src = '<?php echo esc_url( $logo_image ); ?>';
                img.alt = '<?php echo esc_js(get_bloginfo('name')); ?>';
                img.style.height = '<?php echo esc_attr( $logo_height ); ?>px';
                img.style.objectFit = 'contain';
                img.style.objectPosition = 'center';
                img.style.display = 'block';

                logoLink.innerHTML = '';
                logoLink.appendChild(img);
            }
        })();
        </script>
        <?php
    }

    /**
     * Remove default Site Icon
     */
    public static function remove_default_site_icon() {
        $options = self::get_options();

        // If a custom favicon is set, remove WordPress default site icon output
        if (!empty($options['admin_favicon'])) {
            remove_action('wp_head', 'wp_site_icon', 99);
            remove_action('admin_head', 'wp_site_icon', 99);
            remove_action('login_head', 'wp_site_icon', 99);
        }
    }

    /**
     * Output custom Favicon
     */
    public static function output_admin_favicon() {
        $options = self::get_options();

        if (empty($options['admin_favicon'])) {
            return;
        }

        $favicon_url = esc_url($options['admin_favicon']);
        ?>
        <link rel="icon" type="image/x-icon" href="<?php echo esc_url( $favicon_url ); ?>" />
        <link rel="icon" type="image/png" href="<?php echo esc_url( $favicon_url ); ?>" sizes="32x32" />
        <link rel="icon" type="image/png" href="<?php echo esc_url( $favicon_url ); ?>" sizes="192x192" />
        <link rel="apple-touch-icon" href="<?php echo esc_url( $favicon_url ); ?>" />
        <meta name="msapplication-TileImage" content="<?php echo esc_url( $favicon_url ); ?>" />
        <?php
    }
}

// Initialize login page customizer
FD_Login_Customizer::init();
