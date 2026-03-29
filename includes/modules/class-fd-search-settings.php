<?php
/**
 * FD Admin UI - Search Settings Module
 * Manages global search settings
 */

defined('ABSPATH') || exit;

class FD_Search_Settings {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'search_enable' => true,
            'search_shortcut_key' => 'k',
            'search_scope' => array('posts', 'pages', 'media', 'menu_items'),
            'search_results_limit' => 10,
            'search_show_history' => true,
            'search_show_menu_button' => true,
        );
    }

    /**
     * Sanitize module options
     */
    public static function sanitize($input, $sanitized) {
        $sanitized['search_enable'] = isset($input['search_enable']) && $input['search_enable'];

        if (isset($input['search_shortcut_key'])) {
            $sanitized['search_shortcut_key'] = in_array($input['search_shortcut_key'], array('k', 'p'))
                ? $input['search_shortcut_key']
                : 'k';
        }

        if (isset($input['search_scope']) && is_array($input['search_scope'])) {
            $allowed_scopes = array('posts', 'pages', 'media', 'menu_items', 'users', 'plugins');
            $sanitized['search_scope'] = array_intersect($input['search_scope'], $allowed_scopes);
        }

        if (isset($input['search_results_limit'])) {
            $sanitized['search_results_limit'] = max(5, min(20, absint($input['search_results_limit'])));
        }

        $sanitized['search_show_history'] = isset($input['search_show_history']) && $input['search_show_history'];
        $sanitized['search_show_menu_button'] = isset($input['search_show_menu_button']) && $input['search_show_menu_button'];

        return $sanitized;
    }

    /**
     * Output module CSS (search is handled by JS, no extra CSS needed)
     */
    public static function output_styles($options) {
        // Search functionality is implemented via JS, no additional CSS needed
    }

    /**
     * Get options (from main class)
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Register search settings
     */
    public static function register_settings() {
        // Global search settings
        add_settings_section(
            'fd_search_section',
            __('Global Search Settings', 'fd-admin-ui'),
            array(__CLASS__, 'section_callback'),
            'fd-admin-ui-settings-search'
        );

        add_settings_field(
            'search_enable',
            __('Enable Global Search', 'fd-admin-ui'),
            array(__CLASS__, 'enable_callback'),
            'fd-admin-ui-settings-search',
            'fd_search_section'
        );

        add_settings_field(
            'search_shortcut_key',
            __('Shortcut Key', 'fd-admin-ui'),
            array(__CLASS__, 'shortcut_key_callback'),
            'fd-admin-ui-settings-search',
            'fd_search_section'
        );

        add_settings_field(
            'search_scope',
            __('Search Scope', 'fd-admin-ui'),
            array(__CLASS__, 'scope_callback'),
            'fd-admin-ui-settings-search',
            'fd_search_section'
        );

        add_settings_field(
            'search_results_limit',
            __('Results Per Category', 'fd-admin-ui'),
            array(__CLASS__, 'results_limit_callback'),
            'fd-admin-ui-settings-search',
            'fd_search_section'
        );

        add_settings_field(
            'search_show_history',
            __('Show Search History', 'fd-admin-ui'),
            array(__CLASS__, 'show_history_callback'),
            'fd-admin-ui-settings-search',
            'fd_search_section'
        );

        add_settings_field(
            'search_show_menu_button',
            __('Show Menu Search Button', 'fd-admin-ui'),
            array(__CLASS__, 'show_menu_button_callback'),
            'fd-admin-ui-settings-search',
            'fd_search_section'
        );
    }

    // ========================================
    // Callbacks
    // ========================================

    public static function section_callback() {
        echo '<p>' . esc_html__('Configure the global search feature for quickly finding and accessing admin content. A Notion-like Cmd/Ctrl + K search experience.', 'fd-admin-ui') . '</p>';
    }

    public static function enable_callback() {
        $options = self::get_options();
        $checked = isset($options['search_enable']) && $options['search_enable'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[search_enable]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable keyboard shortcut to quickly search admin content.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function shortcut_key_callback() {
        $options = self::get_options();
        $value = isset($options['search_shortcut_key']) ? $options['search_shortcut_key'] : 'k';
        ?>
        <select name="fd_admin_ui_options[search_shortcut_key]" class="fd-form-input">
            <option value="k" <?php selected($value, 'k'); ?>>Cmd/Ctrl + K</option>
            <option value="p" <?php selected($value, 'p'); ?>>Cmd/Ctrl + P</option>
        </select>
        <p class="description"><?php esc_html_e('Choose the keyboard shortcut to trigger the search.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function scope_callback() {
        $options = self::get_options();
        $scope = isset($options['search_scope']) ? $options['search_scope'] : array('posts', 'pages', 'media', 'menu_items');

        $available_scopes = array(
            'posts'      => __('Posts', 'fd-admin-ui'),
            'pages'      => __('Pages', 'fd-admin-ui'),
            'media'      => __('Media', 'fd-admin-ui'),
            'menu_items' => __('Menu Items', 'fd-admin-ui'),
            'users'      => __('Users', 'fd-admin-ui'),
            'plugins'    => __('Plugins', 'fd-admin-ui'),
        );
        ?>
        <fieldset>
            <?php foreach ($available_scopes as $key => $label): ?>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox"
                           name="fd_admin_ui_options[search_scope][]"
                           value="<?php echo esc_attr($key); ?>"
                           <?php checked(in_array($key, $scope)); ?>>
                    <?php echo esc_html($label); ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <p class="description"><?php esc_html_e('Select the content types to include in search results.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function results_limit_callback() {
        $options = self::get_options();
        $value = isset($options['search_results_limit']) ? $options['search_results_limit'] : 10;
        ?>
        <input type="number"
               name="fd_admin_ui_options[search_results_limit]"
               value="<?php echo esc_attr($value); ?>"
               class="fd-form-input"
               min="5"
               max="20"
               step="1">
        <p class="description"><?php esc_html_e('Maximum number of results per category (5-20).', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function show_history_callback() {
        $options = self::get_options();
        $checked = isset($options['search_show_history']) && $options['search_show_history'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[search_show_history]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Show recent search history in the search modal.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function show_menu_button_callback() {
        $options = self::get_options();
        $checked = isset($options['search_show_menu_button']) && $options['search_show_menu_button'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[search_show_menu_button]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Show a search button at the top of the sidebar menu, similar to Notion\'s search entry.', 'fd-admin-ui'); ?></p>
        <?php
    }
}
