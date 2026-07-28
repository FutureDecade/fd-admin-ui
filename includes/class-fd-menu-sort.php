<?php
/**
 * Lingcoo Admin UI - Menu Sort
 * Menu drag-and-drop sorting functionality
 */

defined('ABSPATH') || exit;

class FD_Menu_Sort {

    /**
     * Initialize
     */
    public static function init() {
        // Register AJAX handlers
        add_action('wp_ajax_fd_save_menu_order', array(__CLASS__, 'ajax_save_menu_order'));
        add_action('wp_ajax_fd_save_submenu_order', array(__CLASS__, 'ajax_save_submenu_order'));
        add_action('wp_ajax_fd_reset_menu_order', array(__CLASS__, 'ajax_reset_menu_order'));
        add_action('wp_ajax_fd_add_separator', array(__CLASS__, 'ajax_add_separator'));
        add_action('wp_ajax_fd_remove_separator', array(__CLASS__, 'ajax_remove_separator'));

        // Apply custom menu order and separators - use late priority to ensure all menus are registered
        add_action('admin_menu', array(__CLASS__, 'apply_custom_menu_order'), 9999);
        add_action('admin_menu', array(__CLASS__, 'apply_custom_submenu_order'), 9999);

        // Output drag-and-drop sorting script and styles
        add_action('admin_footer', array(__CLASS__, 'output_sortable_script'));
    }

    /**
     * Get default top-level menu order.
     */
    public static function get_default_menu_order() {
        return array(
            'fd-ai-hub',
            'index.php',
            'separator1',
            'edit.php',
            'edit.php?post_type=page',
            'edit.php?post_type=note',
            'edit.php?post_type=book',
            'edit.php?post_type=app',
            'edit.php?post_type=event',
            'fd-separator-media',
            'upload.php',
            'gf_edit_forms',
            'fd-separator-commerce',
            'edit.php?post_type=product',
            'fd-payment-settings',
            'fd-separator-membership',
            'fd-member',
            'users.php',
            'edit-comments.php',
            'fd-separator-theme',
            'fd-theme-settings',
            'edit.php?post_type=acf-field-group',
            'fd-separator-system',
            'options-general.php',
            'themes.php',
            'plugins.php',
            'tools.php',
            'graphiql-ide',
        );
    }

    /**
     * Get default submenu order.
     */
    public static function get_default_submenu_order() {
        return array(
            'options-general.php' => array(
                'options-general.php',
                'options-writing.php',
                'options-reading.php',
                'options-discussion.php',
                'options-media.php',
                'options-permalink.php',
                'options-privacy.php',
                'options-general.php?page=fluent-mail',
                'options-general.php?page=kodo-qiniu/qiniu-kodo-wordpress.php',
                'options-general.php?page=redis-cache',
                'options-general.php?page=fd-admin-ui-settings',
            ),
            'tools.php' => array(
                'tools.php',
                'import.php',
                'export.php',
                'site-health.php',
                'export-personal-data.php',
                'erase-personal-data.php',
                'action-scheduler',
                'fd-grabweixin-settings',
                'fd-debug-tools',
                'fd-websocket-events',
            ),
        );
    }

    /**
     * Get default custom separators.
     */
    public static function get_default_custom_separators() {
        return array(
            'fd-separator-media',
            'fd-separator-commerce',
            'fd-separator-membership',
            'fd-separator-theme',
            'fd-separator-system',
        );
    }

    /**
     * Apply custom menu order
     */
    public static function apply_custom_menu_order() {
        global $menu;

        $custom_order = get_option('fd_admin_menu_order', array());
        $custom_separators = get_option('fd_admin_custom_separators', array());

        if (empty($custom_order) || empty($menu)) {
            return;
        }

        // Create menu slug to menu item mapping
        $menu_by_slug = array();
        $separators = array();
        $separator_index = 0;

        foreach ($menu as $position => $item) {
            if (empty($item[0]) && isset($item[4]) && strpos($item[4], 'wp-menu-separator') !== false) {
                // Separator
                $sep_id = isset($item[2]) ? $item[2] : 'separator' . $separator_index;
                $separators[$sep_id] = $item;
                $separator_index++;
            } else {
                // Regular menu item - use slug (index 2)
                $slug = isset($item[2]) ? $item[2] : '';
                if ($slug) {
                    $menu_by_slug[$slug] = $item;
                }
            }
        }

        // Build new menu array
        $new_menu = array();
        $position = 1;

        foreach ($custom_order as $slug) {
            // Check if it is a custom separator (fd-separator-xxx)
            if (strpos($slug, 'fd-separator-') === 0) {
                // Custom separator - create a new separator
                $new_menu[$position] = array(
                    '',                          // 0: title
                    'read',                      // 1: capability
                    $slug,                       // 2: slug
                    '',                          // 3: page title
                    'wp-menu-separator fd-custom-separator', // 4: CSS class
                );
                $position++;
            } else if (strpos($slug, 'separator') === 0) {
                // WordPress native separator - try multiple matching methods
                $found = false;
                foreach ($separators as $sep_id => $sep_item) {
                    if ($sep_id === $slug) {
                        $new_menu[$position] = $sep_item;
                        unset($separators[$sep_id]);
                        $position++;
                        $found = true;
                        break;
                    }
                }
                // If no exact match found, try using the first available separator
                if (!$found && !empty($separators)) {
                    $first_sep = array_keys($separators)[0];
                    $new_menu[$position] = $separators[$first_sep];
                    unset($separators[$first_sep]);
                    $position++;
                }
            } else {
                // Regular menu item - try multiple matching methods
                $found_key = null;

                // 1. Exact match
                if (isset($menu_by_slug[$slug])) {
                    $found_key = $slug;
                }

                // 2. If slug is a relative path, try matching
                if (!$found_key) {
                    foreach ($menu_by_slug as $key => $item) {
                        // Check if contains page= parameter match
                        if (strpos($key, 'page=') !== false && strpos($slug, 'page=') !== false) {
                            // Extract page parameter values for comparison
                            preg_match('/page=([^&]+)/', $key, $key_match);
                            preg_match('/page=([^&]+)/', $slug, $slug_match);
                            if (!empty($key_match[1]) && !empty($slug_match[1]) && $key_match[1] === $slug_match[1]) {
                                $found_key = $key;
                                break;
                            }
                        }

                        // Check if slug is part of key or vice versa
                        if (strpos($key, $slug) !== false || strpos($slug, $key) !== false) {
                            $found_key = $key;
                            break;
                        }
                    }
                }

                if ($found_key) {
                    $new_menu[$position] = $menu_by_slug[$found_key];
                    unset($menu_by_slug[$found_key]);
                    $position++;
                }
            }
        }

        // Add menu items not in the custom order (newly installed plugins, etc.)
        foreach ($menu_by_slug as $item) {
            $new_menu[$position] = $item;
            $position++;
        }

        // Add unused separators
        foreach ($separators as $item) {
            $new_menu[$position] = $item;
            $position++;
        }

        // Replace global menu
        $menu = $new_menu;
    }

