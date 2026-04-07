<?php
/**
 * FD Admin UI - Login Page Settings Module
 * Manages the appearance settings of the login page
 */

defined('ABSPATH') || exit;

class FD_Login_Settings {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'login_enable' => true,
            'login_logo_image' => FD_ADMIN_UI_BRAND_LOGO_SQUARE_URL,
            'login_logo_height' => 60,
            'login_logo_url' => '',
            'login_bg_preset' => 'custom',
            'login_bg_light_color' => '#f0f0f1',
            'login_bg_gradient_start' => '#667eea',
            'login_bg_gradient_end' => '#764ba2',
            'login_bg_image' => FD_ADMIN_UI_LOGIN_BG_IMAGE_URL,
            'login_theme_color' => '#2962ff',
            'login_form_width' => 360,
            'login_form_bg_opacity' => 40,
            'login_text_color' => '#ffffff',
            'admin_favicon' => FD_ADMIN_UI_ADMIN_FAVICON_URL,
        );
    }

    /**
     * Sanitize module options
     */
    public static function sanitize($input, $sanitized) {
        $sanitized['login_enable'] = isset($input['login_enable']) && $input['login_enable'];

        if (isset($input['login_logo_image'])) {
            $sanitized['login_logo_image'] = esc_url_raw($input['login_logo_image']);
        }

        if (isset($input['login_logo_height'])) {
            $sanitized['login_logo_height'] = max(40, min(200, absint($input['login_logo_height'])));
        }

        if (isset($input['login_logo_url'])) {
            $sanitized['login_logo_url'] = esc_url_raw($input['login_logo_url']);
        }

        if (isset($input['login_bg_preset'])) {
            $sanitized['login_bg_preset'] = in_array($input['login_bg_preset'], array('light', 'gradient', 'custom'))
                ? $input['login_bg_preset']
                : 'light';
        }

        if (isset($input['login_bg_light_color'])) {
            $sanitized['login_bg_light_color'] = FD_Admin_UI_Settings::sanitize_color($input['login_bg_light_color']);
        }

        if (isset($input['login_bg_gradient_start'])) {
            $sanitized['login_bg_gradient_start'] = FD_Admin_UI_Settings::sanitize_color($input['login_bg_gradient_start']);
        }

        if (isset($input['login_bg_gradient_end'])) {
            $sanitized['login_bg_gradient_end'] = FD_Admin_UI_Settings::sanitize_color($input['login_bg_gradient_end']);
        }

        if (isset($input['login_bg_image'])) {
            $sanitized['login_bg_image'] = esc_url_raw($input['login_bg_image']);
        }

        if (isset($input['login_theme_color'])) {
            $sanitized['login_theme_color'] = FD_Admin_UI_Settings::sanitize_color($input['login_theme_color']);
        }

        if (isset($input['login_form_width'])) {
            $sanitized['login_form_width'] = max(280, min(600, absint($input['login_form_width'])));
        }

        if (isset($input['login_form_bg_opacity'])) {
            $sanitized['login_form_bg_opacity'] = max(0, min(100, absint($input['login_form_bg_opacity'])));
        }

        if (isset($input['login_text_color'])) {
            $sanitized['login_text_color'] = FD_Admin_UI_Settings::sanitize_color($input['login_text_color']);
        }

        if (isset($input['admin_favicon'])) {
            $sanitized['admin_favicon'] = esc_url_raw($input['admin_favicon']);
        }

        return $sanitized;
    }

    /**
     * Output module CSS (login page CSS is handled by a separate login page hook)
     */
    public static function output_styles($options) {
        // Login page styles are handled independently via the login_enqueue_scripts hook
    }

    /**
     * Get options (from the main class)
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Register login page related settings
     */
    public static function register_settings() {
        // Basic settings
        add_settings_section(
            'fd_login_basic_section',
            __('Basic Settings', 'fd-admin-ui'),
            array(__CLASS__, 'basic_section_callback'),
            'fd-admin-ui-settings-login'
        );

        add_settings_field(
            'login_enable',
            __('Enable Login Page Customization', 'fd-admin-ui'),
            array(__CLASS__, 'enable_callback'),
            'fd-admin-ui-settings-login',
            'fd_login_basic_section'
        );

        add_settings_field(
            'admin_favicon',
            __('Admin Favicon', 'fd-admin-ui'),
            array(__CLASS__, 'admin_favicon_callback'),
            'fd-admin-ui-settings-login',
            'fd_login_basic_section'
        );

        // Logo settings
        add_settings_section(
            'fd_login_logo_section',
            __('Logo Settings', 'fd-admin-ui'),
            array(__CLASS__, 'logo_section_callback'),
            'fd-admin-ui-settings-login'
        );

        add_settings_field(
            'login_logo_image',
            __('Logo Image', 'fd-admin-ui'),
            array(__CLASS__, 'logo_image_callback'),
            'fd-admin-ui-settings-login',
            'fd_login_logo_section'
        );

        add_settings_field(
            'login_logo_url',
            __('Logo Link', 'fd-admin-ui'),
            array(__CLASS__, 'logo_url_callback'),
            'fd-admin-ui-settings-login',
            'fd_login_logo_section'
        );

        // Appearance settings
        add_settings_section(
            'fd_login_appearance_section',
            __('Appearance Settings', 'fd-admin-ui'),
            array(__CLASS__, 'appearance_section_callback'),
            'fd-admin-ui-settings-login'
        );

        add_settings_field(
            'login_bg_preset',
            __('Background Style', 'fd-admin-ui'),
            array(__CLASS__, 'bg_preset_callback'),
            'fd-admin-ui-settings-login',
            'fd_login_appearance_section'
        );

        add_settings_field(
            'login_theme_color',
            __('Theme Color', 'fd-admin-ui'),
            array(__CLASS__, 'theme_color_callback'),
            'fd-admin-ui-settings-login',
            'fd_login_appearance_section'
        );

        add_settings_field(
            'login_form_width',
            __('Form Width', 'fd-admin-ui'),
            array(__CLASS__, 'form_width_callback'),
            'fd-admin-ui-settings-login',
            'fd_login_appearance_section'
        );

        add_settings_field(
            'login_form_bg_opacity',
            __('Form Background Opacity', 'fd-admin-ui'),
            array(__CLASS__, 'form_bg_opacity_callback'),
            'fd-admin-ui-settings-login',
            'fd_login_appearance_section'
        );

        add_settings_field(
            'login_text_color',
            __('Text Color', 'fd-admin-ui'),
            array(__CLASS__, 'text_color_callback'),
            'fd-admin-ui-settings-login',
            'fd_login_appearance_section'
        );
    }

    // ========================================
    // Section Callbacks
    // ========================================

    public static function basic_section_callback() {
        echo '<p>' . esc_html__('Enable login page customization and set the admin favicon.', 'fd-admin-ui') . '</p>';
    }

    public static function logo_section_callback() {
        echo '<p>' . esc_html__('Customize the logo image and link on the login page (logo size is auto-adapted, no manual setup needed).', 'fd-admin-ui') . '</p>';
    }

    public static function appearance_section_callback() {
        echo '<p>' . esc_html__('Set the background style, theme color, and form styles for the login page.', 'fd-admin-ui') . '</p>';
    }

    // ========================================
    // Field Callbacks
    // ========================================

    public static function enable_callback() {
        $options = self::get_options();
        $checked = isset($options['login_enable']) && $options['login_enable'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[login_enable]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('When enabled, the login page customization settings below will be applied.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function admin_favicon_callback() {
        $options = self::get_options();
        $favicon = isset($options['admin_favicon']) ? $options['admin_favicon'] : '';
        ?>
        <div class="fd-image-upload-wrap">
            <input type="text"
                   name="fd_admin_ui_options[admin_favicon]"
                   value="<?php echo esc_attr($favicon); ?>"
                   class="fd-form-input"
                   id="admin_favicon_input"
                   style="width: 400px;">
            <button type="button" class="fd-btn fd-btn-secondary fd-upload-btn" data-target="admin_favicon_input"><?php esc_html_e('Select Image', 'fd-admin-ui'); ?></button>
            <?php if ($favicon) : ?>
                <button type="button" class="fd-btn fd-btn-secondary fd-remove-btn" data-target="admin_favicon_input"><?php esc_html_e('Remove', 'fd-admin-ui'); ?></button>
            <?php endif; ?>
        </div>
        <?php if ($favicon) : ?>
            <div class="fd-image-preview" style="margin-top: 10px;">
                <img src="<?php echo esc_url($favicon); ?>" style="max-width: 32px; max-height: 32px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
        <?php endif; ?>
        <p class="description"><?php esc_html_e('Set the favicon for the admin dashboard and login page (recommended: 32x32 or 192x192 pixel PNG/ICO file).', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function logo_image_callback() {
        $options = self::get_options();
        $logo = isset($options['login_logo_image']) ? $options['login_logo_image'] : '';
        $logo_height = isset($options['login_logo_height']) ? $options['login_logo_height'] : 60;
        ?>
        <div class="fd-image-upload-wrap">
            <input type="text"
                   name="fd_admin_ui_options[login_logo_image]"
                   value="<?php echo esc_attr($logo); ?>"
                   class="fd-form-input"
                   id="login_logo_image_input"
                   style="width: 400px;">
            <button type="button" class="fd-btn fd-btn-secondary fd-upload-btn" data-target="login_logo_image_input"><?php esc_html_e('Select Image', 'fd-admin-ui'); ?></button>
            <?php if ($logo) : ?>
                <button type="button" class="fd-btn fd-btn-secondary fd-remove-btn" data-target="login_logo_image_input"><?php esc_html_e('Remove', 'fd-admin-ui'); ?></button>
            <?php endif; ?>
        </div>
        <?php if ($logo) : ?>
            <div class="fd-image-preview" style="margin-top: 10px;">
                <img src="<?php echo esc_url($logo); ?>" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 8px; background: #f5f5f5;">
            </div>
        <?php endif; ?>

        <div style="margin-top: 15px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;"><?php esc_html_e('Logo Height', 'fd-admin-ui'); ?></label>
            <input type="number"
                   name="fd_admin_ui_options[login_logo_height]"
                   value="<?php echo esc_attr($logo_height); ?>"
                   class="fd-form-input"
                   min="40"
                   max="200"
                   step="1"
                   style="width: 100px;"> px
            <p class="description"><?php esc_html_e('Display height of the logo (40-200 pixels), width auto-adapts. Default: 60px.', 'fd-admin-ui'); ?></p>
        </div>
        <?php
    }

    public static function logo_url_callback() {
        $options = self::get_options();
        $url = isset($options['login_logo_url']) ? $options['login_logo_url'] : '';
        $default_url = home_url();
        ?>
        <input type="url"
               name="fd_admin_ui_options[login_logo_url]"
               value="<?php echo esc_attr($url); ?>"
               class="fd-form-input"
               placeholder="<?php echo esc_attr($default_url); ?>"
               style="width: 400px;">
        <p class="description"><?php esc_html_e('URL to navigate to when the logo is clicked. Defaults to the homepage. The title automatically uses the site name.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function bg_preset_callback() {
        $options = self::get_options();
        $preset = isset($options['login_bg_preset']) ? $options['login_bg_preset'] : 'custom';
        $light_color = isset($options['login_bg_light_color']) ? $options['login_bg_light_color'] : '#f0f0f1';
        $gradient_start = isset($options['login_bg_gradient_start']) ? $options['login_bg_gradient_start'] : '#667eea';
        $gradient_end = isset($options['login_bg_gradient_end']) ? $options['login_bg_gradient_end'] : '#764ba2';
        ?>
        <div class="fd-login-preset-selector">
            <label class="fd-preset-option">
                <input type="radio" name="fd_admin_ui_options[login_bg_preset]" value="light" <?php checked($preset, 'light'); ?>>
                <div class="fd-preset-preview" style="background: <?php echo esc_attr($light_color); ?>;">
                    <div class="fd-preset-label"><?php esc_html_e('Light Background', 'fd-admin-ui'); ?></div>
                </div>
            </label>

            <label class="fd-preset-option">
                <input type="radio" name="fd_admin_ui_options[login_bg_preset]" value="gradient" <?php checked($preset, 'gradient'); ?>>
                <div class="fd-preset-preview" style="background: linear-gradient(135deg, <?php echo esc_attr($gradient_start); ?> 0%, <?php echo esc_attr($gradient_end); ?> 100%);">
                    <div class="fd-preset-label" style="color: white;"><?php esc_html_e('Gradient Background', 'fd-admin-ui'); ?></div>
                </div>
            </label>

            <label class="fd-preset-option">
                <input type="radio" name="fd_admin_ui_options[login_bg_preset]" value="custom" <?php checked($preset, 'custom'); ?>>
                <div class="fd-preset-preview" style="background: #1d2327;">
                    <div class="fd-preset-label" style="color: white;"><?php esc_html_e('Custom Image', 'fd-admin-ui'); ?></div>
                </div>
            </label>
        </div>

        <!-- Light background color settings -->
        <div id="light-bg-color" style="margin-top: 15px; <?php echo $preset !== 'light' ? 'display: none;' : ''; ?>">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;"><?php esc_html_e('Light Background Color', 'fd-admin-ui'); ?></label>
            <input type="text"
                   name="fd_admin_ui_options[login_bg_light_color]"
                   value="<?php echo esc_attr($light_color); ?>"
                   class="fd-color-picker"
                   data-default-color="#f0f0f1">
            <p class="description"><?php esc_html_e('Choose the light background color. Default: #f0f0f1', 'fd-admin-ui'); ?></p>
        </div>

        <!-- Gradient background color settings -->
        <div id="gradient-bg-colors" style="margin-top: 15px; <?php echo $preset !== 'gradient' ? 'display: none;' : ''; ?>">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;"><?php esc_html_e('Gradient Background Colors', 'fd-admin-ui'); ?></label>
            <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                <div>
                    <label style="font-size: 13px; margin-bottom: 4px; display: block;"><?php esc_html_e('Start Color', 'fd-admin-ui'); ?></label>
                    <input type="text"
                           name="fd_admin_ui_options[login_bg_gradient_start]"
                           value="<?php echo esc_attr($gradient_start); ?>"
                           class="fd-color-picker"
                           data-default-color="#667eea">
                </div>
                <div>
                    <label style="font-size: 13px; margin-bottom: 4px; display: block;"><?php esc_html_e('End Color', 'fd-admin-ui'); ?></label>
                    <input type="text"
                           name="fd_admin_ui_options[login_bg_gradient_end]"
                           value="<?php echo esc_attr($gradient_end); ?>"
                           class="fd-color-picker"
                           data-default-color="#764ba2">
                </div>
            </div>
            <p class="description"><?php esc_html_e('Choose the start and end colors for the gradient. The gradient direction is fixed at 135 degrees (bottom-right).', 'fd-admin-ui'); ?></p>
        </div>

        <!-- Custom image upload -->
        <div id="custom-bg-upload" style="margin-top: 15px; <?php echo $preset !== 'custom' ? 'display: none;' : ''; ?>">
            <?php
            $bg_image = isset($options['login_bg_image']) ? $options['login_bg_image'] : '';
            ?>
            <div class="fd-image-upload-wrap">
                <input type="text"
                       name="fd_admin_ui_options[login_bg_image]"
                       value="<?php echo esc_attr($bg_image); ?>"
                       class="fd-form-input"
                       id="login_bg_image_input"
                       style="width: 400px;">
                <button type="button" class="fd-btn fd-btn-secondary fd-upload-btn" data-target="login_bg_image_input"><?php esc_html_e('Select Image', 'fd-admin-ui'); ?></button>
                <?php if ($bg_image) : ?>
                    <button type="button" class="fd-btn fd-btn-secondary fd-remove-btn" data-target="login_bg_image_input"><?php esc_html_e('Remove', 'fd-admin-ui'); ?></button>
                <?php endif; ?>
            </div>
            <?php if ($bg_image) : ?>
                <div class="fd-image-preview" style="margin-top: 10px;">
                    <img src="<?php echo esc_url($bg_image); ?>" style="max-width: 200px; max-height: 120px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            <?php endif; ?>
        </div>

        <style>
        .fd-login-preset-selector {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .fd-preset-option {
            cursor: pointer;
            display: block;
        }
        .fd-preset-option input[type="radio"] {
            display: none;
        }
        .fd-preset-preview {
            width: 150px;
            height: 100px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid transparent;
            transition: all 0.2s;
        }
        .fd-preset-option input[type="radio"]:checked + .fd-preset-preview {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }
        .fd-preset-preview:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .fd-preset-label {
            font-weight: 600;
            font-size: 14px;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $('input[name="fd_admin_ui_options[login_bg_preset]"]').on('change', function() {
                var preset = $(this).val();
                $('#light-bg-color, #gradient-bg-colors, #custom-bg-upload').slideUp(200);
                if (preset === 'light') {
                    $('#light-bg-color').slideDown(200);
                } else if (preset === 'gradient') {
                    $('#gradient-bg-colors').slideDown(200);
                } else if (preset === 'custom') {
                    $('#custom-bg-upload').slideDown(200);
                }
            });
        });
        </script>

        <p class="description"><?php esc_html_e('Choose the background style for the login page. Light background suits most scenarios, gradient is more modern, and custom image allows uploading a branded background.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function theme_color_callback() {
        $options = self::get_options();
        $color = isset($options['login_theme_color']) ? $options['login_theme_color'] : '#2962ff';
        ?>
        <input type="text"
               name="fd_admin_ui_options[login_theme_color]"
               value="<?php echo esc_attr($color); ?>"
               class="fd-color-picker"
               data-default-color="#2962ff">
        <p class="description"><?php esc_html_e('The theme color is applied to the login button, input focus state, and links. Default: #2962ff.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function form_width_callback() {
        $options = self::get_options();
        $width = isset($options['login_form_width']) ? $options['login_form_width'] : 360;
        ?>
        <input type="number"
               name="fd_admin_ui_options[login_form_width]"
               value="<?php echo esc_attr($width); ?>"
               class="fd-form-input"
               min="280"
               max="600"
               style="width: 100px;"> px
        <p class="description"><?php esc_html_e('Width of the login form (280-600 pixels). Default: 360px. Automatically adapts on mobile.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function form_bg_opacity_callback() {
        $options = self::get_options();
        $opacity = isset($options['login_form_bg_opacity']) ? $options['login_form_bg_opacity'] : 40;
        ?>
        <input type="range"
               name="fd_admin_ui_options[login_form_bg_opacity]"
               value="<?php echo esc_attr($opacity); ?>"
               min="0"
               max="100"
               step="5"
               class="fd-form-range"
               id="login_form_bg_opacity_range"
               style="width: 200px; vertical-align: middle;">
        <span id="opacity_value" style="margin-left: 10px; font-weight: 600;"><?php echo esc_html($opacity); ?>%</span>
        <p class="description"><?php esc_html_e('Opacity of the form background (0% = fully transparent, 100% = fully opaque).', 'fd-admin-ui'); ?></p>

        <script>
        jQuery(document).ready(function($) {
            $('#login_form_bg_opacity_range').on('input', function() {
                $('#opacity_value').text($(this).val() + '%');
            });
        });
        </script>
        <?php
    }

    public static function text_color_callback() {
        $options = self::get_options();
        $color = isset($options['login_text_color']) ? $options['login_text_color'] : '';
        $default_color = '#1d2327';
        ?>
        <input type="text"
               name="fd_admin_ui_options[login_text_color]"
               value="<?php echo esc_attr($color); ?>"
               class="fd-color-picker"
               data-default-color="<?php echo esc_attr($default_color); ?>">
        <p class="description"><?php esc_html_e('Text color for login page navigation links (register, forgot password, back to site, etc.). Leave empty to auto-detect based on the background.', 'fd-admin-ui'); ?></p>
        <?php
    }
}
