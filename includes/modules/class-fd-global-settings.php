<?php
/**
 * FD Admin UI - Global Settings Module
 * Manages global appearance settings (border radius, font size, modern style)
 */

defined('ABSPATH') || exit;

class FD_Global_Settings {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'global_border_radius' => '',
            'global_base_font_size' => '',
            'settings_modern_style' => true, // Enable settings page modern style by default
        );
    }

    /**
     * Sanitize module options
     */
    public static function sanitize($input, $sanitized) {
        if (isset($input['global_border_radius'])) {
            $sanitized['global_border_radius'] = $input['global_border_radius'] !== ''
                ? absint($input['global_border_radius'])
                : '';
        }

        if (isset($input['global_base_font_size'])) {
            $sanitized['global_base_font_size'] = $input['global_base_font_size'] !== ''
                ? max(12, min(18, absint($input['global_base_font_size'])))
                : '';
        }

        $sanitized['settings_modern_style'] = isset($input['settings_modern_style']) && $input['settings_modern_style'];

        return $sanitized;
    }

    /**
     * Output module CSS
     */
    public static function output_styles($options) {
        $border_radius = $options['global_border_radius'];
        $has_radius = $border_radius !== '';

        $font_size = $options['global_base_font_size'];
        $has_font_size = !empty($font_size);

        if (!$has_radius && !$has_font_size) {
            return;
        }
        ?>
        <?php if ($has_radius): ?>
        /* Global border radius */
        .wp-core-ui .button,
        .wp-core-ui .button-primary,
        .wp-core-ui .button-secondary {
            border-radius: <?php echo esc_attr($border_radius); ?>px !important;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="url"],
        input[type="search"],
        input[type="number"],
        textarea,
        select {
            border-radius: <?php echo esc_attr($border_radius); ?>px !important;
        }
        <?php endif; ?>

        <?php if ($has_font_size): ?>
        /* Base font size */
        #wpcontent,
        #wpbody-content {
            font-size: <?php echo esc_attr($font_size); ?>px !important;
        }
        .form-table td,
        .form-table th {
            font-size: <?php echo esc_attr($font_size); ?>px !important;
        }
        <?php endif; ?>
        <?php
    }

    /**
     * Get options (from main class)
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Register global settings
     */
    public static function register_settings() {
        add_settings_section(
            'fd_global_section',
            __('Global Settings', 'fd-admin-ui'),
            array(__CLASS__, 'section_callback'),
            'fd-admin-ui-settings-global'
        );

        add_settings_field(
            'global_border_radius',
            __('Global Border Radius', 'fd-admin-ui'),
            array(__CLASS__, 'border_radius_callback'),
            'fd-admin-ui-settings-global',
            'fd_global_section'
        );

        add_settings_field(
            'global_base_font_size',
            __('Base Font Size', 'fd-admin-ui'),
            array(__CLASS__, 'base_font_size_callback'),
            'fd-admin-ui-settings-global',
            'fd_global_section'
        );

        add_settings_field(
            'settings_modern_style',
            __('Modern Settings Pages', 'fd-admin-ui'),
            array(__CLASS__, 'settings_modern_style_callback'),
            'fd-admin-ui-settings-global',
            'fd_global_section'
        );
    }

    // ========================================
    // Callbacks
    // ========================================

    public static function section_callback() {
        echo '<p>' . esc_html__('Global appearance settings that affect the entire admin interface.', 'fd-admin-ui') . '</p>';
    }

    public static function border_radius_callback() {
        $options = self::get_options();
        $value = isset($options['global_border_radius']) ? $options['global_border_radius'] : '';
        ?>
        <select name="fd_admin_ui_options[global_border_radius]">
            <option value="" <?php selected($value, ''); ?>><?php esc_html_e('WordPress Default', 'fd-admin-ui'); ?></option>
            <option value="0" <?php selected($value, '0'); ?>><?php echo esc_html('0px (' . __('Square', 'fd-admin-ui') . ')'); ?></option>
            <option value="4" <?php selected($value, '4'); ?>><?php echo esc_html('4px (' . __('Slight', 'fd-admin-ui') . ')'); ?></option>
            <option value="6" <?php selected($value, '6'); ?>><?php echo esc_html('6px (' . __('Medium', 'fd-admin-ui') . ')'); ?></option>
            <option value="8" <?php selected($value, '8'); ?>><?php echo esc_html('8px (' . __('Rounded', 'fd-admin-ui') . ')'); ?></option>
        </select>
        <p class="description"><?php esc_html_e('Global border radius for buttons, inputs, cards, and other elements.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function base_font_size_callback() {
        $options = self::get_options();
        $value = isset($options['global_base_font_size']) ? $options['global_base_font_size'] : '';
        ?>
        <select name="fd_admin_ui_options[global_base_font_size]">
            <option value="" <?php selected($value, ''); ?>><?php esc_html_e('WordPress Default (13px)', 'fd-admin-ui'); ?></option>
            <option value="14" <?php selected($value, '14'); ?>>14px</option>
            <option value="15" <?php selected($value, '15'); ?>>15px</option>
            <option value="16" <?php selected($value, '16'); ?>>16px</option>
        </select>
        <p class="description"><?php esc_html_e('Base font size that affects most admin text.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function settings_modern_style_callback() {
        $options = self::get_options();
        $checked = isset($options['settings_modern_style']) && $options['settings_modern_style'] ? 'checked' : '';
        // Enable by default
        if (!isset($options['settings_modern_style'])) {
            $checked = 'checked';
        }
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[settings_modern_style]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable modern styling for WordPress settings and tools pages, including form elements, buttons, and card components.', 'fd-admin-ui'); ?></p>
        <?php
    }
}