    /**
     * Apply custom submenu order
     */
    public static function apply_custom_submenu_order() {
        global $submenu;

        $custom_submenu_order = get_option('fd_admin_submenu_order', array());

        if (empty($custom_submenu_order) || empty($submenu)) {
            return;
        }

        // Iterate over each parent menu's submenu
        foreach ($custom_submenu_order as $saved_parent_slug => $sub_order) {
            if (empty($sub_order)) {
                continue;
            }

            // Find the corresponding $submenu key (may need fuzzy matching)
            $actual_parent_slug = null;

            // 1. Exact match
            if (isset($submenu[$saved_parent_slug])) {
                $actual_parent_slug = $saved_parent_slug;
            }

            // 2. Match after URL decoding
            if (!$actual_parent_slug) {
                $decoded_saved = urldecode($saved_parent_slug);
                foreach ($submenu as $key => $items) {
                    if (urldecode($key) === $decoded_saved) {
                        $actual_parent_slug = $key;
                        break;
                    }
                }
            }

            // 3. Partial match (handle different query parameter order, etc.)
            if (!$actual_parent_slug) {
                foreach ($submenu as $key => $items) {
                    // Check containment relationship
                    if (strpos($key, $saved_parent_slug) !== false || strpos($saved_parent_slug, $key) !== false) {
                        $actual_parent_slug = $key;
                        break;
                    }

                    // Check if page parameter is the same
                    preg_match('/[?&]page=([^&]+)/', $key, $key_match);
                    preg_match('/[?&]page=([^&]+)/', $saved_parent_slug, $saved_match);
                    if (!empty($key_match[1]) && !empty($saved_match[1]) && $key_match[1] === $saved_match[1]) {
                        $actual_parent_slug = $key;
                        break;
                    }
                }
            }

            if (!$actual_parent_slug || !isset($submenu[$actual_parent_slug])) {
                continue;
            }

            // Create submenu slug to submenu item mapping (supports multiple format matching)
            $submenu_by_slug = array();
            $submenu_items = $submenu[$actual_parent_slug];

            foreach ($submenu_items as $item) {
                $slug = isset($item[2]) ? $item[2] : '';
                if ($slug) {
                    $submenu_by_slug[$slug] = $item;
                }
            }

            // Build new submenu array
            $new_submenu = array();
            $position = 0;

            foreach ($sub_order as $saved_slug) {
                $found_key = null;

                // 1. Exact match
                if (isset($submenu_by_slug[$saved_slug])) {
                    $found_key = $saved_slug;
                }

                // 2. Try matching (handle URL encoding differences, etc.)
                if (!$found_key) {
                    $decoded_saved = urldecode($saved_slug);
                    foreach ($submenu_by_slug as $key => $item) {
                        $decoded_key = urldecode($key);

                        // Full match (after decoding)
                        if ($decoded_key === $decoded_saved) {
                            $found_key = $key;
                            break;
                        }

                        // Check if one contains the other (handle different query parameter order)
                        if (strpos($decoded_key, $decoded_saved) !== false ||
                            strpos($decoded_saved, $decoded_key) !== false) {
                            $found_key = $key;
                            break;
                        }

                        // Extract page parameter for comparison
                        preg_match('/[?&]page=([^&]+)/', $key, $key_match);
                        preg_match('/[?&]page=([^&]+)/', $saved_slug, $saved_match);
                        if (!empty($key_match[1]) && !empty($saved_match[1]) &&
                            $key_match[1] === $saved_match[1]) {
                            $found_key = $key;
                            break;
                        }
                    }
                }

                if ($found_key) {
                    $new_submenu[$position] = $submenu_by_slug[$found_key];
                    unset($submenu_by_slug[$found_key]);
                    $position++;
                }
            }

            // Add submenu items not in the custom order
            foreach ($submenu_by_slug as $item) {
                $new_submenu[$position] = $item;
                $position++;
            }

            // Replace this parent menu's submenu
            $submenu[$actual_parent_slug] = $new_submenu;
        }
    }

