<?php
/**
 * FD Admin UI - VI Settings Module
 * Brand visual identity settings (colors, border radius, shadows, etc.)
 *
 * If the fd-theme is active, sync with the theme's VI settings
 * If the theme is not active, use the plugin's own VI settings
 */

defined('ABSPATH') || exit;

class FD_VI_Settings {

    /**
     * Check if fd-theme is active
     */
    public static function is_fd_theme_active() {
        $current_theme = wp_get_theme();
        return (
            $current_theme->get_template() === 'fd-theme' ||
            $current_theme->get('TextDomain') === 'fd-theme' ||
            function_exists('fd_register_graphql_vi_settings')
        );
    }

    /**
     * Get VI option (automatically determines source)
     *
     * @param string $key Option key name
     * @param mixed $default Default value
     * @return mixed Option value
     */
    public static function get_vi_option($key, $default = '') {
        $is_fd_theme = self::is_fd_theme_active();

        if ($is_fd_theme) {
            // Read from theme
            return get_option($key, $default);
        } else {
            // Read from plugin
            $plugin_key = 'fd_admin_ui_' . str_replace('fd_', '', $key);
            return get_option($plugin_key, $default);
        }
    }

    /**
     * Save VI option (only when theme is not active)
     *
     * @param string $key Option key name
     * @param mixed $value Option value
     */
    public static function save_vi_option($key, $value) {
        $is_fd_theme = self::is_fd_theme_active();

        if (!$is_fd_theme) {
            // Only save to plugin settings when theme is not active
            $plugin_key = 'fd_admin_ui_' . str_replace('fd_', '', $key);
            update_option($plugin_key, $value);
        }
    }

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            // Color settings
            'vi_primary_color' => '#3b82f6',
            'vi_action_color' => '',
            'vi_secondary_color' => '#4285f4',
            'vi_success_color' => '#10b981',
            'vi_error_color' => '#ef4444',
            'vi_warning_color' => '#f59e0b',
            'vi_amber_color' => '#f59e0b',
            'vi_rose_color' => '#ec4899',

