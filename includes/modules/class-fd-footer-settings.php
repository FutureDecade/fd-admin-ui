<?php
/**
 * Lingcoo Admin UI - Footer Settings Module
 * Manages admin footer display content
 */

defined('ABSPATH') || exit;

class FD_Footer_Settings {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'footer_hide' => true,
            'footer_custom_text' => '版权所有 © 讯鸟云服智能科技（青岛）有限公司',
            'footer_hide_version' => true,
        );
    }

    /**
     * Sanitize module options
     */
    public static function sanitize($input, $sanitized) {
        $sanitized['footer_hide'] = isset($input['footer_hide']) && $input['footer_hide'];

        if (isset($input['footer_custom_text'])) {
            $sanitized['footer_custom_text'] = wp_kses_post($input['footer_custom_text']);
        }

        $sanitized['footer_hide_version'] = isset($input['footer_hide_version']) && $input['footer_hide_version'];

        return $sanitized;
    }

    /**
     * Output module CSS
     */
    public static function output_styles($options) {
        if (!isset($options['footer_hide']) || !$options['footer_hide']) {
            return;
        }
        ?>
        <?php if ($options['footer_hide']): ?>
        /* Hide footer */
        #wpfooter { display: none !important; }
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
     * Register footer settings
     */
    public static function register_settings() {
        add_settings_section(
            'fd_footer_section',
            __('Footer Settings', 'fd-admin-ui'),
            array(__CLASS__, 'section_callback'),
            'fd-admin-ui-settings-footer'
        );

        add_settings_field(
            'footer_hide',
            __('Hide Footer', 'fd-admin-ui'),
            array(__CLASS__, 'hide_callback'),
            'fd-admin-ui-settings-footer',
            'fd_footer_section'
        );

        add_settings_field(
            'footer_custom_text',
            __('Custom Footer Text', 'fd-admin-ui'),
            array(__CLASS__, 'custom_text_callback'),
            'fd-admin-ui-settings-footer',
            'fd_footer_section'
        );

        add_settings_field(
            'footer_hide_version',
            __('Hide Version Number', 'fd-admin-ui'),
            array(__CLASS__, 'hide_version_callback'),
            'fd-admin-ui-settings-footer',
            'fd_footer_section'
        );
    }

    // ========================================
    // Callbacks
    // ========================================

    public static function section_callback() {
        echo '<p>' . esc_html__('Customize the admin footer display content.', 'fd-admin-ui') . '</p>';
    }

    public static function hide_callback() {
        $options = self::get_options();
        $checked = isset($options['footer_hide']) && $options['footer_hide'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[footer_hide]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Hide the admin footer area ("Thank you for creating with WordPress", etc.)', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function custom_text_callback() {
        $options = self::get_options();
        $text = isset($options['footer_custom_text']) ? $options['footer_custom_text'] : '';
        ?>
        <input type="text" name="fd_admin_ui_options[footer_custom_text]" value="<?php echo esc_attr($text); ?>" class="regular-text">
        <p class="description"><?php esc_html_e('Custom footer text to replace "Thank you for creating with WordPress". HTML supported. Leave empty for default.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function hide_version_callback() {
        $options = self::get_options();
        $checked = isset($options['footer_hide_version']) && $options['footer_hide_version'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[footer_hide_version]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Hide the WordPress version number in the footer (for security reasons).', 'fd-admin-ui'); ?></p>
        <?php
    }
}
