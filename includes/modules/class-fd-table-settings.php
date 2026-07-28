<?php
/**
 * Lingcoo Admin UI - Table Settings Module
 * Manages admin list table appearance settings
 */

defined('ABSPATH') || exit;

class FD_Table_Settings {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'table_row_hover_color' => '',
            'table_striped' => false,
            'table_compact' => true,
            'table_horizontal_scroll' => true, // Enable horizontal scroll by default
            'table_modern_style' => true, // Enable modern style by default
        );
    }

    /**
     * Sanitize module options
     */
    public static function sanitize($input, $sanitized) {
        if (isset($input['table_row_hover_color'])) {
            $sanitized['table_row_hover_color'] = !empty($input['table_row_hover_color'])
                ? FD_Admin_UI_Settings::sanitize_color($input['table_row_hover_color'])
                : '';
        }

        $sanitized['table_striped'] = isset($input['table_striped']) && $input['table_striped'];
        $sanitized['table_compact'] = isset($input['table_compact']) && $input['table_compact'];
        $sanitized['table_horizontal_scroll'] = isset($input['table_horizontal_scroll']) && $input['table_horizontal_scroll'];
        $sanitized['table_modern_style'] = isset($input['table_modern_style']) && $input['table_modern_style'];

        return $sanitized;
    }

    /**
     * Output module CSS
     */
    public static function output_styles($options) {
        $table_hover = $options['table_row_hover_color'];
        $table_striped = $options['table_striped'];
        $table_compact = $options['table_compact'];

        if (empty($table_hover) && !$table_striped && !$table_compact) {
            return;
        }
        ?>
        <?php if (!empty($table_hover)): ?>
        /* Table row hover */
        .wp-list-table tbody tr:hover td,
        .wp-list-table tbody tr:hover th {
            background: <?php echo esc_attr($table_hover); ?> !important;
        }
        <?php endif; ?>

        <?php if ($table_striped): ?>
        /* Table striped rows */
        .wp-list-table tbody tr:nth-child(even),
        .wp-list-table tbody tr:nth-child(even).alternate {
            background: rgba(0, 0, 0, 0.02) !important;
        }
        .wp-list-table tbody tr:nth-child(even) td,
        .wp-list-table tbody tr:nth-child(even) th,
        .wp-list-table tbody tr:nth-child(even).alternate td,
        .wp-list-table tbody tr:nth-child(even).alternate th {
            background: transparent !important;
        }
        /* Striped row hover effect */
        .wp-list-table tbody tr:nth-child(even):hover,
        .wp-list-table tbody tr:nth-child(even).alternate:hover {
            background: rgba(0, 0, 0, 0.04) !important;
        }
        <?php endif; ?>

        <?php if ($table_compact): ?>
        /* Table compact mode */
        .wp-list-table td,
        .wp-list-table th {
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }
        .wp-list-table .column-title .row-title {
            font-size: 13px !important;
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
     * Register table settings
     */
    public static function register_settings() {
        add_settings_section(
            'fd_table_section',
            __('Table Settings', 'fd-admin-ui'),
            array(__CLASS__, 'section_callback'),
            'fd-admin-ui-settings-table'
        );

        add_settings_field(
            'table_modern_style',
            __('Modern Style', 'fd-admin-ui'),
            array(__CLASS__, 'modern_style_callback'),
            'fd-admin-ui-settings-table',
            'fd_table_section'
        );

        add_settings_field(
            'table_horizontal_scroll',
            __('Horizontal Scroll', 'fd-admin-ui'),
            array(__CLASS__, 'horizontal_scroll_callback'),
            'fd-admin-ui-settings-table',
            'fd_table_section'
        );

        add_settings_field(
            'table_row_hover_color',
            __('Row Hover Color', 'fd-admin-ui'),
            array(__CLASS__, 'row_hover_color_callback'),
            'fd-admin-ui-settings-table',
            'fd_table_section'
        );

        add_settings_field(
            'table_striped',
            __('Striped Rows', 'fd-admin-ui'),
            array(__CLASS__, 'striped_callback'),
            'fd-admin-ui-settings-table',
            'fd_table_section'
        );

        add_settings_field(
            'table_compact',
            __('Compact Mode', 'fd-admin-ui'),
            array(__CLASS__, 'compact_callback'),
            'fd-admin-ui-settings-table',
            'fd_table_section'
        );
    }

    // ========================================
    // Callbacks
    // ========================================

    public static function section_callback() {
        echo '<p>' . esc_html__('Customize admin list tables (posts, users, etc.) appearance.', 'fd-admin-ui') . '</p>';
    }

    public static function modern_style_callback() {
        $options = self::get_options();
        $checked = isset($options['table_modern_style']) && $options['table_modern_style'] ? 'checked' : '';
        // Enable by default
        if (!isset($options['table_modern_style'])) {
            $checked = 'checked';
        }
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[table_modern_style]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable modern table styling with rounded borders, soft colors, optimized spacing, and improved interactions.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function horizontal_scroll_callback() {
        $options = self::get_options();
        $checked = isset($options['table_horizontal_scroll']) && $options['table_horizontal_scroll'] ? 'checked' : '';
        // Enable by default
        if (!isset($options['table_horizontal_scroll'])) {
            $checked = 'checked';
        }
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[table_horizontal_scroll]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable horizontal scrolling for tables with many columns, preventing layout distortion.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function row_hover_color_callback() {
        $options = self::get_options();
        $color = isset($options['table_row_hover_color']) ? $options['table_row_hover_color'] : '';
        ?>
        <input type="text" class="fd-color-picker" name="fd_admin_ui_options[table_row_hover_color]" value="<?php echo esc_attr($color); ?>" data-default-color="">
        <p class="description"><?php esc_html_e('Background color when hovering over table rows. Leave empty for no hover effect.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function striped_callback() {
        $options = self::get_options();
        $checked = isset($options['table_striped']) && $options['table_striped'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[table_striped]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable alternating row colors (zebra striping) for better readability.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function compact_callback() {
        $options = self::get_options();
        $checked = isset($options['table_compact']) && $options['table_compact'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[table_compact]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable compact mode to reduce row height and display more data on screen.', 'fd-admin-ui'); ?></p>
        <?php
    }
}
