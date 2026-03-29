<?php
/**
 * FD Admin UI - Content Area Settings Module
 * Manages the appearance of the main content area
 */

defined('ABSPATH') || exit;

class FD_Content_Area {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'content_bg_color' => '#f8fafc',
            'content_card_bg_color' => '',
            'content_card_border_radius' => '',
            'content_card_shadow' => '',
        );
    }

    /**
     * Sanitize module options
     */
    public static function sanitize($input, $sanitized) {
        if (isset($input['content_bg_color'])) {
            $sanitized['content_bg_color'] = !empty($input['content_bg_color'])
                ? FD_Admin_UI_Settings::sanitize_color($input['content_bg_color'])
                : '';
        }

        if (isset($input['content_card_bg_color'])) {
            $sanitized['content_card_bg_color'] = !empty($input['content_card_bg_color'])
                ? FD_Admin_UI_Settings::sanitize_color($input['content_card_bg_color'])
                : '';
        }

        if (isset($input['content_card_border_radius'])) {
            $sanitized['content_card_border_radius'] = $input['content_card_border_radius'] !== ''
                ? absint($input['content_card_border_radius'])
                : '';
        }

        if (isset($input['content_card_shadow'])) {
            $sanitized['content_card_shadow'] = in_array($input['content_card_shadow'], array('', 'light', 'medium'))
                ? $input['content_card_shadow']
                : '';
        }

        return $sanitized;
    }

    /**
     * Output module CSS
     */
    public static function output_styles($options) {
        $content_bg = !empty($options['content_bg_color']) ? $options['content_bg_color'] : '#f8fafc';
        $card_bg = $options['content_card_bg_color'];
        $card_radius = $options['content_card_border_radius'];
        $card_shadow = $options['content_card_shadow'];

        ?>
        /* Content area background */
        html,
        body,
        #wpcontent,
        #wpbody,
        #wpbody-content,
        #wpbody-content > .wrap,
        .wrap {
            background: <?php echo esc_attr($content_bg); ?> !important;
        }

        <?php if (!empty($card_bg)): ?>
        /* Card background */
        .postbox,
        .meta-box-sortables .postbox,
        .stuffbox {
            background: <?php echo esc_attr($card_bg); ?> !important;
        }
        <?php endif; ?>

        <?php if ($card_radius !== ''): ?>
        /* Card border radius */
        .postbox,
        .meta-box-sortables .postbox,
        .stuffbox,
        .notice,
        .updated,
        .error {
            border-radius: <?php echo esc_attr($card_radius); ?>px !important;
        }
        <?php endif; ?>

        <?php if (!empty($card_shadow)): ?>
        /* Card shadow */
        .postbox,
        .meta-box-sortables .postbox,
        .stuffbox {
            <?php if ($card_shadow === 'light'): ?>
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
            <?php elseif ($card_shadow === 'medium'): ?>
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12) !important;
            <?php endif; ?>
            border: 1px solid rgba(0, 0, 0, 0.06) !important;
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
     * Register content area settings
     */
    public static function register_settings() {
        add_settings_section(
            'fd_content_section',
            __('Content Area Settings', 'fd-admin-ui'),
            array(__CLASS__, 'section_callback'),
            'fd-admin-ui-settings-content'
        );

        add_settings_field(
            'content_bg_color',
            __('Content Background Color', 'fd-admin-ui'),
            array(__CLASS__, 'bg_color_callback'),
            'fd-admin-ui-settings-content',
            'fd_content_section'
        );

        add_settings_field(
            'content_card_bg_color',
            __('Card Background Color', 'fd-admin-ui'),
            array(__CLASS__, 'card_bg_color_callback'),
            'fd-admin-ui-settings-content',
            'fd_content_section'
        );

        add_settings_field(
            'content_card_border_radius',
            __('Card Border Radius', 'fd-admin-ui'),
            array(__CLASS__, 'card_border_radius_callback'),
            'fd-admin-ui-settings-content',
            'fd_content_section'
        );

        add_settings_field(
            'content_card_shadow',
            __('Card Shadow', 'fd-admin-ui'),
            array(__CLASS__, 'card_shadow_callback'),
            'fd-admin-ui-settings-content',
            'fd_content_section'
        );
    }

    // ========================================
    // Callbacks
    // ========================================

    public static function section_callback() {
        echo '<p>' . esc_html__('Customize the main content area appearance, including background and card styles.', 'fd-admin-ui') . '</p>';
    }

    public static function bg_color_callback() {
        $options = self::get_options();
        $color = isset($options['content_bg_color']) ? $options['content_bg_color'] : '#f8fafc';
        ?>
        <input type="text" class="fd-color-picker" name="fd_admin_ui_options[content_bg_color]" value="<?php echo esc_attr($color); ?>" data-default-color="#f8fafc">
        <p class="description"><?php esc_html_e('Main content area background color. Default: #f8fafc (fd-gray-50)', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function card_bg_color_callback() {
        $options = self::get_options();
        $color = isset($options['content_card_bg_color']) ? $options['content_card_bg_color'] : '';
        ?>
        <input type="text" class="fd-color-picker" name="fd_admin_ui_options[content_card_bg_color]" value="<?php echo esc_attr($color); ?>" data-default-color="">
        <p class="description"><?php esc_html_e('Card/panel background color (postbox, meta-box, etc.). Leave empty for WordPress default (#fff).', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function card_border_radius_callback() {
        $options = self::get_options();
        $value = isset($options['content_card_border_radius']) ? $options['content_card_border_radius'] : '';
        ?>
        <select name="fd_admin_ui_options[content_card_border_radius]">
            <option value="" <?php selected($value, ''); ?>><?php esc_html_e('WordPress Default (0)', 'fd-admin-ui'); ?></option>
            <option value="4" <?php selected($value, '4'); ?>>4px</option>
            <option value="6" <?php selected($value, '6'); ?>>6px</option>
            <option value="8" <?php selected($value, '8'); ?>>8px</option>
            <option value="12" <?php selected($value, '12'); ?>>12px</option>
        </select>
        <p class="description"><?php esc_html_e('Card/panel border radius size.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function card_shadow_callback() {
        $options = self::get_options();
        $value = isset($options['content_card_shadow']) ? $options['content_card_shadow'] : '';
        ?>
        <select name="fd_admin_ui_options[content_card_shadow]">
            <option value="" <?php selected($value, ''); ?>><?php esc_html_e('No Shadow (WordPress Default)', 'fd-admin-ui'); ?></option>
            <option value="light" <?php selected($value, 'light'); ?>><?php esc_html_e('Light Shadow', 'fd-admin-ui'); ?></option>
            <option value="medium" <?php selected($value, 'medium'); ?>><?php esc_html_e('Medium Shadow', 'fd-admin-ui'); ?></option>
        </select>
        <p class="description"><?php esc_html_e('Card/panel shadow effect for added depth.', 'fd-admin-ui'); ?></p>
        <?php
    }
}