    /**
     * AJAX: Save menu order
     */
    public static function ajax_save_menu_order() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fd_menu_sort_nonce')) {
            wp_send_json_error(__('Security verification failed', 'fd-admin-ui'));
        }

        // Verify permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'fd-admin-ui'));
        }

        // Get menu order
        if (!isset($_POST['menu_order']) || !is_array($_POST['menu_order'])) {
            wp_send_json_error(__('Invalid menu order data', 'fd-admin-ui'));
        }

        $menu_order = array_map('sanitize_text_field', wp_unslash( $_POST['menu_order'] ));

        // Save to wp_option
        update_option('fd_admin_menu_order', $menu_order);

        wp_send_json_success(array(
            'message' => __('Menu order saved', 'fd-admin-ui'),
            'order' => $menu_order
        ));
    }

    /**
     * AJAX: Save submenu order
     */
    public static function ajax_save_submenu_order() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fd_menu_sort_nonce')) {
            wp_send_json_error(__('Security verification failed', 'fd-admin-ui'));
        }

        // Verify permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'fd-admin-ui'));
        }

        // Get parent slug and submenu order
        if (!isset($_POST['parent_slug']) || !isset($_POST['submenu_order']) || !is_array($_POST['submenu_order'])) {
            wp_send_json_error(__('Invalid submenu order data', 'fd-admin-ui'));
        }

        $parent_slug = sanitize_text_field( wp_unslash( $_POST['parent_slug'] ) );
        $submenu_order = array_map('sanitize_text_field', wp_unslash( $_POST['submenu_order'] ));

        // Get existing submenu order settings
        $all_submenu_order = get_option('fd_admin_submenu_order', array());

        // Update this parent menu's submenu order
        $all_submenu_order[$parent_slug] = $submenu_order;

        // Save to wp_option
        update_option('fd_admin_submenu_order', $all_submenu_order);

        wp_send_json_success(array(
            'message' => __('Submenu order saved', 'fd-admin-ui'),
            'parent_slug' => $parent_slug,
            'order' => $submenu_order
        ));
    }

    /**
     * AJAX: Reset menu order
     */
    public static function ajax_reset_menu_order() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fd_menu_sort_nonce')) {
            wp_send_json_error(__('Security verification failed', 'fd-admin-ui'));
        }

        // Verify permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'fd-admin-ui'));
        }

        update_option('fd_admin_menu_order', self::get_default_menu_order());
        update_option('fd_admin_submenu_order', self::get_default_submenu_order());
        update_option('fd_admin_custom_separators', self::get_default_custom_separators());

        wp_send_json_success(array(
            'message' => __('Menu order has been reset', 'fd-admin-ui')
        ));
    }

    /**
     * AJAX: Add separator
     */
    public static function ajax_add_separator() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fd_menu_sort_nonce')) {
            wp_send_json_error(__('Security verification failed', 'fd-admin-ui'));
        }

        // Verify permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'fd-admin-ui'));
        }

        // Get which menu item to add separator after
        if (!isset($_POST['after_slug'])) {
            wp_send_json_error(__('Missing parameter', 'fd-admin-ui'));
        }

        $after_slug = sanitize_text_field( wp_unslash( $_POST['after_slug'] ) );

        // Get current menu order
        $menu_order = get_option('fd_admin_menu_order', array());

        if (empty($menu_order)) {
            // If no custom order yet, get current menu order first
            global $menu;
            $menu_order = array();
            foreach ($menu as $item) {
                if (isset($item[2])) {
                    $menu_order[] = $item[2];
                }
            }
        }

        // Generate new separator ID
        $separator_id = 'fd-separator-' . time() . '-' . wp_rand(1000, 9999);

        // Insert separator after specified position
        $new_order = array();
        foreach ($menu_order as $slug) {
            $new_order[] = $slug;
            if ($slug === $after_slug) {
                $new_order[] = $separator_id;
            }
        }

        // Save new order
        update_option('fd_admin_menu_order', $new_order);

        // Record custom separator
        $custom_separators = get_option('fd_admin_custom_separators', array());
        $custom_separators[] = $separator_id;
        update_option('fd_admin_custom_separators', $custom_separators);

        wp_send_json_success(array(
            'message' => __('Separator added', 'fd-admin-ui'),
            'separator_id' => $separator_id,
            'menu_order' => $new_order
        ));
    }

    /**
     * AJAX: Remove separator
     */
    public static function ajax_remove_separator() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fd_menu_sort_nonce')) {
            wp_send_json_error(__('Security verification failed', 'fd-admin-ui'));
        }

        // Verify permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'fd-admin-ui'));
        }

        if (!isset($_POST['separator_id'])) {
            wp_send_json_error(__('Missing parameter', 'fd-admin-ui'));
        }

        $separator_id = sanitize_text_field( wp_unslash( $_POST['separator_id'] ) );

        // Remove from menu order
        $menu_order = get_option('fd_admin_menu_order', array());
        $menu_order = array_filter($menu_order, function($slug) use ($separator_id) {
            return $slug !== $separator_id;
        });
        $menu_order = array_values($menu_order); // Re-index
        update_option('fd_admin_menu_order', $menu_order);

        // Remove from custom separators list
        $custom_separators = get_option('fd_admin_custom_separators', array());
        $custom_separators = array_filter($custom_separators, function($id) use ($separator_id) {
            return $id !== $separator_id;
        });
        $custom_separators = array_values($custom_separators);
        update_option('fd_admin_custom_separators', $custom_separators);

        wp_send_json_success(array(
            'message' => __('Separator removed', 'fd-admin-ui'),
            'menu_order' => $menu_order
        ));
    }

    /**
     * Get current menu slug mapping (for JS-side matching)
     */
    public static function get_menu_slugs() {
        global $menu;

        $slugs = array();

        if (!empty($menu)) {
            foreach ($menu as $position => $item) {
                if (empty($item[0]) && isset($item[4]) && strpos($item[4], 'wp-menu-separator') !== false) {
                    // Separator
                    $sep_id = isset($item[2]) ? $item[2] : 'separator' . count($slugs);
                    $slugs[] = array(
                        'type' => 'separator',
                        'slug' => $sep_id,
                        'position' => $position
                    );
                } else if (isset($item[2]) && !empty($item[2])) {
                    // Regular menu item
                    $slugs[] = array(
                        'type' => 'menu',
                        'slug' => $item[2],
                        'title' => wp_strip_all_tags($item[0]),
                        'position' => $position
                    );
                }
            }
        }

        return $slugs;
    }

    /**
     * Get submenu slug mapping (for JS-side matching)
     */
    public static function get_submenu_slugs() {
        global $submenu;

        $result = array();

        if (!empty($submenu)) {
            foreach ($submenu as $parent_slug => $items) {
                $result[$parent_slug] = array();
                foreach ($items as $item) {
                    if (isset($item[2]) && !empty($item[2])) {
                        $result[$parent_slug][] = $item[2];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Output drag-and-drop sorting script and styles
     */
    public static function output_sortable_script() {
        // Check if menu sorting is enabled
        $options = FD_Admin_UI_Settings::get_options();
        if (!isset($options['menu_sort_enable']) || !$options['menu_sort_enable']) {
            return;
        }

        // Only administrators can sort
        if (!current_user_can('manage_options')) {
            return;
        }

        $nonce = wp_create_nonce('fd_menu_sort_nonce');

        // Get PHP-side menu slug list
        $menu_slugs = self::get_menu_slugs();

        // Get PHP-side submenu slug list
        $submenu_slugs = self::get_submenu_slugs();

        // Localize strings for JavaScript
        $js_strings = array(
            'sortModeEnabled'     => __('Menu sort mode enabled', 'fd-admin-ui'),
            'dragToReorder'       => __('Drag menu items to reorder', 'fd-admin-ui'),
            'menuOrderSaved'      => __('Menu order saved', 'fd-admin-ui'),
            'saveFailed'          => __('Save failed: ', 'fd-admin-ui'),
            'networkError'        => __('Network error, save failed', 'fd-admin-ui'),
            'addSeparator'        => __('Add separator', 'fd-admin-ui'),
            'removeSeparator'     => __('Remove separator', 'fd-admin-ui'),
            'separatorAdded'      => __('Separator added', 'fd-admin-ui'),
            'addFailed'           => __('Add failed: ', 'fd-admin-ui'),
            'separatorRemoved'    => __('Separator removed', 'fd-admin-ui'),
            'removeFailed'        => __('Remove failed: ', 'fd-admin-ui'),
            'cannotDeterminePos'  => __('Cannot determine position', 'fd-admin-ui'),
            'cannotDetermineSep'  => __('Cannot determine separator', 'fd-admin-ui'),
            'networkErrorShort'   => __('Network error', 'fd-admin-ui'),
            'submenuOrderSaved'   => __('Submenu order saved', 'fd-admin-ui'),
        );
        ?>
        <style id="fd-menu-sortable-styles">
            /* Drag-and-drop sort mode - menu item styles */
            body.fd-menu-sortable #adminmenu li.menu-top {
                cursor: grab;
                transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
                position: relative;
            }

            body.fd-menu-sortable #adminmenu li.menu-top:active {
                cursor: grabbing;
            }

            /* Dragging element */
            body.fd-menu-sortable #adminmenu li.menu-top.ui-sortable-helper {
                background: rgba(255, 255, 255, 0.15) !important;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3) !important;
                transform: scale(1.02);
                z-index: 9999 !important;
                border-radius: 4px;
            }

            /* Placeholder style */
            body.fd-menu-sortable #adminmenu li.fd-sortable-placeholder {
                background: rgba(255, 255, 255, 0.08) !important;
                border: 2px dashed rgba(255, 255, 255, 0.3) !important;
                border-radius: 4px;
                margin: 2px 0;
                visibility: visible !important;
                height: 36px !important;
            }

            /* Hide placeholder content */
            body.fd-menu-sortable #adminmenu li.fd-sortable-placeholder * {
                visibility: hidden !important;
            }

            /* Hover visual hint */
            body.fd-menu-sortable #adminmenu li.menu-top:hover:not(.ui-sortable-helper) {
                background: rgba(255, 255, 255, 0.05);
            }

            /* Separators are also draggable */
            body.fd-menu-sortable #adminmenu li.wp-menu-separator {
                cursor: grab;
                transition: background 0.15s ease;
                position: relative;
            }

            body.fd-menu-sortable #adminmenu li.wp-menu-separator:hover {
                background: rgba(255, 255, 255, 0.1) !important;
            }

            /* ===== Separator management buttons ===== */

            /* Add separator trigger zone - below each menu item */
            #adminmenu li.fd-separator-add-zone {
                height: 0;
                position: relative;
                overflow: visible;
                transition: height 0.2s ease;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                list-style: none !important;
            }

            #adminmenu li.fd-separator-add-zone:hover {
                height: 20px;
            }

            /* Add separator button */
            .fd-separator-add-btn {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 20px;
                height: 20px;
                background: #2271b1;
                border: none;
                border-radius: 50%;
                color: #fff;
                font-size: 16px;
                line-height: 18px;
                text-align: center;
                cursor: pointer;
                opacity: 0;
                transition: opacity 0.2s ease, transform 0.15s ease;
                z-index: 100;
                padding: 0;
            }

            #adminmenu li.fd-separator-add-zone:hover .fd-separator-add-btn {
                opacity: 1;
            }

            .fd-separator-add-btn:hover {
                background: #135e96;
                transform: translate(-50%, -50%) scale(1.1);
            }

            /* Dashed line shown on hover */
            #adminmenu li.fd-separator-add-zone::before {
                content: '';
                position: absolute;
                left: 10px;
                right: 10px;
                top: 50%;
                height: 0;
                border-top: 2px dashed rgba(255, 255, 255, 0.3);
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            #adminmenu li.fd-separator-add-zone:hover::before {
                opacity: 1;
            }

            /* Remove button on separator */
            .fd-separator-remove-btn {
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                width: 18px;
                height: 18px;
                background: #d63638;
                border: none;
                border-radius: 50%;
                color: #fff;
                font-size: 14px;
                line-height: 16px;
                text-align: center;
                cursor: pointer;
                opacity: 0;
                transition: opacity 0.2s ease, transform 0.15s ease;
                z-index: 100;
                padding: 0;
            }

            body.fd-menu-sortable #adminmenu li.wp-menu-separator:hover .fd-separator-remove-btn {
                opacity: 1;
            }

            .fd-separator-remove-btn:hover {
                background: #a10005;
                transform: translateY(-50%) scale(1.1);
            }

            /* Sort mode indicator bar */
            #fd-sort-mode-indicator {
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: #1d2327;
                color: #fff;
                padding: 12px 24px;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                z-index: 99999;
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 14px;
            }

            #fd-sort-mode-indicator .dashicons {
                font-size: 18px;
                width: 18px;
                height: 18px;
                color: #72aee6;
            }

            #fd-sort-mode-indicator .fd-sort-text {
                color: #f0f0f1;
            }

            #fd-sort-mode-indicator .fd-sort-hint {
                color: #a7aaad;
                font-size: 12px;
            }

            /* Site branding area is not draggable */
            body.fd-menu-sortable #fd-site-branding {
                cursor: default !important;
            }

            /* Search button area is not draggable */
            body.fd-menu-sortable #fd-search-trigger {
                cursor: pointer !important;
            }

            /* Hide add zones while dragging */
            body.fd-menu-sortable.ui-sortable-helper-active #adminmenu li.fd-separator-add-zone {
                display: none !important;
            }

            /* ===== Submenu sorting styles ===== */

            /* Submenu items are draggable */
            body.fd-menu-sortable #adminmenu .wp-submenu li {
                cursor: grab;
                transition: background 0.15s ease, transform 0.15s ease;
                position: relative;
            }

            body.fd-menu-sortable #adminmenu .wp-submenu li:active {
                cursor: grabbing;
            }

            /* Submenu dragging element */
            body.fd-menu-sortable #adminmenu .wp-submenu li.ui-sortable-helper {
                background: rgba(255, 255, 255, 0.15) !important;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
                z-index: 99999 !important;
                border-radius: 3px;
            }

            /* Submenu placeholder style */
            body.fd-menu-sortable #adminmenu .wp-submenu li.fd-submenu-placeholder {
                background: rgba(255, 255, 255, 0.08) !important;
                border: 1px dashed rgba(255, 255, 255, 0.3) !important;
                border-radius: 3px;
                visibility: visible !important;
                height: 28px !important;
            }

            /* Hide submenu placeholder content */
            body.fd-menu-sortable #adminmenu .wp-submenu li.fd-submenu-placeholder * {
                visibility: hidden !important;
            }

            /* Submenu hover visual hint */
            body.fd-menu-sortable #adminmenu .wp-submenu li:hover:not(.ui-sortable-helper) {
                background: rgba(255, 255, 255, 0.08);
            }

            /* Submenu header (parent menu name) is not draggable */
            body.fd-menu-sortable #adminmenu .wp-submenu li.wp-submenu-head {
                cursor: default !important;
            }

            /* Submenu sort hint icon */
            body.fd-menu-sortable #adminmenu .wp-submenu li:not(.wp-submenu-head)::before {
                content: '\f545';
                font-family: dashicons;
                position: absolute;
                left: 8px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 12px;
                color: rgba(255, 255, 255, 0.3);
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            body.fd-menu-sortable #adminmenu .wp-submenu li:not(.wp-submenu-head):hover::before {
                opacity: 1;
            }

            /* Adjust submenu item padding to accommodate sort icon */
            body.fd-menu-sortable #adminmenu .wp-submenu li:not(.wp-submenu-head) a {
                padding-left: 24px !important;
            }
        </style>

        <script id="fd-menu-sortable-script">
        jQuery(document).ready(function($) {
            // Localized strings
            var fdSortStrings = <?php echo json_encode($js_strings); ?>;

            // PHP-side menu slug mapping
            var phpMenuSlugs = <?php echo json_encode($menu_slugs); ?>;

            // PHP-side submenu slug mapping (parent_slug => [submenu_slugs])
            var phpSubmenuSlugs = <?php echo json_encode($submenu_slugs); ?>;

            // Create slug lookup map
            var slugMap = {};
            phpMenuSlugs.forEach(function(item) {
                slugMap[item.slug] = item;
            });

            // Add data-menu-slug attribute to each menu item
            // Match the corresponding PHP slug by matching href
            var separatorIndex = 0;

            $('#adminmenu > li.menu-top, #adminmenu > li.wp-menu-separator').each(function() {
                var $item = $(this);

                // Skip non-menu elements like site branding and search button
                if ($item.attr('id') === 'fd-site-branding' || $item.attr('id') === 'fd-search-trigger') {
                    return;
                }

                var foundSlug = '';

                if ($item.hasClass('wp-menu-separator')) {
                    // Separator - assign in order
                    var sepCount = 0;
                    for (var i = 0; i < phpMenuSlugs.length; i++) {
                        if (phpMenuSlugs[i].type === 'separator') {
                            if (sepCount === separatorIndex) {
                                foundSlug = phpMenuSlugs[i].slug;
                                break;
                            }
                            sepCount++;
                        }
                    }
                    separatorIndex++;
                } else {
                    // Regular menu item - match by href
                    var $link = $item.find('> a.menu-top');
                    var href = $link.attr('href') || '';

                    // Iterate PHP slugs to find match
                    for (var i = 0; i < phpMenuSlugs.length; i++) {
                        var phpItem = phpMenuSlugs[i];
                        if (phpItem.type !== 'menu') continue;

                        var slug = phpItem.slug;

                        // Multiple matching methods
                        if (
                            // Exact match
                            href === slug ||
                            // href ends with slug (e.g. /wp-admin/edit.php matches edit.php)
                            href.endsWith('/' + slug) ||
                            href.endsWith(slug) ||
                            // page= parameter match
                            href.indexOf('page=' + slug) > -1 ||
                            // For plugin pages, slug may be in admin.php?page=xxx format
                            (slug.indexOf('admin.php?page=') === 0 && href.indexOf(slug.replace('admin.php?page=', 'page=')) > -1) ||
                            // Handle full URLs
                            (href.indexOf('/wp-admin/') > -1 && href.split('/wp-admin/').pop().split('#')[0] === slug)
                        ) {
                            foundSlug = slug;
                            break;
                        }
                    }

                    // If still not found, try extracting relative path as slug
                    if (!foundSlug && href) {
                        var adminUrl = '<?php echo esc_url( admin_url() ); ?>';
                        var relativeHref = href;

                        if (href.indexOf(adminUrl) === 0) {
                            relativeHref = href.substring(adminUrl.length);
                        } else if (href.indexOf('/wp-admin/') > -1) {
                            relativeHref = href.split('/wp-admin/').pop();
                        }

                        // Remove anchor
                        relativeHref = relativeHref.split('#')[0];

                        // Check if this relative path is in PHP slugs
                        for (var i = 0; i < phpMenuSlugs.length; i++) {
                            if (phpMenuSlugs[i].type === 'menu' && phpMenuSlugs[i].slug === relativeHref) {
                                foundSlug = relativeHref;
                                break;
                            }
                        }

                        // If still not found, use the relative path directly
                        if (!foundSlug) {
                            foundSlug = relativeHref;
                        }
                    }
                }

                if (foundSlug) {
                    $item.attr('data-menu-slug', foundSlug);
                }
            });

            // Add sort mode class
            $('body').addClass('fd-menu-sortable');

            // Add bottom indicator bar
            var indicator = $('<div id="fd-sort-mode-indicator">' +
                '<span class="dashicons dashicons-sort"></span>' +
                '<span class="fd-sort-text">' + fdSortStrings.sortModeEnabled + '</span>' +
                '<span class="fd-sort-hint">' + fdSortStrings.dragToReorder + '</span>' +
                '</div>');
            $('body').append(indicator);

            // Auto-hide indicator bar after 3 seconds
            setTimeout(function() {
                indicator.fadeOut(500, function() {
                    $(this).remove();
                });
            }, 3000);

            // Initialize sortable
            $('#adminmenu').sortable({
                items: '> li.menu-top, > li.wp-menu-separator:not(.fd-separator-add-zone)',
                axis: 'y',
                containment: '#adminmenu',
                tolerance: 'pointer',
                placeholder: 'fd-sortable-placeholder menu-top',
                forcePlaceholderSize: true,
                opacity: 0.9,
                revert: 150,
                delay: 100,
                distance: 5,

                // Exclude non-sortable elements
                cancel: '#fd-site-branding, #fd-search-trigger, .wp-submenu, .wp-submenu-wrap, .fd-separator-add-zone, .fd-separator-add-btn, .fd-separator-remove-btn',

                start: function(event, ui) {
                    // Set placeholder height to match dragged element
                    ui.placeholder.height(ui.item.outerHeight());

                    // Close all open submenus
                    $('#adminmenu li.opensub').removeClass('opensub');
                    $('#adminmenu .wp-submenu').hide();
                },

                stop: function(event, ui) {
                    // Restore submenu display
                    $('#adminmenu .wp-has-current-submenu .wp-submenu').show();
                },

                update: function(event, ui) {
                    // Get new menu order - directly from data-menu-slug attribute
                    var menuOrder = [];

                    $('#adminmenu > li.menu-top, #adminmenu > li.wp-menu-separator').each(function() {
                        var $item = $(this);

                        // Skip add zones
                        if ($item.hasClass('fd-separator-add-zone')) {
                            return;
                        }

                        var menuSlug = $item.attr('data-menu-slug');

                        if (menuSlug) {
                            menuOrder.push(menuSlug);
                        }
                    });

                    // Save menu order
                    if (menuOrder.length > 0) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'fd_save_menu_order',
                                nonce: '<?php echo esc_attr( $nonce ); ?>',
                                menu_order: menuOrder
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Show save success notification
                                    showSaveNotification('success', fdSortStrings.menuOrderSaved);
                                } else {
                                    showSaveNotification('error', fdSortStrings.saveFailed + response.data);
                                }
                            },
                            error: function() {
                                showSaveNotification('error', fdSortStrings.networkError);
                            }
                        });
                    }
                }
            });

            // Show save notification
            function showSaveNotification(type, message) {
                var icons = {
                    success: 'dashicons-yes-alt',
                    error: 'dashicons-dismiss'
                };

                var colors = {
                    success: '#10b981',
                    error: '#ef4444'
                };

                var $notification = $('<div class="fd-save-notification" style="' +
                    'position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);' +
                    'background: #1d2327; color: #fff; padding: 12px 24px; border-radius: 8px;' +
                    'box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 99999; display: flex;' +
                    'align-items: center; gap: 10px; font-size: 14px;">' +
                    '<span class="dashicons ' + icons[type] + '" style="color: ' + colors[type] + ';"></span>' +
                    '<span>' + message + '</span>' +
                    '</div>');

                $('body').append($notification);

                setTimeout(function() {
                    $notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 2000);
            }

            // ===== Separator management functionality =====

            // Add "add separator" zone after each menu item
            function initSeparatorZones() {
                // Remove existing zones
                $('.fd-separator-add-zone').remove();

                // Add trigger zone after each regular menu item
                $('#adminmenu > li.menu-top').each(function() {
                    var $item = $(this);
                    var slug = $item.attr('data-menu-slug');

                    // Skip items without slug
                    if (!slug) return;

                    // Check if next element is a separator or add zone
                    var $next = $item.next();
                    while ($next.hasClass('fd-separator-add-zone')) {
                        $next = $next.next();
                    }
                    if ($next.hasClass('wp-menu-separator')) {
                        // Next is a separator, don't add the add zone
                        return;
                    }

                    // Create add zone (using li element to match ul structure)
                    var $zone = $('<li class="fd-separator-add-zone" data-after-slug="' + slug + '">' +
                        '<button type="button" class="fd-separator-add-btn" title="' + fdSortStrings.addSeparator + '">+</button>' +
                        '</li>');

                    $item.after($zone);
                });
            }

            // Add remove button to each separator
            function initSeparatorRemoveButtons() {
                // Remove existing buttons
                $('.fd-separator-remove-btn').remove();

                // Add remove button to each separator
                $('#adminmenu > li.wp-menu-separator').each(function() {
                    var $sep = $(this);
                    var slug = $sep.attr('data-menu-slug');

                    if (!slug) return;

                    var $btn = $('<button type="button" class="fd-separator-remove-btn" title="' + fdSortStrings.removeSeparator + '" data-separator-id="' + slug + '">\u2212</button>');
                    $sep.append($btn);
                });
            }

            // Add separator
            $(document).on('click', '.fd-separator-add-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var $zone = $(this).closest('.fd-separator-add-zone');
                var afterSlug = $zone.attr('data-after-slug');

                if (!afterSlug) {
                    showSaveNotification('error', fdSortStrings.cannotDeterminePos);
                    return;
                }

                // Send AJAX request
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'fd_add_separator',
                        nonce: '<?php echo esc_attr( $nonce ); ?>',
                        after_slug: afterSlug
                    },
                    success: function(response) {
                        if (response.success) {
                            showSaveNotification('success', fdSortStrings.separatorAdded);
                            // Refresh page to show new separator
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        } else {
                            showSaveNotification('error', fdSortStrings.addFailed + response.data);
                        }
                    },
                    error: function() {
                        showSaveNotification('error', fdSortStrings.networkErrorShort);
                    }
                });
            });

            // Remove separator
            $(document).on('click', '.fd-separator-remove-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var separatorId = $(this).attr('data-separator-id');

                if (!separatorId) {
                    showSaveNotification('error', fdSortStrings.cannotDetermineSep);
                    return;
                }

                // Send AJAX request
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'fd_remove_separator',
                        nonce: '<?php echo esc_attr( $nonce ); ?>',
                        separator_id: separatorId
                    },
                    success: function(response) {
                        if (response.success) {
                            showSaveNotification('success', fdSortStrings.separatorRemoved);
                            // Refresh page
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        } else {
                            showSaveNotification('error', fdSortStrings.removeFailed + response.data);
                        }
                    },
                    error: function() {
                        showSaveNotification('error', fdSortStrings.networkErrorShort);
                    }
                });
            });

            // Initialize separator management UI
            initSeparatorZones();
            initSeparatorRemoveButtons();

            // ===== Submenu sorting functionality =====

            // Add data-submenu-slug attribute to submenu items
            function initSubmenuSlugs() {
                var adminUrl = '<?php echo esc_url( admin_url() ); ?>';

                $('#adminmenu li.menu-top').each(function() {
                    var $parentItem = $(this);
                    var parentMenuSlug = $parentItem.attr('data-menu-slug');

                    if (!parentMenuSlug) return;

                    var $submenu = $parentItem.find('.wp-submenu');
                    if ($submenu.length === 0) return;

                    // Find the PHP-side parent menu slug ($submenu array key)
                    // This slug may not be exactly the same as data-menu-slug
                    var phpParentSlug = null;

                    // Try multiple matching methods to find the correct PHP parent menu slug
                    for (var pSlug in phpSubmenuSlugs) {
                        if (pSlug === parentMenuSlug) {
                            phpParentSlug = pSlug;
                            break;
                        }
                        // Compare after URL decoding
                        if (decodeURIComponent(pSlug) === decodeURIComponent(parentMenuSlug)) {
                            phpParentSlug = pSlug;
                            break;
                        }
                        // Check containment relationship
                        if (pSlug.indexOf(parentMenuSlug) !== -1 || parentMenuSlug.indexOf(pSlug) !== -1) {
                            phpParentSlug = pSlug;
                            break;
                        }
                    }

                    // If not found, use the original slug
                    if (!phpParentSlug) {
                        phpParentSlug = parentMenuSlug;
                    }

                    // Store PHP parent menu slug on submenu (this is the key used for saving and matching)
                    $submenu.attr('data-parent-slug', phpParentSlug);

                    // Get PHP-side submenu slug list
                    var phpSubSlugs = phpSubmenuSlugs[phpParentSlug] || [];

                    // Add slug to each submenu item
                    var subIndex = 0;
                    $submenu.find('li').each(function() {
                        var $subItem = $(this);

                        // Skip submenu header
                        if ($subItem.hasClass('wp-submenu-head')) return;

                        var $link = $subItem.find('a');
                        var href = $link.attr('href') || '';

                        // Prefer matching from PHP-provided slug list
                        var foundSlug = '';

                        if (phpSubSlugs.length > 0) {
                            // Extract relative path from href for matching
                            var relativeHref = href;
                            if (href.indexOf(adminUrl) === 0) {
                                relativeHref = href.substring(adminUrl.length);
                            } else if (href.indexOf('/wp-admin/') > -1) {
                                relativeHref = href.split('/wp-admin/').pop();
                            }
                            relativeHref = relativeHref.split('#')[0];

                            // Iterate PHP slugs to find match
                            for (var i = 0; i < phpSubSlugs.length; i++) {
                                var phpSlug = phpSubSlugs[i];
                                var decodedPhpSlug = decodeURIComponent(phpSlug);
                                var decodedHref = decodeURIComponent(relativeHref);

                                // Exact match
                                if (phpSlug === relativeHref || decodedPhpSlug === decodedHref) {
                                    foundSlug = phpSlug;
                                    break;
                                }

                                // href ends with phpSlug
                                if (relativeHref.endsWith(phpSlug) || decodedHref.endsWith(decodedPhpSlug)) {
                                    foundSlug = phpSlug;
                                    break;
                                }

                                // Check page parameter match
                                var hrefPageMatch = relativeHref.match(/[?&]page=([^&]+)/);
                                var phpPageMatch = phpSlug.match(/[?&]page=([^&]+)/);
                                if (hrefPageMatch && phpPageMatch && hrefPageMatch[1] === phpPageMatch[1]) {
                                    foundSlug = phpSlug;
                                    break;
                                }

                                // If phpSlug itself is a page name (without admin.php?page=)
                                if (hrefPageMatch && hrefPageMatch[1] === phpSlug) {
                                    foundSlug = phpSlug;
                                    break;
                                }
                            }
                        }

                        // If no match found, fall back to sequential assignment
                        if (!foundSlug && phpSubSlugs[subIndex]) {
                            foundSlug = phpSubSlugs[subIndex];
                        }

                        if (foundSlug) {
                            $subItem.attr('data-submenu-slug', foundSlug);
                        }

                        subIndex++;
                    });
                });
            }

            // Initialize submenu sorting
            function initSubmenuSortable() {
                $('#adminmenu .wp-submenu').each(function() {
                    var $submenu = $(this);
                    var parentSlug = $submenu.attr('data-parent-slug');

                    if (!parentSlug) return;

                    // Initialize sortable
                    $submenu.sortable({
                        items: '> li:not(.wp-submenu-head)',
                        axis: 'y',
                        containment: 'parent',
                        tolerance: 'pointer',
                        placeholder: 'fd-submenu-placeholder',
                        forcePlaceholderSize: true,
                        opacity: 0.9,
                        revert: 100,
                        delay: 50,
                        distance: 3,

                        start: function(event, ui) {
                            // Set placeholder height
                            ui.placeholder.height(ui.item.outerHeight());
                        },

                        update: function(event, ui) {
                            // Get new submenu order
                            var submenuOrder = [];

                            $submenu.find('li:not(.wp-submenu-head)').each(function() {
                                var slug = $(this).attr('data-submenu-slug');
                                if (slug) {
                                    submenuOrder.push(slug);
                                }
                            });

                            // Save submenu order
                            if (submenuOrder.length > 0) {
                                $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'fd_save_submenu_order',
                                        nonce: '<?php echo esc_attr( $nonce ); ?>',
                                        parent_slug: parentSlug,
                                        submenu_order: submenuOrder
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            showSaveNotification('success', fdSortStrings.submenuOrderSaved);
                                        } else {
                                            showSaveNotification('error', fdSortStrings.saveFailed + response.data);
                                        }
                                    },
                                    error: function() {
                                        showSaveNotification('error', fdSortStrings.networkError);
                                    }
                                });
                            }
                        }
                    });
                });
            }

            // Initialize submenu sorting
            initSubmenuSlugs();
            initSubmenuSortable();

            // Add marker when drag starts
            $('#adminmenu').on('sortstart', function() {
                $('body').addClass('ui-sortable-helper-active');
            });

            // Remove marker when drag ends and reinitialize
            $('#adminmenu').on('sortstop', function() {
                $('body').removeClass('ui-sortable-helper-active');
                // Delay reinitialization, wait for DOM update
                setTimeout(function() {
                    initSeparatorZones();
                    initSeparatorRemoveButtons();
                }, 100);
            });
        });
        </script>
        <?php
    }
}

// Initialize
FD_Menu_Sort::init();
