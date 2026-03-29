<?php
/**
 * FD Global Search
 * Global search functionality
 */

defined('ABSPATH') || exit;

class FD_Global_Search {

    /**
     * Initialize
     */
    public static function init() {
        $options = FD_Admin_UI_Settings::get_options();

        // Check if search is enabled
        if (!isset($options['search_enable']) || !$options['search_enable']) {
            return;
        }

        // Enqueue assets
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));

        // Register AJAX endpoint
        add_action('wp_ajax_fd_global_search', array(__CLASS__, 'ajax_search'));

        // Output search HTML
        add_action('admin_footer', array(__CLASS__, 'render_search_modal'));

        // Output menu search button (PHP rendered)
        if (isset($options['search_show_menu_button']) && $options['search_show_menu_button']) {
            add_action('admin_head', array(__CLASS__, 'render_menu_search_button_styles'));
            add_action('adminmenu', array(__CLASS__, 'render_menu_search_button_html'), 1); // Priority 1, after site branding
        }
    }

    /**
     * Enqueue scripts and styles
     */
    public static function enqueue_scripts() {
        $options = FD_Admin_UI_Settings::get_options();

        // CSS
        wp_enqueue_style(
            'fd-global-search',
            plugins_url('assets/css/fd-global-search.css', dirname(__FILE__)),
            array(),
            FD_ADMIN_UI_VERSION
        );

        // JavaScript
        wp_enqueue_script(
            'fd-global-search',
            plugins_url('assets/js/fd-global-search.js', dirname(__FILE__)),
            array('jquery'),
            FD_ADMIN_UI_VERSION,
            true
        );

        // Pass configuration to JavaScript
        wp_localize_script('fd-global-search', 'fdSearchConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fd_global_search'),
            'shortcutKey' => isset($options['search_shortcut_key']) ? $options['search_shortcut_key'] : 'k',
            'showHistory' => isset($options['search_show_history']) && $options['search_show_history'],
            'i18n' => array(
                'placeholder' => __('Search content, menus, settings...', 'fd-admin-ui'),
                'noResults' => __('No results found', 'fd-admin-ui'),
                'searching' => __('Searching...', 'fd-admin-ui'),
                'searchFailed' => __('Search failed, please try again', 'fd-admin-ui'),
                'searchHintText' => __('Type a keyword to start searching', 'fd-admin-ui'),
                'searchHintSubtext' => __('Search posts, pages, menus, users, and more', 'fd-admin-ui'),
                'recentSearches' => __('Recent Searches', 'fd-admin-ui'),
                'posts' => __('Posts', 'fd-admin-ui'),
                'pages' => __('Pages', 'fd-admin-ui'),
                'media' => __('Media', 'fd-admin-ui'),
                'menuItems' => __('Menu Items', 'fd-admin-ui'),
                'users' => __('Users', 'fd-admin-ui'),
                'plugins' => __('Plugins', 'fd-admin-ui'),
            )
        ));
    }

    /**
     * Render search modal
     */
    public static function render_search_modal() {
        $options = FD_Admin_UI_Settings::get_options();
        ?>
        <!-- Global Search Modal -->
        <div id="fd-search-modal" class="fd-search-modal" style="display: none;">
            <div class="fd-search-backdrop"></div>
            <div class="fd-search-container">
                <div class="fd-search-header">
                    <span class="fd-search-icon dashicons dashicons-search"></span>
                    <input type="text"
                           id="fd-search-input"
                           class="fd-search-input"
                           placeholder="<?php echo esc_attr__('Search content, menus, settings...', 'fd-admin-ui'); ?>"
                           autocomplete="off">
                    <button class="fd-search-close" title="<?php echo esc_attr__('Close (Esc)', 'fd-admin-ui'); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>

                <div class="fd-search-results" id="fd-search-results">
                    <!-- Search results will be dynamically inserted via JavaScript -->
                </div>

                <div class="fd-search-footer">
                    <div class="fd-search-shortcuts">
                        <span><kbd>↑</kbd><kbd>↓</kbd> <?php esc_html_e('Navigate', 'fd-admin-ui'); ?></span>
                        <span><kbd>Enter</kbd> <?php esc_html_e('Open', 'fd-admin-ui'); ?></span>
                        <span><kbd>Esc</kbd> <?php esc_html_e('Close', 'fd-admin-ui'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render menu search button styles
     */
    public static function render_menu_search_button_styles() {
        $options = FD_Admin_UI_Settings::get_options();
        ?>
        <style>
            /* Menu search button - mimics WordPress native menu item style */
            #fd-menu-search-button {
                margin: 0;
                padding: 0;
                display: block;
                position: relative;
                list-style: none;
            }

            #fd-menu-search-button .fd-search-menu-item {
                display: flex;
                align-items: center;
                padding: 0 0;
                margin: 0;
                cursor: pointer;
                text-decoration: none;
                color: <?php echo esc_attr($options['menu_text_color']); ?>;
                transition: all 0.15s;
                position: relative;
            }

            #fd-menu-search-button .fd-search-menu-item:hover {
                background-color: <?php echo esc_attr($options['menu_hover_bg_color']); ?>;
                color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
                box-shadow: inset 4px 0 0 0 <?php echo esc_attr($options['menu_hover_shadow_color']); ?>;
            }

            /* Icon container */
            #fd-menu-search-button .fd-search-menu-icon {
                width: 36px;
                height: 34px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            #fd-menu-search-button .fd-search-menu-icon .dashicons {
                font-size: 20px;
                width: 20px;
                height: 20px;
                color: <?php echo esc_attr($options['menu_text_color']); ?>;
                transition: color 0.15s;
            }

            /* Icon and text color on hover */
            #fd-menu-search-button .fd-search-menu-item:hover .fd-search-menu-icon .dashicons {
                color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
            }

            #fd-menu-search-button .fd-search-menu-item:hover .fd-search-menu-label {
                color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
            }

            /* Text content */
            #fd-menu-search-button .fd-search-menu-text {
                flex: 1;
                font-size: 14px;
                line-height: 1.3;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding-right: 12px;
                color: <?php echo esc_attr($options['menu_text_color']); ?>;
                transition: color 0.15s;
            }

            #fd-menu-search-button .fd-search-menu-label {
                flex: 1;
                color: inherit;
            }

            /* Text container color on hover */
            #fd-menu-search-button .fd-search-menu-item:hover .fd-search-menu-text {
                color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
            }

            /* Shortcut hint */
            #fd-menu-search-button .fd-search-menu-shortcut {
                font-size: 11px;
                padding: 2px 6px;
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 3px;
                font-family: monospace;
                opacity: 0.6;
                margin-left: 8px;
            }

            #fd-menu-search-button .fd-search-menu-item:hover .fd-search-menu-shortcut {
                opacity: 0.8;
            }

            /* Collapsed state (783-960px auto-collapse) */
            @media screen and (min-width: 783px) and (max-width: 960px) {
                body.auto-fold #fd-menu-search-button .fd-search-menu-item {
                    padding: 0;
                    height: 34px;
                    justify-content: center;
                }

                body.auto-fold #fd-menu-search-button .fd-search-menu-text {
                    display: none;
                }

                body.auto-fold #fd-menu-search-button .fd-search-menu-icon {
                    width: 36px;
                }
            }

            /* Manually collapsed state (>960px) */
            @media screen and (min-width: 961px) {
                body.folded #fd-menu-search-button .fd-search-menu-item {
                    padding: 0;
                    height: 34px;
                    justify-content: center;
                }

                body.folded #fd-menu-search-button .fd-search-menu-text {
                    display: none;
                }

                body.folded #fd-menu-search-button .fd-search-menu-icon {
                    width: 36px;
                }
            }

            /* Small screens: hidden by default, shown when menu is expanded */
            @media screen and (max-width: 782px) {
                #fd-menu-search-button {
                    display: none;
                }

                /* Show search button when menu is expanded */
                body.mobile-menu-active #fd-menu-search-button,
                .wp-responsive-open #fd-menu-search-button {
                    display: block;
                }
            }

            /* Adaptation for custom menu width */
            <?php if (!empty($options['menu_width'])): ?>
            @media only screen and (min-width: 961px) {
                <?php
                // If left/right padding is manually set, use manual values; otherwise auto-calculate
                if ($options['menu_item_left_padding'] !== '' && $options['menu_item_right_padding'] !== '') {
                    $main_left_padding = $options['menu_item_left_padding'];
                    $main_right_padding = $options['menu_item_right_padding'];
                } else {
                    $padding_adjustment = ($options['menu_width'] - 160) / 2;
                    $main_left_padding = max(0, $padding_adjustment);
                    $main_right_padding = max(10, 10 + $padding_adjustment);
                }
                ?>
                #fd-menu-search-button .fd-search-menu-item {
                    padding-left: <?php echo esc_attr( $main_left_padding ); ?>px;
                    padding-right: <?php echo esc_attr( $main_right_padding ); ?>px;
                }
            }
            <?php endif; ?>
        </style>
        <?php
    }

    /**
     * Render menu search button HTML (output directly into menu via adminmenu hook)
     */
    public static function render_menu_search_button_html() {
        $options = FD_Admin_UI_Settings::get_options();
        $shortcut_key = isset($options['search_shortcut_key']) ? $options['search_shortcut_key'] : 'k';
        $shortcut_display = $shortcut_key === 'k' ? '⌘K' : '⌘P';
        ?>
        <li id="fd-menu-search-button">
            <a href="#" class="fd-search-menu-item" id="fd-search-trigger">
                <div class="fd-search-menu-icon">
                    <span class="dashicons dashicons-search"></span>
                </div>
                <div class="fd-search-menu-text">
                    <span class="fd-search-menu-label"><?php esc_html_e('Search', 'fd-admin-ui'); ?></span>
                    <span class="fd-search-menu-shortcut"><?php echo esc_html($shortcut_display); ?></span>
                </div>
            </a>
        </li>
        <script>
        // Move search button after site branding (DOM structure adjustment)
        (function() {
            var searchButton = document.getElementById('fd-menu-search-button');
            var branding = document.getElementById('fd-site-branding');
            var adminmenu = document.getElementById('adminmenu');

            if (searchButton && adminmenu) {
                if (branding && branding.nextSibling) {
                    // If site branding exists, insert after it
                    adminmenu.insertBefore(searchButton, branding.nextSibling);
                } else if (adminmenu.firstChild) {
                    // Otherwise insert at the top of the menu
                    adminmenu.insertBefore(searchButton, adminmenu.firstChild);
                }
            }
        })();

        // Search button click event
        document.addEventListener('DOMContentLoaded', function() {
            var searchTrigger = document.getElementById('fd-search-trigger');
            if (searchTrigger) {
                searchTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Trigger search modal open
                    if (typeof FDSearch !== 'undefined' && FDSearch.open) {
                        FDSearch.open();
                    } else {
                        // If FDSearch object is not yet initialized, show the modal directly
                        var modal = document.getElementById('fd-search-modal');
                        if (modal) {
                            modal.style.display = 'block';
                            var input = document.getElementById('fd-search-input');
                            if (input) input.focus();
                        }
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * AJAX search handler
     */
    public static function ajax_search() {
        // Verify nonce
        check_ajax_referer('fd_global_search', 'nonce');

        // Get search keyword
        $keyword = isset($_POST['keyword']) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';

        if (empty($keyword)) {
            wp_send_json_error(array('message' => __('Please enter a search keyword', 'fd-admin-ui')));
        }

        // Get settings
        $options = FD_Admin_UI_Settings::get_options();
        $scope = isset($options['search_scope']) ? $options['search_scope'] : array();
        $limit = isset($options['search_results_limit']) ? $options['search_results_limit'] : 10;

        $results = array();

        // Search posts
        if (in_array('posts', $scope)) {
            $results['posts'] = self::search_posts($keyword, $limit);
        }

        // Search pages
        if (in_array('pages', $scope)) {
            $results['pages'] = self::search_pages($keyword, $limit);
        }

        // Search media
        if (in_array('media', $scope)) {
            $results['media'] = self::search_media($keyword, $limit);
        }

        // Search menu items
        if (in_array('menu_items', $scope)) {
            $results['menu_items'] = self::search_menu_items($keyword, $limit);
        }

        // Search users
        if (in_array('users', $scope)) {
            $results['users'] = self::search_users($keyword, $limit);
        }

        // Search plugins
        if (in_array('plugins', $scope)) {
            $results['plugins'] = self::search_plugins($keyword, $limit);
        }

        wp_send_json_success($results);
    }

    /**
     * Search posts
     */
    private static function search_posts($keyword, $limit) {
        $args = array(
            'post_type' => 'post',
            'post_status' => array('publish', 'draft', 'pending'),
            's' => $keyword,
            'posts_per_page' => $limit,
            'orderby' => 'relevance',
        );

        $query = new WP_Query($args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'url' => get_edit_post_link(get_the_ID(), 'raw'),
                    'excerpt' => wp_trim_words(get_the_excerpt(), 15),
                    'status' => get_post_status(),
                    'date' => get_the_date('Y-m-d'),
                    'icon' => 'dashicons-admin-post',
                );
            }
            wp_reset_postdata();
        }

        return $results;
    }

    /**
     * Search pages
     */
    private static function search_pages($keyword, $limit) {
        $args = array(
            'post_type' => 'page',
            'post_status' => array('publish', 'draft', 'pending'),
            's' => $keyword,
            'posts_per_page' => $limit,
            'orderby' => 'relevance',
        );

        $query = new WP_Query($args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'url' => get_edit_post_link(get_the_ID(), 'raw'),
                    'excerpt' => wp_trim_words(get_the_excerpt(), 15),
                    'status' => get_post_status(),
                    'date' => get_the_date('Y-m-d'),
                    'icon' => 'dashicons-admin-page',
                );
            }
            wp_reset_postdata();
        }

        return $results;
    }

    /**
     * Search media
     */
    private static function search_media($keyword, $limit) {
        $args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            's' => $keyword,
            'posts_per_page' => $limit,
        );

        $query = new WP_Query($args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'url' => get_edit_post_link(get_the_ID(), 'raw'),
                    'excerpt' => wp_get_attachment_url(get_the_ID()),
                    'date' => get_the_date('Y-m-d'),
                    'icon' => 'dashicons-admin-media',
                );
            }
            wp_reset_postdata();
        }

        return $results;
    }

    /**
     * Search menu items
     */
    private static function search_menu_items($keyword, $limit) {
        global $menu, $submenu;

        $results = array();
        $keyword_lower = strtolower($keyword);
        $count = 0;

        // Search main menu
        if (!empty($menu)) {
            foreach ($menu as $item) {
                if ($count >= $limit) break;

                if (empty($item[0])) continue;

                $title = wp_strip_all_tags($item[0]);
                if (stripos($title, $keyword) !== false) {
                    $results[] = array(
                        'title' => $title,
                        'url' => admin_url($item[2]),
                        'icon' => self::extract_menu_icon($item[6]),
                    );
                    $count++;
                }
            }
        }

        // Search submenus
        if (!empty($submenu)) {
            foreach ($submenu as $parent => $items) {
                if ($count >= $limit) break;

                foreach ($items as $item) {
                    if ($count >= $limit) break;

                    $title = wp_strip_all_tags($item[0]);
                    if (stripos($title, $keyword) !== false) {
                        $results[] = array(
                            'title' => $title,
                            'url' => admin_url($item[2]),
                            'icon' => 'dashicons-admin-generic',
                        );
                        $count++;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Search users
     */
    private static function search_users($keyword, $limit) {
        $args = array(
            'search' => '*' . $keyword . '*',
            'number' => $limit,
            'orderby' => 'display_name',
        );

        $user_query = new WP_User_Query($args);
        $results = array();

        if (!empty($user_query->results)) {
            foreach ($user_query->results as $user) {
                $results[] = array(
                    'id' => $user->ID,
                    'title' => $user->display_name,
                    'url' => get_edit_user_link($user->ID),
                    'excerpt' => $user->user_email,
                    'icon' => 'dashicons-admin-users',
                );
            }
        }

        return $results;
    }

    /**
     * Search plugins
     */
    private static function search_plugins($keyword, $limit) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $results = array();
        $count = 0;

        foreach ($all_plugins as $plugin_file => $plugin_data) {
            if ($count >= $limit) break;

            if (stripos($plugin_data['Name'], $keyword) !== false ||
                stripos($plugin_data['Description'], $keyword) !== false) {

                $results[] = array(
                    'title' => $plugin_data['Name'],
                    'url' => admin_url('plugins.php'),
                    'excerpt' => wp_trim_words($plugin_data['Description'], 15),
                    'icon' => 'dashicons-admin-plugins',
                );
                $count++;
            }
        }

        return $results;
    }

    /**
     * Extract menu icon class name
     */
    private static function extract_menu_icon($icon) {
        if (empty($icon)) {
            return 'dashicons-admin-generic';
        }

        // If it's a dashicon
        if (strpos($icon, 'dashicons-') !== false) {
            return $icon;
        }

        // Default icon
        return 'dashicons-admin-generic';
    }
}

// Initialize
FD_Global_Search::init();