            // UI design tokens
            'vi_radius_small' => '4px',
            'vi_radius_medium' => '8px',
            'vi_radius_large' => '16px',
            'vi_shadow_small' => '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
            'vi_shadow_medium' => '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
            'vi_shadow_large' => '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
        );
    }

    /**
     * Sanitize module options
     */
    public static function sanitize($input, $sanitized) {
        // Only save when theme is not active
        if (self::is_fd_theme_active()) {
            return $sanitized;
        }

        // Color settings
        $color_fields = array(
            'vi_primary_color',
            'vi_action_color',
            'vi_secondary_color',
            'vi_success_color',
            'vi_error_color',
            'vi_warning_color',
            'vi_amber_color',
            'vi_rose_color',
        );

        foreach ($color_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_hex_color($input[$field]);
            }
        }

        // Design tokens
        $token_fields = array(
            'vi_radius_small',
            'vi_radius_medium',
            'vi_radius_large',
            'vi_shadow_small',
            'vi_shadow_medium',
            'vi_shadow_large',
        );

        foreach ($token_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_text_field($input[$field]);
            }
        }

        return $sanitized;
    }

    /**
     * Get options (from main class)
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Get VI setting value (for form display)
     */
    private static function get_vi_value($key, $default = '') {
        $is_fd_theme = self::is_fd_theme_active();
        $options = self::get_options();

        if ($is_fd_theme) {
            // Read from theme
            return get_option($key, $default);
        } else {
            // Read from plugin options
            $plugin_key = 'vi_' . str_replace('fd_', '', $key);
            return isset($options[$plugin_key]) ? $options[$plugin_key] : $default;
        }
    }

    /**
     * Register VI settings
     */
    public static function register_settings() {
        $is_fd_theme = self::is_fd_theme_active();

        // Color settings section
        add_settings_section(
            'fd_vi_colors_section',
            __('VI Color Settings', 'fd-admin-ui'),
            array(__CLASS__, 'colors_section_callback'),
            'fd-admin-ui-settings-vi'
        );

        add_settings_field(
            'vi_primary_color',
            __('Brand Primary', 'fd-admin-ui'),
            array(__CLASS__, 'primary_color_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_colors_section'
        );

        add_settings_field(
            'vi_action_color',
            __('Action', 'fd-admin-ui'),
            array(__CLASS__, 'action_color_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_colors_section'
        );

        add_settings_field(
            'vi_secondary_color',
            __('Brand Secondary', 'fd-admin-ui'),
            array(__CLASS__, 'secondary_color_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_colors_section'
        );

        add_settings_field(
            'vi_rose_color',
            __('Brand Accent', 'fd-admin-ui'),
            array(__CLASS__, 'rose_color_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_colors_section'
        );

        add_settings_field(
            'vi_success_color',
            __('Success', 'fd-admin-ui'),
            array(__CLASS__, 'success_color_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_colors_section'
        );

        add_settings_field(
            'vi_error_color',
            __('Error', 'fd-admin-ui'),
            array(__CLASS__, 'error_color_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_colors_section'
        );

        add_settings_field(
            'vi_warning_color',
            __('Warning', 'fd-admin-ui'),
            array(__CLASS__, 'warning_color_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_colors_section'
        );

        // UI design tokens section
        add_settings_section(
            'fd_vi_tokens_section',
            __('UI Design Tokens', 'fd-admin-ui'),
            array(__CLASS__, 'tokens_section_callback'),
            'fd-admin-ui-settings-vi'
        );

        add_settings_field(
            'vi_radius_small',
            __('Small Border Radius', 'fd-admin-ui'),
            array(__CLASS__, 'radius_small_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_tokens_section'
        );

        add_settings_field(
            'vi_radius_medium',
            __('Medium Border Radius', 'fd-admin-ui'),
            array(__CLASS__, 'radius_medium_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_tokens_section'
        );

        add_settings_field(
            'vi_radius_large',
            __('Large Border Radius', 'fd-admin-ui'),
            array(__CLASS__, 'radius_large_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_tokens_section'
        );

        add_settings_field(
            'vi_shadow_small',
            __('Small Shadow', 'fd-admin-ui'),
            array(__CLASS__, 'shadow_small_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_tokens_section'
        );

        add_settings_field(
            'vi_shadow_medium',
            __('Medium Shadow', 'fd-admin-ui'),
            array(__CLASS__, 'shadow_medium_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_tokens_section'
        );

        add_settings_field(
            'vi_shadow_large',
            __('Large Shadow', 'fd-admin-ui'),
            array(__CLASS__, 'shadow_large_callback'),
            'fd-admin-ui-settings-vi',
            'fd_vi_tokens_section'
        );
    }

    // ========================================
    // Callback functions
    // ========================================

    public static function colors_section_callback() {
        $is_fd_theme = self::is_fd_theme_active();

        if ($is_fd_theme) {
            $vi_settings_url = admin_url('admin.php?page=fd-vi-settings');
            ?>
            <div class="fd-alert fd-alert-info" style="margin-bottom: 20px;">
                <div class="fd-alert-icon">
                    <span class="dashicons dashicons-info"></span>
                </div>
                <div class="fd-alert-content">
                    <div class="fd-alert-message">
                        <strong><?php esc_html_e('Synced with Theme VI Settings', 'fd-admin-ui'); ?></strong><br>
                        <?php esc_html_e('FD Theme is active. Color settings are automatically synced with the theme\'s VI settings.', 'fd-admin-ui'); ?>
                        <br>
                        <a href="<?php echo esc_url($vi_settings_url); ?>" style="margin-top: 8px; display: inline-block;"><?php esc_html_e('Go to Theme VI Settings', 'fd-admin-ui'); ?> &rarr;</a>
                    </div>
                </div>
            </div>
            <p><?php esc_html_e('The following settings are read-only, showing the current theme VI color configuration.', 'fd-admin-ui'); ?></p>
            <?php
        } else {
            ?>
            <p><?php esc_html_e('Configure the brand color scheme for the admin interface. These colors will be applied to buttons, links, status indicators, and other elements.', 'fd-admin-ui'); ?></p>
            <div class="fd-alert fd-alert-info" style="margin-top: 12px; padding: 12px 16px; background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 4px;">
                <div style="display: flex; align-items: start; gap: 10px;">
                    <span class="dashicons dashicons-info" style="color: #3b82f6; margin-top: 2px;"></span>
                    <div style="flex: 1; font-size: 13px; line-height: 1.6;">
                        <strong style="color: #1e40af;"><?php esc_html_e('Automatic Color Scale Generation', 'fd-admin-ui'); ?></strong><br>
                        <?php esc_html_e('The first 3 brand colors (primary, secondary, accent) automatically generate a complete color scale system (50-900, 10 shades) for various UI elements in the admin interface. The scales are calculated based on the HSL color space to ensure visual consistency.', 'fd-admin-ui'); ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public static function tokens_section_callback() {
        $is_fd_theme = self::is_fd_theme_active();

        if ($is_fd_theme) {
            ?>
            <p><?php esc_html_e('The following settings are read-only, showing the current theme UI design token configuration.', 'fd-admin-ui'); ?></p>
            <?php
        } else {
            ?>
            <p><?php esc_html_e('Configure border radius and shadow effects for UI elements to create a unified visual style.', 'fd-admin-ui'); ?></p>
            <div class="fd-alert fd-alert-info" style="margin-top: 12px; padding: 12px 16px; background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 4px;">
                <div style="display: flex; align-items: start; gap: 10px;">
                    <span class="dashicons dashicons-admin-appearance" style="color: #10b981; margin-top: 2px;"></span>
                    <div style="flex: 1; font-size: 13px; line-height: 1.6;">
                        <strong style="color: #065f46;"><?php esc_html_e('Design Token System', 'fd-admin-ui'); ?></strong><br>
                        <?php esc_html_e('These design tokens define the foundational visual styles for the admin interface. Through unified border radius and shadow settings, you can quickly adjust the overall visual style of the admin panel while maintaining design consistency.', 'fd-admin-ui'); ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public static function primary_color_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $color = self::get_vi_value('fd_primary_color', '#3b82f6');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_primary_color]';
        ?>
        <input type="text"
               class="fd-color-picker"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($color); ?>"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Brand primary color. Automatically generates primary-50 to primary-900 color scale for link highlights, active states, and brand identity.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function action_color_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $color = self::get_vi_value('fd_action_color', '');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_action_color]';
        ?>
        <input type="text"
               class="fd-color-picker"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($color); ?>"
               placeholder="<?php echo esc_attr__('Leave empty to match brand primary color', 'fd-admin-ui'); ?>"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Action color for primary buttons (save, publish, submit, and other CTAs). Leave empty to inherit brand primary color.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function secondary_color_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $color = self::get_vi_value('fd_secondary_color', '#4285f4');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_secondary_color]';
        ?>
        <input type="text"
               class="fd-color-picker"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($color); ?>"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Brand secondary color. Automatically generates secondary-50 to secondary-900 color scale for secondary interactions and informational hints.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function rose_color_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $color = self::get_vi_value('fd_rose_color', '#ec4899');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_rose_color]';
        ?>
        <input type="text"
               class="fd-color-picker"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($color); ?>"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Brand accent color for highlights, tags, and attention guidance. Automatically generates accent-50 to accent-900 color scale.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function success_color_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $color = self::get_vi_value('fd_success_color', '#10b981');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_success_color]';
        ?>
        <input type="text"
               class="fd-color-picker"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($color); ?>"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Success color for indicating successful operations or positive states.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function error_color_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $color = self::get_vi_value('fd_error_color', '#ef4444');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_error_color]';
        ?>
        <input type="text"
               class="fd-color-picker"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($color); ?>"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Error color for indicating errors or negative states.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function warning_color_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $color = self::get_vi_value('fd_amber_color', '#f59e0b');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_warning_color]';
        ?>
        <input type="text"
               class="fd-color-picker"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($color); ?>"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Warning color for warnings and informational notices.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function radius_small_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $value = self::get_vi_value('fd_radius_small', '4px');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_radius_small]';
        ?>
        <input type="text"
               class="regular-text"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($value); ?>"
               placeholder="4px"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Small border radius value (e.g., 4px)', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function radius_medium_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $value = self::get_vi_value('fd_radius_medium', '8px');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_radius_medium]';
        ?>
        <input type="text"
               class="regular-text"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($value); ?>"
               placeholder="8px"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Medium border radius value (e.g., 8px)', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function radius_large_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $value = self::get_vi_value('fd_radius_large', '16px');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_radius_large]';
        ?>
        <input type="text"
               class="regular-text"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($value); ?>"
               placeholder="16px"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Large border radius value (e.g., 16px)', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function shadow_small_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $value = self::get_vi_value('fd_shadow_small', '0 1px 2px 0 rgba(0, 0, 0, 0.05)');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_shadow_small]';
        ?>
        <input type="text"
               class="regular-text"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($value); ?>"
               placeholder="0 1px 2px 0 rgba(0, 0, 0, 0.05)"
               style="width: 400px;"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Small shadow CSS value (e.g., 0 1px 2px 0 rgba(0, 0, 0, 0.05))', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function shadow_medium_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $value = self::get_vi_value('fd_shadow_medium', '0 4px 6px -1px rgba(0, 0, 0, 0.1)');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_shadow_medium]';
        ?>
        <input type="text"
               class="regular-text"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($value); ?>"
               placeholder="0 4px 6px -1px rgba(0, 0, 0, 0.1)"
               style="width: 400px;"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Medium shadow CSS value (e.g., 0 4px 6px -1px rgba(0, 0, 0, 0.1))', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function shadow_large_callback() {
        $is_fd_theme = self::is_fd_theme_active();
        $value = self::get_vi_value('fd_shadow_large', '0 10px 15px -3px rgba(0, 0, 0, 0.1)');
        $readonly = $is_fd_theme ? 'readonly' : '';
        $field_name = $is_fd_theme ? '' : 'fd_admin_ui_options[vi_shadow_large]';
        ?>
        <input type="text"
               class="regular-text"
               name="<?php echo esc_attr($field_name); ?>"
               value="<?php echo esc_attr($value); ?>"
               placeholder="0 10px 15px -3px rgba(0, 0, 0, 0.1)"
               style="width: 400px;"
               <?php echo esc_attr( $readonly ); ?>>
        <p class="description"><?php esc_html_e('Large shadow CSS value (e.g., 0 10px 15px -3px rgba(0, 0, 0, 0.1))', 'fd-admin-ui'); ?></p>
        <?php
    }
}
