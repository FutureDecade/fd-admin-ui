<?php
/**
 * FD Admin UI - Dashboard Settings Module
 * Manages dashboard page display settings
 */

defined('ABSPATH') || exit;

class FD_Dashboard_Settings {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'dashboard_hide_welcome' => false,
            'dashboard_columns' => '',
        );
    }

    /**
     * Sanitize module options
     */
    public static function sanitize($input, $sanitized) {
        $sanitized['dashboard_hide_welcome'] = isset($input['dashboard_hide_welcome']) && $input['dashboard_hide_welcome'];

        if (isset($input['dashboard_columns'])) {
            $sanitized['dashboard_columns'] = in_array($input['dashboard_columns'], array('', '1', '2', '3', '4'))
                ? $input['dashboard_columns']
                : '';
        }

        return $sanitized;
    }

    /**
     * Output module CSS (Dashboard needs no extra CSS)
     */
    public static function output_styles($options) {
        // Dashboard settings are implemented via PHP hooks, no CSS needed
    }

    /**
     * Get options (from main class)
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Register dashboard settings
     */
    public static function register_settings() {
        add_settings_section(
            'fd_dashboard_section',
            __('Dashboard Settings', 'fd-admin-ui'),
            array(__CLASS__, 'section_callback'),
            'fd-admin-ui-settings-dashboard'
        );

        add_settings_field(
            'dashboard_hide_welcome',
            __('Hide Welcome Panel', 'fd-admin-ui'),
            array(__CLASS__, 'hide_welcome_callback'),
            'fd-admin-ui-settings-dashboard',
            'fd_dashboard_section'
        );

        add_settings_field(
            'dashboard_columns',
            __('Default Columns', 'fd-admin-ui'),
            array(__CLASS__, 'columns_callback'),
            'fd-admin-ui-settings-dashboard',
            'fd_dashboard_section'
        );
    }

    // ========================================
    // Callbacks
    // ========================================

    public static function section_callback() {
        echo '<p>' . esc_html__('Customize the dashboard page display.', 'fd-admin-ui') . '</p>';
    }

    public static function hide_welcome_callback() {
        $options = self::get_options();
        $checked = isset($options['dashboard_hide_welcome']) && $options['dashboard_hide_welcome'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[dashboard_hide_welcome]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Hide the dashboard Welcome Panel.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function columns_callback() {
        $options = self::get_options();
        $value = isset($options['dashboard_columns']) ? $options['dashboard_columns'] : '';
        ?>
        <select name="fd_admin_ui_options[dashboard_columns]">
            <option value="" <?php selected($value, ''); ?>><?php esc_html_e('WordPress Default', 'fd-admin-ui'); ?></option>
            <?php /* translators: %d: number of columns */ ?>
            <option value="1" <?php selected($value, '1'); ?>><?php echo esc_html(sprintf(__('%d Column', 'fd-admin-ui'), 1)); ?></option>
            <?php /* translators: %d: number of columns */ ?>
            <option value="2" <?php selected($value, '2'); ?>><?php echo esc_html(sprintf(_n('%d Column', '%d Columns', 2, 'fd-admin-ui'), 2)); ?></option>
            <?php /* translators: %d: number of columns */ ?>
            <option value="3" <?php selected($value, '3'); ?>><?php echo esc_html(sprintf(_n('%d Column', '%d Columns', 3, 'fd-admin-ui'), 3)); ?></option>
            <?php /* translators: %d: number of columns */ ?>
            <option value="4" <?php selected($value, '4'); ?>><?php echo esc_html(sprintf(_n('%d Column', '%d Columns', 4, 'fd-admin-ui'), 4)); ?></option>
        </select>
        <p class="description"><?php esc_html_e('Default number of dashboard columns. Users can still adjust via Screen Options.', 'fd-admin-ui'); ?></p>
        <?php
    }

    // ========================================
    // Dashboard Implementation
    // ========================================

    /**
     * Apply dashboard settings
     * Called on wp_dashboard_setup hook
     */
    public static function setup() {
        $options = self::get_options();

        // Hide welcome panel
        if (!empty($options['dashboard_hide_welcome']) && $options['dashboard_hide_welcome']) {
            remove_action('welcome_panel', 'wp_welcome_panel');
        }

        // Set default columns
        if (!empty($options['dashboard_columns'])) {
            $columns = absint($options['dashboard_columns']);
            if ($columns >= 1 && $columns <= 4) {
                add_filter('get_user_option_screen_layout_dashboard', function() use ($columns) {
                    return $columns;
                });
            }
        }
    }
}
