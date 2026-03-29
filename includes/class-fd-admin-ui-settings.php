<?php
/**
 * FD Admin UI Settings
 * Admin settings page
 */

defined('ABSPATH') || exit;

// Load modules
require_once __DIR__ . '/modules/class-fd-adminbar.php';
require_once __DIR__ . '/modules/class-fd-sidebar-menu.php';
require_once __DIR__ . '/modules/class-fd-search-settings.php';
require_once __DIR__ . '/modules/class-fd-content-area.php';
require_once __DIR__ . '/modules/class-fd-post-toc.php';
require_once __DIR__ . '/modules/class-fd-table-settings.php';
require_once __DIR__ . '/modules/class-fd-dashboard-settings.php';
require_once __DIR__ . '/modules/class-fd-login-settings.php';
require_once __DIR__ . '/modules/class-fd-global-settings.php';
require_once __DIR__ . '/modules/class-fd-footer-settings.php';
require_once __DIR__ . '/modules/class-fd-vi-settings.php';
require_once __DIR__ . '/modules/class-fd-editor-enhance.php';
require_once __DIR__ . '/modules/class-fd-post-meta-box.php';
require_once __DIR__ . '/modules/class-fd-taxonomy-box.php';

class FD_Admin_UI_Settings {

    /**
     * Initialize
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_settings_page'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_head', array(__CLASS__, 'output_custom_styles'));
        add_action('admin_head', array(__CLASS__, 'output_site_branding_styles'));
        add_action('adminmenu', array(__CLASS__, 'output_site_branding_html'));
        add_filter('wp_redirect', array(__CLASS__, 'add_tab_to_redirect'));
        add_action('admin_head', array(__CLASS__, 'remove_help_tabs'));
        add_action('admin_head', array(__CLASS__, 'add_user_menu_when_adminbar_hidden'));
        add_action('admin_post_fd_admin_ui_reset', array(__CLASS__, 'handle_reset'));

        // Footer customization
        add_filter('admin_footer_text', array(__CLASS__, 'custom_footer_text'));
        add_filter('update_footer', array(__CLASS__, 'custom_footer_version'), 11);

        // Dashboard settings (handled by module)
        add_action('wp_dashboard_setup', array('FD_Dashboard_Settings', 'setup'), 20);

        // Modern style Adminbar HTML (use high priority to be able to remove other nodes)
        add_action('admin_bar_menu', array(__CLASS__, 'add_modern_adminbar_elements'), 999);
    }

    /**
     * Handle reset to defaults
     */
    public static function handle_reset() {
        if ( ! current_user_can('manage_options') ) {
            wp_die(esc_html__('Insufficient permissions', 'fd-admin-ui'));
        }
        check_admin_referer('fd_admin_ui_reset');

        delete_option('fd_admin_ui_options');

        $tab = isset($_POST['active_tab']) ? sanitize_text_field( wp_unslash( $_POST['active_tab'] ) ) : 'adminbar';
        wp_safe_redirect( add_query_arg( array(
            'page'    => 'fd-admin-ui-settings',
            'tab'     => $tab,
            'reset'   => '1',
        ), admin_url('admin.php') ) );
        exit;
    }

    /**
     * Add settings page
     */
    public static function add_settings_page() {
        $page = add_options_page(
            __('Admin UI Settings', 'fd-admin-ui'),
            __('Admin UI', 'fd-admin-ui'),
            'manage_options',
            'fd-admin-ui-settings',
            array(__CLASS__, 'render_settings_page')
        );

        // Enqueue media upload scripts on the settings page
        add_action('admin_print_scripts-' . $page, array(__CLASS__, 'enqueue_media_uploader'));
    }

    /**
     * Enqueue media upload scripts
     */
    public static function enqueue_media_uploader() {
        wp_enqueue_media();
        // Ensure dashicons are loaded
        wp_enqueue_style('dashicons');
    }

    /**
     * Add tab parameter to redirect URL after settings save
     */
    public static function add_tab_to_redirect($location) {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'fd_admin_ui_settings-options' ) ) {
            return $location;
        }
        if (isset($_POST['active_tab'])) {
            $location = add_query_arg('tab', sanitize_text_field( wp_unslash( $_POST['active_tab'] ) ), $location);
        }
        return $location;
    }

    /**
     * Register settings
     */
    public static function register_settings() {
        register_setting('fd_admin_ui_settings', 'fd_admin_ui_options', array(
            'sanitize_callback' => array(__CLASS__, 'sanitize_options')
        ));

        // ========================================
        // Adminbar settings (handled by module)
        // ========================================
        FD_Adminbar::register_settings();

        // ========================================
        // Sidebar menu settings (handled by module)
        // ========================================
        FD_Sidebar_Menu::register_settings();

        // ========================================
        // Global search settings (handled by module)
        // ========================================
        FD_Search_Settings::register_settings();

        // ========================================
        // Login page settings (handled by module)
        // ========================================
        FD_Login_Settings::register_settings();

        // ========================================
        // Global settings (handled by module)
        // ========================================
        FD_Global_Settings::register_settings();

        // ========================================
        // Content area settings (handled by module)
        // ========================================
        FD_Content_Area::register_settings();

        // ========================================
        // Post editor TOC (handled by module)
        // ========================================
        FD_Post_TOC::register_settings();

        // ========================================
        // Footer settings (handled by module)
        // ========================================
        FD_Footer_Settings::register_settings();

        // ========================================
        // Table settings (handled by module)
        // ========================================
        FD_Table_Settings::register_settings();

        // ========================================
        // Dashboard settings (handled by module)
        // ========================================
        FD_Dashboard_Settings::register_settings();

        // ========================================
        // VI settings (handled by module)
        // ========================================
        FD_VI_Settings::register_settings();

        // ========================================
        // Editor enhancements (handled by module)
        // ========================================
        FD_Editor_Enhance::register_settings();

        // ========================================
        // Post meta box enhancements (handled by module)
        // ========================================
        FD_Post_Meta_Box::register_settings();

        // ========================================
        // Taxonomy box integration (handled by module)
        // ========================================
        FD_Taxonomy_Box::register_settings();
    }

    /**
     * Get default options (aggregated from all modules)
     */
    public static function get_default_options() {
        return array_merge(
            FD_Adminbar::get_defaults(),
            FD_Sidebar_Menu::get_defaults(),
            FD_Search_Settings::get_defaults(),
            FD_Login_Settings::get_defaults(),
            FD_Global_Settings::get_defaults(),
            FD_Content_Area::get_defaults(),
            FD_Post_TOC::get_defaults(),
            FD_Footer_Settings::get_defaults(),
            FD_Table_Settings::get_defaults(),
            FD_Dashboard_Settings::get_defaults(),
            FD_VI_Settings::get_defaults(),
            FD_Editor_Enhance::get_defaults(),
            FD_Post_Meta_Box::get_defaults(),
            FD_Taxonomy_Box::get_defaults()
        );
    }

    /**
     * Get options
     */
    public static function get_options() {
        $defaults = self::get_default_options();
        $options = get_option('fd_admin_ui_options', array());
        return wp_parse_args($options, $defaults);
    }

    /**
     * Sanitize color value (supports hex and rgba)
     */
    public static function sanitize_color($color) {
        $color = trim($color);

        // If rgba format
        if (preg_match('/^rgba?\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+)\s*)?\)$/i', $color, $matches)) {
            $r = min(255, max(0, intval($matches[1])));
            $g = min(255, max(0, intval($matches[2])));
            $b = min(255, max(0, intval($matches[3])));
            $a = isset($matches[4]) ? min(1, max(0, floatval($matches[4]))) : 1;

            if ($a < 1) {
                return "rgba($r, $g, $b, $a)";
            } else {
                return "rgb($r, $g, $b)";
            }
        }

        // If hex format
        return sanitize_hex_color($color);
    }

    /**
     * Sanitize options (delegated to each module)
     */
    public static function sanitize_options($input) {
        $sanitized = array();

        // Call each module's sanitize method
        $sanitized = FD_Adminbar::sanitize($input, $sanitized);
        $sanitized = FD_Sidebar_Menu::sanitize($input, $sanitized);
        $sanitized = FD_Search_Settings::sanitize($input, $sanitized);
        $sanitized = FD_Login_Settings::sanitize($input, $sanitized);
        $sanitized = FD_Global_Settings::sanitize($input, $sanitized);
        $sanitized = FD_Content_Area::sanitize($input, $sanitized);
        $sanitized = FD_Post_TOC::sanitize($input, $sanitized);
        $sanitized = FD_Footer_Settings::sanitize($input, $sanitized);
        $sanitized = FD_Table_Settings::sanitize($input, $sanitized);
        $sanitized = FD_Dashboard_Settings::sanitize($input, $sanitized);
        $sanitized = FD_VI_Settings::sanitize($input, $sanitized);
        $sanitized = FD_Editor_Enhance::sanitize($input, $sanitized);
        $sanitized = FD_Post_Meta_Box::sanitize($input, $sanitized);
        $sanitized = FD_Taxonomy_Box::sanitize($input, $sanitized);

        return $sanitized;
    }

    // ========================================
    // Sidebar menu settings callbacks (called by module delegation)
    // ========================================
    public static function menu_site_logo_full_callback() {
        $options = self::get_options();
        $value = isset($options['menu_site_logo_full']) ? $options['menu_site_logo_full'] : '';
        ?>
        <div class="fd-media-upload">
            <input type="hidden"
                   id="menu_site_logo_full"
                   name="fd_admin_ui_options[menu_site_logo_full]"
                   value="<?php echo esc_attr($value); ?>">
            <div class="fd-logo-full-preview">
                <?php if ($value): ?>
                    <img src="<?php echo esc_url($value); ?>" style="max-width: 200px; max-height: 60px; display: block; margin-bottom: 10px; object-fit: contain;">
                <?php endif; ?>
            </div>
            <button type="button" class="button fd-upload-logo-full-btn">
                <?php echo $value ? esc_html__('Replace Full Logo', 'fd-admin-ui') : esc_html__('Upload Full Logo', 'fd-admin-ui'); ?>
            </button>
            <?php if ($value): ?>
                <button type="button" class="button fd-remove-logo-full-btn"><?php esc_html_e('Remove', 'fd-admin-ui'); ?></button>
            <?php endif; ?>
        </div>
        <p class="description"><?php esc_html_e('Horizontal full logo (icon + text combination), recommended size: 400x100px or similar ratio. Displayed when the sidebar is expanded on desktop.', 'fd-admin-ui'); ?></p>

        <script>
        jQuery(document).ready(function($) {
            var mediaUploader;

            $('.fd-upload-logo-full-btn').on('click', function(e) {
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: <?php echo wp_json_encode(__('Select Full Logo', 'fd-admin-ui')); ?>,
                    button: {
                        text: <?php echo wp_json_encode(__('Use This Image', 'fd-admin-ui')); ?>
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#menu_site_logo_full').val(attachment.url);
                    $('.fd-logo-full-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; max-height: 60px; display: block; margin-bottom: 10px; object-fit: contain;">');
                    $('.fd-upload-logo-full-btn').text(<?php echo wp_json_encode(__('Replace Full Logo', 'fd-admin-ui')); ?>);
                    if ($('.fd-remove-logo-full-btn').length === 0) {
                        $('.fd-upload-logo-full-btn').after('<button type="button" class="button fd-remove-logo-full-btn">' + <?php echo wp_json_encode(__('Remove', 'fd-admin-ui')); ?> + '</button>');
                    }
                });

                mediaUploader.open();
            });

            $(document).on('click', '.fd-remove-logo-full-btn', function(e) {
                e.preventDefault();
                $('#menu_site_logo_full').val('');
                $('.fd-logo-full-preview').html('');
                $('.fd-upload-logo-full-btn').text(<?php echo wp_json_encode(__('Upload Full Logo', 'fd-admin-ui')); ?>);
                $(this).remove();
            });
        });
        </script>
        <?php
    }

    public static function menu_site_logo_square_callback() {
        $options = self::get_options();
        $value = isset($options['menu_site_logo_square']) ? $options['menu_site_logo_square'] : '';
        ?>
        <div class="fd-media-upload">
            <input type="hidden"
                   id="menu_site_logo_square"
                   name="fd_admin_ui_options[menu_site_logo_square]"
                   value="<?php echo esc_attr($value); ?>">
            <div class="fd-logo-square-preview">
                <?php if ($value): ?>
                    <img src="<?php echo esc_url($value); ?>" style="max-width: 60px; max-height: 60px; border-radius: 4px; display: block; margin-bottom: 10px;">
                <?php endif; ?>
            </div>
            <button type="button" class="button fd-upload-logo-square-btn">
                <?php echo $value ? esc_html__('Replace Square Logo', 'fd-admin-ui') : esc_html__('Upload Square Logo', 'fd-admin-ui'); ?>
            </button>
            <?php if ($value): ?>
                <button type="button" class="button fd-remove-logo-square-btn"><?php esc_html_e('Remove', 'fd-admin-ui'); ?></button>
            <?php endif; ?>
        </div>
        <p class="description"><?php esc_html_e('Square icon logo, recommended size: 200x200px. Displayed when the menu is collapsed.', 'fd-admin-ui'); ?></p>

        <script>
        jQuery(document).ready(function($) {
            var mediaUploader;

            $('.fd-upload-logo-square-btn').on('click', function(e) {
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: <?php echo wp_json_encode(__('Select Square Logo', 'fd-admin-ui')); ?>,
                    button: {
                        text: <?php echo wp_json_encode(__('Use This Image', 'fd-admin-ui')); ?>
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#menu_site_logo_square').val(attachment.url);
                    $('.fd-logo-square-preview').html('<img src="' + attachment.url + '" style="max-width: 60px; max-height: 60px; border-radius: 4px; display: block; margin-bottom: 10px;">');
                    $('.fd-upload-logo-square-btn').text(<?php echo wp_json_encode(__('Replace Square Logo', 'fd-admin-ui')); ?>);
                    if ($('.fd-remove-logo-square-btn').length === 0) {
                        $('.fd-upload-logo-square-btn').after('<button type="button" class="button fd-remove-logo-square-btn">' + <?php echo wp_json_encode(__('Remove', 'fd-admin-ui')); ?> + '</button>');
                    }
                });

                mediaUploader.open();
            });

            $(document).on('click', '.fd-remove-logo-square-btn', function(e) {
                e.preventDefault();
                $('#menu_site_logo_square').val('');
                $('.fd-logo-square-preview').html('');
                $('.fd-upload-logo-square-btn').text(<?php echo wp_json_encode(__('Upload Square Logo', 'fd-admin-ui')); ?>);
                $(this).remove();
            });
        });
        </script>
        <?php
    }

    public static function menu_branding_link_callback() {
        $options = self::get_options();
        $value = isset($options['menu_branding_link']) ? $options['menu_branding_link'] : '';
        $default_url = home_url();
        ?>
        <input type="url"
               name="fd_admin_ui_options[menu_branding_link]"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               placeholder="<?php echo esc_attr($default_url); ?>">
        <p class="description"><?php esc_html_e('Link to navigate to when clicking the site branding. Defaults to the homepage. Can be set to a front-end application URL.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function menu_branding_show_icon_callback() {
        $options = self::get_options();
        $checked = isset($options['menu_branding_show_icon']) && $options['menu_branding_show_icon'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[menu_branding_show_icon]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Show a quick access icon (external link icon) next to the site branding', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function svg_icon_replacements_callback() {
        $options = self::get_options();
        $replacements = isset($options['svg_icon_replacements']) ? $options['svg_icon_replacements'] : array();

        // Get all menu items that use SVG icons
        global $menu;
        $svg_menus = array();

        if (!empty($menu)) {
            foreach ($menu as $menu_item) {
                if (empty($menu_item[0]) || empty($menu_item[6])) {
                    continue;
                }

                // Check if it contains SVG (by checking for data:image or .svg)
                if (strpos($menu_item[6], 'data:image') !== false ||
                    strpos($menu_item[6], '.svg') !== false ||
                    strpos($menu_item[6], 'div') !== false && strpos($menu_item[6], 'svg') !== false) {

                    $svg_menus[] = array(
                        'title' => wp_strip_all_tags($menu_item[0]),
                        'slug' => $menu_item[2],
                        'icon' => $menu_item[6]
                    );
                }
            }
        }

        if (empty($svg_menus)) {
            echo '<p>' . esc_html__('No menu items using SVG icons were detected.', 'fd-admin-ui') . '</p>';
            return;
        }

        // Get icon list (using the component's list, but modify the empty option label)
        $dashicons = FD_Icon_Picker::get_dashicons();
        $dashicons[''] = __('Do not replace (keep SVG)', 'fd-admin-ui');

        ?>
        <div class="svg-icon-replacements">
            <style>
                .svg-icon-replacements table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .svg-icon-replacements th,
                .svg-icon-replacements td {
                    padding: 12px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                    vertical-align: middle;
                }
                .svg-icon-replacements th {
                    background: #f5f5f5;
                    font-weight: 600;
                }
            </style>

            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;"><?php esc_html_e('Menu Name', 'fd-admin-ui'); ?></th>
                        <th style="width: 15%;"><?php esc_html_e('Current Icon', 'fd-admin-ui'); ?></th>
                        <th style="width: 45%;"><?php esc_html_e('Replace with Dashicons', 'fd-admin-ui'); ?></th>
                        <th style="width: 15%;"><?php esc_html_e('Preview', 'fd-admin-ui'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($svg_menus as $menu_item): ?>
                        <?php
                        $slug = $menu_item['slug'];
                        $selected = isset($replacements[$slug]) ? $replacements[$slug] : '';
                        $preview_id = 'preview-' . esc_attr(md5($slug));
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($menu_item['title']); ?></strong></td>
                            <td><small style="color: #666;"><?php esc_html_e('SVG Icon', 'fd-admin-ui'); ?></small></td>
                            <td>
                                <?php
                                FD_Icon_Picker::render(array(
                                    'name' => 'fd_admin_ui_options[svg_icon_replacements][' . esc_attr($slug) . ']',
                                    'value' => $selected,
                                    'preview_id' => $preview_id,
                                    'empty_label' => __('Do not replace (keep SVG)', 'fd-admin-ui'),
                                    'icons' => $dashicons,
                                ));
                                ?>
                            </td>
                            <td>
                                <?php FD_Icon_Picker::render_preview($preview_id, $selected); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="description" style="margin-top: 15px;">
                <strong><?php esc_html_e('Tip:', 'fd-admin-ui'); ?></strong> <?php esc_html_e('Select a Dashicons icon to replace the SVG icon. The replaced icon will automatically use your configured menu colors, avoiding color control issues.', 'fd-admin-ui'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render settings page
     */
    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get current tab (default to adminbar)
        $active_tab = isset($_GET['tab']) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'adminbar'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        ?>
        <div class="wrap fd-admin-wrapper">
            <?php
            // Reset success notice
            if ( isset($_GET['reset']) && $_GET['reset'] === '1' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings have been reset to defaults. The current page displays the new default appearance.', 'fd-admin-ui') . '</p></div>';
            }

            FD_Admin_UI::render_page_header(
                __('Admin UI Settings', 'fd-admin-ui'),
                __('Customize WordPress admin interface appearance', 'fd-admin-ui'),
                array()
            );

            ?>
            <hr class="wp-header-end">

            <!-- Tab navigation -->
            <div class="fd-tabs">
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'global' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('global')">
                    <?php esc_html_e('Global Settings', 'fd-admin-ui'); ?>
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'vi' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('vi')">
                    <?php esc_html_e('VI Settings', 'fd-admin-ui'); ?>
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'adminbar' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('adminbar')">
                    Adminbar
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'menu' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('menu')">
                    <?php esc_html_e('Sidebar Menu', 'fd-admin-ui'); ?>
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'content' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('content')">
                    <?php esc_html_e('Content Area', 'fd-admin-ui'); ?>
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'table' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('table')">
                    <?php esc_html_e('Table', 'fd-admin-ui'); ?>
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'footer' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('footer')">
                    <?php esc_html_e('Footer', 'fd-admin-ui'); ?>
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'dashboard' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('dashboard')">
                    Dashboard
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'search' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('search')">
                    <?php esc_html_e('Global Search', 'fd-admin-ui'); ?>
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'login' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('login')">
                    <?php esc_html_e('Login Page', 'fd-admin-ui'); ?>
                </button>
                <button type="button"
                        class="fd-tab <?php echo $active_tab === 'editor' ? 'fd-tab-active' : ''; ?>"
                        onclick="switchTab('editor')">
                    <?php esc_html_e('Editor Enhancements', 'fd-admin-ui'); ?>
                </button>
            </div>

            <div class="fd-card">
                <form method="post" action="options.php">
                    <?php settings_fields('fd_admin_ui_settings'); ?>

                    <!-- Hidden field to save current tab -->
                    <input type="hidden" name="active_tab" id="active_tab" value="<?php echo esc_attr($active_tab); ?>">

                    <!-- Global Settings tab -->
                    <div id="tab-global" class="fd-tab-content" style="<?php echo $active_tab === 'global' ? 'display:block;' : 'display:none;'; ?>">
                        <?php do_settings_sections('fd-admin-ui-settings-global'); ?>
                    </div>

                    <!-- VI Settings tab -->
                    <div id="tab-vi" class="fd-tab-content" style="<?php echo $active_tab === 'vi' ? 'display:block;' : 'display:none;'; ?>">
                        <?php do_settings_sections('fd-admin-ui-settings-vi'); ?>
                    </div>

                    <!-- Adminbar Settings tab - using accordion -->
                    <div id="tab-adminbar" class="fd-tab-content" style="<?php echo $active_tab === 'adminbar' ? 'display:block;' : 'display:none;'; ?>">
                        <div class="fd-accordion">
                            <?php
                            $options = self::get_options();
                            $adminbar_style = isset($options['adminbar_style']) ? $options['adminbar_style'] : 'classic';

                            // Define Adminbar settings section groups
                            $adminbar_sections = array(
                                'fd_adminbar_basic_section' => __('Basic Settings', 'fd-admin-ui'),
                                'fd_adminbar_color_section' => __('Color Settings', 'fd-admin-ui'),
                                'fd_adminbar_modern_section' => __('Modern Style Settings', 'fd-admin-ui'),
                                'fd_adminbar_items_section' => __('Item Display Control', 'fd-admin-ui'),
                            );

                            $index = 0;
                            foreach ($adminbar_sections as $section_id => $section_title):
                                $is_first = ($index === 0);
                                $is_modern_section = ($section_id === 'fd_adminbar_modern_section');
                                $index++;
                            ?>
                            <div class="fd-accordion-item <?php echo $is_first ? 'fd-accordion-active' : ''; ?> <?php echo $is_modern_section ? 'fd-adminbar-modern-only' : ''; ?>" <?php echo $is_modern_section && $adminbar_style !== 'modern' ? 'style="display:none;"' : ''; ?>>
                                <div class="fd-accordion-header">
                                    <h3><?php echo esc_html($section_title); ?><?php if ($is_modern_section): ?><span class="fd-badge" style="margin-left: 8px; background: #3b82f6; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px;"><?php esc_html_e('Modern style only', 'fd-admin-ui'); ?></span><?php endif; ?></h3>
                                    <span class="fd-accordion-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="fd-accordion-content" style="<?php echo $is_first ? 'display:block;' : 'display:none;'; ?>">
                                    <?php
                                    // Output all fields for this section
                                    global $wp_settings_sections, $wp_settings_fields;

                                    if (isset($wp_settings_sections['fd-admin-ui-settings-adminbar'][$section_id])) {
                                        $section = $wp_settings_sections['fd-admin-ui-settings-adminbar'][$section_id];

                                        // Output section description
                                        if ($section['callback']) {
                                            call_user_func($section['callback'], $section);
                                        }

                                        // Output all fields for this section
                                        if (isset($wp_settings_fields['fd-admin-ui-settings-adminbar'][$section_id])) {
                                            echo '<table class="form-table" role="presentation">';
                                            do_settings_fields('fd-admin-ui-settings-adminbar', $section_id);
                                            echo '</table>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Adminbar style toggle JS -->
                        <script>
                        jQuery(document).ready(function($) {
                            // Listen for style toggle
                            $('input[name="fd_admin_ui_options[adminbar_style]"]').on('change', function() {
                                var style = $(this).val();
                                if (style === 'modern') {
                                    $('.fd-adminbar-modern-only').slideDown(300);
                                } else {
                                    $('.fd-adminbar-modern-only').slideUp(300);
                                }
                            });
                        });
                        </script>
                    </div>

                    <!-- Sidebar Menu Settings tab - using accordion -->
                    <div id="tab-menu" class="fd-tab-content" style="<?php echo $active_tab === 'menu' ? 'display:block;' : 'display:none;'; ?>">
                        <div class="fd-accordion">
                            <?php
                            // Define menu settings section groups
                            $menu_sections = array(
                                'fd_menu_style_section' => __('Menu Style', 'fd-admin-ui'),
                                'fd_menu_basic_section' => __('Basic Settings', 'fd-admin-ui'),
                                'fd_menu_hover_section' => __('Hover Effect', 'fd-admin-ui'),
                                'fd_menu_current_section' => __('Current Selected Item', 'fd-admin-ui'),
                                'fd_menu_size_section' => __('Size & Spacing', 'fd-admin-ui'),
                                'fd_menu_modern_section' => __('Modern Style Settings', 'fd-admin-ui'),
                                'fd_menu_branding_section' => __('Site Branding', 'fd-admin-ui'),
                                'fd_menu_sort_section' => __('Menu Sorting', 'fd-admin-ui'),
                                'fd_menu_auto_fold_section' => __('Auto Collapse', 'fd-admin-ui'),
                                'fd_menu_svg_section' => __('SVG Icon Replacement', 'fd-admin-ui'),
                            );

                            $menu_style = isset($options['menu_style']) ? $options['menu_style'] : 'classic';

                            $index = 0;
                            foreach ($menu_sections as $section_id => $section_title):
                                $is_first = ($index === 0);
                                $is_modern_section = ($section_id === 'fd_menu_modern_section');
                                $index++;
                            ?>
                            <div class="fd-accordion-item <?php echo $is_first ? 'fd-accordion-active' : ''; ?> <?php echo $is_modern_section ? 'fd-menu-modern-only' : ''; ?>" <?php echo $is_modern_section && $menu_style !== 'modern' ? 'style="display:none;"' : ''; ?>>
                                <div class="fd-accordion-header">
                                    <h3><?php echo esc_html($section_title); ?><?php if ($is_modern_section): ?><span class="fd-badge" style="margin-left: 8px; background: #3b82f6; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px;"><?php esc_html_e('Modern style only', 'fd-admin-ui'); ?></span><?php endif; ?></h3>
                                    <span class="fd-accordion-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="fd-accordion-content" style="<?php echo $is_first ? 'display:block;' : 'display:none;'; ?>">
                                    <?php
                                    // Output all fields for this section
                                    global $wp_settings_sections, $wp_settings_fields;

                                    if (isset($wp_settings_sections['fd-admin-ui-settings-menu'][$section_id])) {
                                        $section = $wp_settings_sections['fd-admin-ui-settings-menu'][$section_id];

                                        // Output section description
                                        if ($section['callback']) {
                                            call_user_func($section['callback'], $section);
                                        }

                                        // Output all fields for this section
                                        if (isset($wp_settings_fields['fd-admin-ui-settings-menu'][$section_id])) {
                                            echo '<table class="form-table" role="presentation">';
                                            do_settings_fields('fd-admin-ui-settings-menu', $section_id);
                                            echo '</table>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Menu style toggle JS -->
                        <script>
                        jQuery(document).ready(function($) {
                            // Listen for style toggle
                            $('input[name="fd_admin_ui_options[menu_style]"]').on('change', function() {
                                var style = $(this).val();
                                if (style === 'modern') {
                                    $('.fd-menu-modern-only').slideDown(300);
                                } else {
                                    $('.fd-menu-modern-only').slideUp(300);
                                }
                            });
                        });
                        </script>
                    </div>

                    <!-- Content Area Settings tab -->
                    <div id="tab-content" class="fd-tab-content" style="<?php echo $active_tab === 'content' ? 'display:block;' : 'display:none;'; ?>">
                        <?php do_settings_sections('fd-admin-ui-settings-content'); ?>
                    </div>

                    <!-- Table Settings tab -->
                    <div id="tab-table" class="fd-tab-content" style="<?php echo $active_tab === 'table' ? 'display:block;' : 'display:none;'; ?>">
                        <?php do_settings_sections('fd-admin-ui-settings-table'); ?>
                    </div>

                    <!-- Footer Settings tab -->
                    <div id="tab-footer" class="fd-tab-content" style="<?php echo $active_tab === 'footer' ? 'display:block;' : 'display:none;'; ?>">
                        <?php do_settings_sections('fd-admin-ui-settings-footer'); ?>
                    </div>

                    <!-- Dashboard Settings tab -->
                    <div id="tab-dashboard" class="fd-tab-content" style="<?php echo $active_tab === 'dashboard' ? 'display:block;' : 'display:none;'; ?>">
                        <?php do_settings_sections('fd-admin-ui-settings-dashboard'); ?>
                    </div>

                    <!-- Global Search Settings tab -->
                    <div id="tab-search" class="fd-tab-content" style="<?php echo $active_tab === 'search' ? 'display:block;' : 'display:none;'; ?>">
                        <?php do_settings_sections('fd-admin-ui-settings-search'); ?>
                    </div>

                    <!-- Login Page Settings tab -->
                    <div id="tab-login" class="fd-tab-content" style="<?php echo $active_tab === 'login' ? 'display:block;' : 'display:none;'; ?>">
                        <?php do_settings_sections('fd-admin-ui-settings-login'); ?>

                        <!-- Preview login page button -->
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e5e5;">
                            <a href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" class="fd-btn fd-btn-secondary">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="margin-right: 6px;">
                                    <path d="M10 3C5.58 3 2 6.58 2 11C2 15.42 5.58 19 10 19C14.42 19 18 15.42 18 11C18 6.58 14.42 3 10 3ZM10 17C6.69 17 4 14.31 4 11C4 7.69 6.69 5 10 5C13.31 5 16 7.69 16 11C16 14.31 13.31 17 10 17ZM10 7C8.34 7 7 8.34 7 10C7 11.66 8.34 13 10 13C11.66 13 13 11.66 13 10C13 8.34 11.66 7 10 7Z" fill="currentColor"/>
                                </svg>
                                <?php esc_html_e('Preview Login Page', 'fd-admin-ui'); ?>
                            </a>
                            <span class="description" style="margin-left: 10px;"><?php esc_html_e('View the login page appearance in a new window', 'fd-admin-ui'); ?></span>
                        </div>
                    </div>

                    <!-- Editor Enhancements Settings tab -->
                    <div id="tab-editor" class="fd-tab-content" style="<?php echo $active_tab === 'editor' ? 'display:block;' : 'display:none;'; ?>">
                        <?php do_settings_sections('fd-admin-ui-settings-editor'); ?>
                    </div>

                    <div class="fd-form-actions">
                        <?php submit_button(__('Save Settings', 'fd-admin-ui'), 'fd-btn fd-btn-primary', 'submit', false); ?>
                        <button type="button" class="button button-secondary" id="fd-btn-reset-defaults">
                            <?php esc_html_e('Reset to Defaults', 'fd-admin-ui'); ?>
                        </button>
                    </div>
                </form>

                <!-- Reset form: separate from the main form to avoid HTML nesting issues -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="fd-reset-defaults-form" style="display:none;">
                    <input type="hidden" name="action" value="fd_admin_ui_reset">
                    <input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab); ?>">
                    <?php wp_nonce_field('fd_admin_ui_reset'); ?>
                </form>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Reset to defaults
            $('#fd-btn-reset-defaults').on('click', function() {
                if (confirm(<?php echo wp_json_encode(__('Are you sure you want to reset all settings to defaults? This action cannot be undone.', 'fd-admin-ui')); ?>)) {
                    $('#fd-reset-defaults-form').submit();
                }
            });

            // Accordion functionality
            $('.fd-accordion-header').on('click', function() {
                var $item = $(this).closest('.fd-accordion-item');
                var $content = $item.find('.fd-accordion-content');
                var isActive = $item.hasClass('fd-accordion-active');

                if (isActive) {
                    // Close current item
                    $item.removeClass('fd-accordion-active');
                    $content.slideUp(300);
                } else {
                    // Close other items
                    $('.fd-accordion-item').removeClass('fd-accordion-active');
                    $('.fd-accordion-content').slideUp(300);

                    // Open current item
                    $item.addClass('fd-accordion-active');
                    $content.slideDown(300);
                }
            });

            // Initialize color pickers
            $('.fd-color-picker').wpColorPicker();

            // Create independent media uploaders for each upload button
            $(document).on('click', '.fd-upload-btn', function(e) {
                e.preventDefault();

                var button = $(this);
                var targetId = button.data('target');
                var targetInput = $('#' + targetId);

                // Create a new media uploader instance for each click
                var mediaUploader = wp.media({
                    title: <?php echo wp_json_encode(__('Select Image', 'fd-admin-ui')); ?>,
                    button: {
                        text: <?php echo wp_json_encode(__('Use This Image', 'fd-admin-ui')); ?>
                    },
                    multiple: false
                });

                // Bind select event to the current input
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();

                    // Update target input
                    targetInput.val(attachment.url);

                    // Update preview
                    var previewContainer = button.closest('.fd-image-upload-wrap').next('.fd-image-preview');
                    if (previewContainer.length) {
                        var maxWidth = targetId.indexOf('favicon') !== -1 ? '32px' : '200px';
                        var maxHeight = targetId.indexOf('favicon') !== -1 ? '32px' : '120px';
                        previewContainer.html('<img src="' + attachment.url + '" style="max-width: ' + maxWidth + '; max-height: ' + maxHeight + '; border: 1px solid #ddd; border-radius: 4px;">');
                    }

                    // Add remove button (if it doesn't exist)
                    if (button.siblings('.fd-remove-btn[data-target="' + targetId + '"]').length === 0) {
                        button.after('<button type="button" class="fd-btn fd-btn-secondary fd-remove-btn" data-target="' + targetId + '" style="margin-left: 8px;">' + <?php echo wp_json_encode(__('Remove', 'fd-admin-ui')); ?> + '</button>');
                    }
                });

                // Open media library
                mediaUploader.open();
            });

            // Remove image button handler
            $(document).on('click', '.fd-remove-btn', function(e) {
                e.preventDefault();

                var button = $(this);
                var targetId = button.data('target');
                var targetInput = $('#' + targetId);

                targetInput.val('');

                // Clear preview
                var previewContainer = button.closest('.fd-image-upload-wrap').next('.fd-image-preview');
                if (previewContainer.length) {
                    previewContainer.html('');
                }

                button.remove();
            });
        });

        function switchTab(tab) {
            // Hide all tab contents
            document.querySelectorAll('.fd-tab-content').forEach(function(content) {
                content.style.display = 'none';
            });

            // Remove active state from all tabs
            document.querySelectorAll('.fd-tab').forEach(function(tabBtn) {
                tabBtn.classList.remove('fd-tab-active');
            });

            // Show current tab content
            document.getElementById('tab-' + tab).style.display = 'block';

            // Activate current tab
            event.target.classList.add('fd-tab-active');

            // Update hidden field
            document.getElementById('active_tab').value = tab;

            // Update URL (without page refresh)
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
        </script>

        <style>
        /* Image upload area styles */
        .fd-image-upload-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .fd-image-upload-wrap input[type="text"] {
            flex: 1;
            max-width: 400px;
        }

        /* Save button container styles */
        .fd-card form > p.submit {
            margin: 0;
            padding: 20px 0 0;
            border-top: 1px solid #e5e5e5;
        }

        /* Unified button styles */
        .fd-btn.fd-btn-primary {
            background: #3b82f6 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            padding: 10px 24px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            text-shadow: none !important;
            height: auto !important;
            display: inline-flex !important;
            align-items: center !important;
            text-decoration: none !important;
        }

        .fd-btn.fd-btn-primary:hover {
            background: #2563eb !important;
            border-color: #2563eb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2) !important;
        }

        .fd-btn.fd-btn-primary:active {
            transform: translateY(0);
        }

        .fd-btn.fd-btn-secondary {
            background: #f0f0f1 !important;
            border: 1px solid #dcdcde !important;
            color: #2c3338 !important;
            padding: 8px 16px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
            height: auto !important;
            display: inline-flex !important;
            align-items: center !important;
            text-decoration: none !important;
        }

        .fd-btn.fd-btn-secondary:hover {
            background: #e8e8e9 !important;
            border-color: #c3c4c7 !important;
            color: #1d2327 !important;
        }

        .fd-btn.fd-btn-secondary .dashicons {
            margin-top: -2px;
        }
        </style>
        <?php
    }

    /**
     * Output custom styles (delegated to each module)
     */
    public static function output_custom_styles() {
        $options = self::get_options();
        ?>
        <style id="fd-admin-ui-custom-colors">
            <?php
            // Call each module's output_styles method
            FD_Adminbar::output_styles($options);
            FD_Sidebar_Menu::output_styles($options);
            FD_Global_Settings::output_styles($options);
            FD_Content_Area::output_styles($options);
            FD_Table_Settings::output_styles($options);
            FD_Footer_Settings::output_styles($options);
            // The following modules' CSS is handled by other hooks or does not need CSS
            // FD_Search_Settings::output_styles($options);   // Handled by JS
            // FD_Login_Settings::output_styles($options);    // Handled by login_enqueue_scripts
            // FD_Dashboard_Settings::output_styles($options); // Handled by PHP hooks
            ?>
        </style>

        <?php
        // === Adminbar search button JS ===
        $adminbar_style = isset($options['adminbar_style']) ? $options['adminbar_style'] : 'classic';
        if ($adminbar_style === 'modern' && isset($options['adminbar_modern_show_search']) && $options['adminbar_modern_show_search']):
        ?>
        <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                var searchTrigger = document.getElementById('fd-adminbar-search-trigger');
                if (searchTrigger && typeof window.FDSearch !== 'undefined' && typeof window.FDSearch.open === 'function') {
                    searchTrigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        window.FDSearch.open();
                    });
                }
            });
        })();
        </script>
        <?php endif; ?>

        <?php
    }

    /**
     * Get dashicon content code
     * Delegated to FD_Icon_Picker component
     */
    public static function get_dashicon_content($class_name) {
        return FD_Icon_Picker::get_dashicon_content($class_name);
    }

    /**
     * Output site branding styles
     */
    public static function output_site_branding_styles() {
        $options = self::get_options();

        // Check if enabled
        if (!isset($options['menu_branding_enable']) || !$options['menu_branding_enable']) {
            return;
        }

        $logo_full = $options['menu_site_logo_full'];
        $logo_square = $options['menu_site_logo_square'];

        // If neither logo is set, don't display
        if (empty($logo_full) && empty($logo_square)) {
            return;
        }

        ?>
        <style>
            /* Enable flex order for adminmenu */
            #adminmenu {
                display: flex;
                flex-direction: column;
            }

            /* Site branding container */
            #adminmenu #fd-site-branding {
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 4px;
                list-style: none;
                padding: 10px 12px !important;
            }

            /* Main link area */
            #fd-site-branding .fd-branding-link {
                display: flex;
                align-items: center;
                flex: 1;
                text-decoration: none;
                color: inherit;
                border-radius: 6px;
                padding: 4px 6px;
                margin: -4px -6px;
                transition: all 0.2s ease;
                min-width: 0;
            }

            #fd-site-branding .fd-branding-link:hover {
                background: rgba(255, 255, 255, 0.08);
            }

            #fd-site-branding .fd-branding-link:focus {
                outline: none;
                box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
            }

            /* Full Logo - displayed when expanded on desktop */
            #fd-site-branding .fd-logo-full {
                width: auto;
                max-width: 140px;
                height: 20px;
                object-fit: contain;
                display: block !important;
            }

            /* Square Logo - hidden by default, displayed when collapsed */
            #fd-site-branding .fd-logo-square {
                width: 26px;
                height: 26px;
                border-radius: 4px;
                object-fit: cover;
                display: none !important;
            }

            /* Disable left border hover effect for site branding */
            #fd-site-branding .fd-branding-link:hover,
            #fd-site-branding .fd-branding-link:focus,
            #fd-site-branding .fd-quick-icon:hover,
            #fd-site-branding .fd-quick-icon:focus {
                box-shadow: none !important;
            }

            /* Quick access icon */
            #fd-site-branding .fd-quick-icon {
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                color: <?php echo esc_attr($options['menu_text_color']); ?>;
                opacity: 0.6;
                transition: all 0.2s ease;
                flex-shrink: 0;
                text-decoration: none;
                font-size: 16px;
            }

            #fd-site-branding .fd-quick-icon:hover {
                opacity: 1;
                background: rgba(255, 255, 255, 0.1);
                color: <?php echo esc_attr($options['menu_hover_text_color']); ?>;
            }

            #fd-site-branding .fd-quick-icon:focus {
                outline: none;
                box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
            }

            /* Sync with custom menu left padding */
            <?php if (!empty($options['menu_item_left_padding']) && $options['menu_item_left_padding'] !== ''): ?>
            @media only screen and (min-width: 961px) {
                #adminmenu #fd-site-branding {
                    padding-left: <?php echo esc_attr( $options['menu_item_left_padding'] ); ?>px !important;
                }
            }
            <?php endif; ?>

            /* Collapsed state (783-960px auto-collapse range) */
            @media screen and (min-width: 783px) and (max-width: 960px) {
                body.auto-fold #fd-site-branding {
                    justify-content: center;
                    padding: 8px;
                }

                body.auto-fold #fd-site-branding .fd-branding-link {
                    flex: 0;
                }

                /* When collapsed: hide full logo and quick icon, show square logo */
                body.auto-fold #fd-site-branding .fd-logo-full {
                    display: none !important;
                }

                body.auto-fold #fd-site-branding .fd-quick-icon {
                    display: none !important;
                }

                body.auto-fold #fd-site-branding .fd-logo-square {
                    display: block !important;
                    position: relative;
                    left: -12px;
                }
            }

            /* Manually collapsed state (>960px but has folded class) */
            @media screen and (min-width: 961px) {
                body.folded #fd-site-branding {
                    justify-content: center;
                    padding: 8px;
                }

                body.folded #fd-site-branding .fd-branding-link {
                    flex: 0;
                }

                body.folded #fd-site-branding .fd-logo-full {
                    display: none !important;
                }

                body.folded #fd-site-branding .fd-quick-icon {
                    display: none !important;
                }

                body.folded #fd-site-branding .fd-logo-square {
                    display: block !important;
                    position: relative;
                    left: -12px;
                }
            }

            /* Responsive: hidden on small screens by default, but shown when menu is expanded */
            @media screen and (max-width: 782px) {
                #adminmenu #fd-site-branding {
                    display: none;
                }

                /* Show site branding when menu is expanded */
                body.mobile-menu-active #adminmenu #fd-site-branding,
                .wp-responsive-open #adminmenu #fd-site-branding {
                    display: flex;
                }
            }
        </style>
        <?php
    }

    /**
     * Output site branding HTML (output directly into menu via adminmenu hook)
     */
    public static function output_site_branding_html() {
        $options = self::get_options();

        // Check if enabled
        if (!isset($options['menu_branding_enable']) || !$options['menu_branding_enable']) {
            return;
        }

        $logo_full = $options['menu_site_logo_full'];
        $logo_square = $options['menu_site_logo_square'];
        $custom_link = !empty($options['menu_branding_link']) ? $options['menu_branding_link'] : home_url();
        $admin_link = admin_url();
        $show_icon = isset($options['menu_branding_show_icon']) && $options['menu_branding_show_icon'];

        // If neither logo is set, don't display
        if (empty($logo_full) && empty($logo_square)) {
            return;
        }

        ?>
        <li id="fd-site-branding">
            <a href="<?php echo esc_url($admin_link); ?>" class="fd-branding-link">
                <?php if ($logo_full): ?>
                <img src="<?php echo esc_url($logo_full); ?>" alt="Logo" class="fd-logo-full">
                <?php endif; ?>
                <?php if ($logo_square): ?>
                <img src="<?php echo esc_url($logo_square); ?>" alt="Logo" class="fd-logo-square">
                <?php endif; ?>
            </a>
            <?php if ($show_icon): ?>
            <a href="<?php echo esc_url($custom_link); ?>" class="fd-quick-icon" target="_blank" title="<?php esc_attr_e('Visit Front End', 'fd-admin-ui'); ?>">
                <span class="dashicons dashicons-external" style="font-size: 16px; width: 16px; height: 16px;"></span>
            </a>
            <?php endif; ?>
        </li>
        <script>
        // Move site branding to top of menu (DOM structure adjustment)
        (function() {
            var branding = document.getElementById('fd-site-branding');
            var adminmenu = document.getElementById('adminmenu');
            if (branding && adminmenu && adminmenu.firstChild) {
                adminmenu.insertBefore(branding, adminmenu.firstChild);
            }
        })();
        </script>
        <?php
    }

    /**
     * Remove help tabs
     */
    public static function remove_help_tabs() {
        $screen = get_current_screen();
        if ($screen) {
            $screen->remove_help_tabs();
        }
    }

    /**
     * Add user menu to top right when adminbar is hidden
     */
    public static function add_user_menu_when_adminbar_hidden() {
        $options = self::get_options();

        // Only add user menu when adminbar is hidden
        if (!isset($options['adminbar_hide']) || !$options['adminbar_hide']) {
            return;
        }

        $current_user = wp_get_current_user();
        $user_name = $current_user->display_name;
        $avatar = get_avatar($current_user->ID, 32);
        $profile_url = get_edit_profile_url($current_user->ID);
        $logout_url = wp_logout_url();

        ?>
        <style>
        /* User menu container - placed to the left of Screen Options */
        #fd-user-menu-container {
            position: absolute;
            top: 0;
            right: 170px;
            z-index: 99999;
        }

        /* Trigger - underline + small triangle */
        #fd-user-menu-trigger {
            width: 32px;
            height: 5px;
            background: #a0a5aa;
            border-radius: 0 0 4px 4px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
            animation: trigger-pulse 3s ease-in-out infinite;
        }

        @keyframes trigger-pulse {
            0%, 100% {
                opacity: 0.6;
            }
            50% {
                opacity: 1;
            }
        }

        #fd-user-menu-trigger::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #a0a5aa;
            transform: translateX(-50%);
            opacity: 0;
            transition: all 0.3s;
        }

        #fd-user-menu-container:hover #fd-user-menu-trigger {
            background: #3b82f6;
            height: 6px;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }

        #fd-user-menu-container:hover #fd-user-menu-trigger::after {
            opacity: 1;
            border-top-color: #3b82f6;
        }

        /* Dropdown panel */
        #fd-user-menu-panel {
            position: absolute;
            top: 100%;
            right: -8px;
            margin-top: 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.2s ease;
            overflow: hidden;
        }

        #fd-user-menu-container:hover #fd-user-menu-panel {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* Remove blue outline on link click */
        #fd-user-menu-panel a:focus {
            outline: none;
            box-shadow: none;
        }

        /* User info header */
        #fd-user-menu-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
        }

        #fd-user-menu-header .avatar {
            border-radius: 50%;
            width: 36px;
            height: 36px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #fd-user-menu-header .user-info {
            flex: 1;
            min-width: 0;
        }

        #fd-user-menu-header .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #fd-user-menu-header .user-role {
            font-size: 12px;
            color: #64748b;
        }

        /* Menu links */
        #fd-user-menu-panel .menu-links a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            color: #475569;
            text-decoration: none;
            transition: all 0.15s ease;
            font-size: 13px;
        }

        #fd-user-menu-panel .menu-links a:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        #fd-user-menu-panel .menu-links a .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
            color: #94a3b8;
            transition: color 0.15s ease;
        }

        #fd-user-menu-panel .menu-links a:hover .dashicons {
            color: #3b82f6;
        }

        /* Logout link special styles */
        #fd-user-menu-panel .menu-links a.logout-link {
            border-top: 1px solid #f1f5f9;
            color: #64748b;
        }

        #fd-user-menu-panel .menu-links a.logout-link:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        #fd-user-menu-panel .menu-links a.logout-link:hover .dashicons {
            color: #dc2626;
        }

        /* Responsive: hidden on small screens (adminbar will automatically appear) */
        @media only screen and (max-width: 782px) {
            #fd-user-menu-container {
                display: none;
            }
        }
        </style>

        <div id="fd-user-menu-container">
            <div id="fd-user-menu-trigger"></div>
            <div id="fd-user-menu-panel">
                <div id="fd-user-menu-header">
                    <?php echo get_avatar($current_user->ID, 36); ?>
                    <div class="user-info">
                        <span class="user-name"><?php echo esc_html($user_name); ?></span>
                        <span class="user-role"><?php
                            $roles = $current_user->roles;
                            $role_names = array(
                                'administrator' => __('Administrator', 'fd-admin-ui'),
                                'editor' => __('Editor', 'fd-admin-ui'),
                                'author' => __('Author', 'fd-admin-ui'),
                                'contributor' => __('Contributor', 'fd-admin-ui'),
                                'subscriber' => __('Subscriber', 'fd-admin-ui')
                            );
                            $role = reset($roles);
                            echo isset($role_names[$role]) ? esc_html($role_names[$role]) : esc_html(ucfirst($role));
                        ?></span>
                    </div>
                </div>
                <div class="menu-links">
                    <a href="<?php echo esc_url($profile_url); ?>">
                        <span class="dashicons dashicons-admin-users"></span>
                        <span><?php esc_html_e('Profile', 'fd-admin-ui'); ?></span>
                    </a>
                    <a href="<?php echo esc_url($logout_url); ?>" class="logout-link">
                        <span class="dashicons dashicons-exit"></span>
                        <span><?php esc_html_e('Log Out', 'fd-admin-ui'); ?></span>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Custom footer text
     */
    public static function custom_footer_text($text) {
        $options = self::get_options();

        // If custom text is set
        if (!empty($options['footer_custom_text'])) {
            return wp_kses_post($options['footer_custom_text']);
        }

        return $text;
    }

    /**
     * Custom footer version number
     */
    public static function custom_footer_version($text) {
        $options = self::get_options();

        // If hide version number is set
        if (!empty($options['footer_hide_version']) && $options['footer_hide_version']) {
            return '';
        }

        return $text;
    }

    /**
     * Add modern style Adminbar elements
     */
    public static function add_modern_adminbar_elements($wp_admin_bar) {
        $options = self::get_options();

        // If Adminbar is hidden, don't add elements
        if (isset($options['adminbar_hide']) && $options['adminbar_hide']) {
            return;
        }

        // === Handle item display control (applies to both styles) ===
        // Hide updates
        if (isset($options['adminbar_hide_updates']) && $options['adminbar_hide_updates']) {
            $wp_admin_bar->remove_node('updates');
        }

        // Hide comments
        if (isset($options['adminbar_hide_comments']) && $options['adminbar_hide_comments']) {
            $wp_admin_bar->remove_node('comments');
        }

        // Hide new content
        if (isset($options['adminbar_hide_new_content']) && $options['adminbar_hide_new_content']) {
            $wp_admin_bar->remove_node('new-content');
        }

        // Hide plugin-added items
        if (isset($options['adminbar_hide_plugin_items']) && $options['adminbar_hide_plugin_items']) {
            // Common plugin item IDs
            $plugin_nodes = array(
                'graphiql-ide',        // GraphiQL IDE
                'object-cache',        // Object Cache
                'redis-cache',         // Redis Cache
                'w3tc',                // W3 Total Cache
                'wp-rocket',           // WP Rocket
                'autoptimize',         // Autoptimize
                'litespeed-menu',      // LiteSpeed Cache
                'wordfence_admin_bar_menu', // Wordfence
                'itsec_admin_bar_menu',     // iThemes Security
                'query-monitor',       // Query Monitor
                'debug-bar',           // Debug Bar
                'updraft_admin_node',  // UpdraftPlus
                'tribe-events',        // The Events Calendar
                'wpseo-menu',          // Yoast SEO (if user doesn't need it)
            );

            foreach ($plugin_nodes as $node_id) {
                $wp_admin_bar->remove_node($node_id);
            }
        }

        // === The following only applies to modern style ===
        if (!isset($options['adminbar_style']) || $options['adminbar_style'] !== 'modern') {
            return;
        }

        // Add custom Logo (supports both horizontal and square logos, switches when collapsed)
        // Horizontal Logo priority: adminbar_modern_logo > menu_site_logo_full
        $logo_full_url = !empty($options['adminbar_modern_logo'])
            ? $options['adminbar_modern_logo']
            : (!empty($options['menu_site_logo_full']) ? $options['menu_site_logo_full'] : '');

        // Square Logo priority: adminbar_modern_logo_square > menu_site_logo_square
        $logo_square_url = !empty($options['adminbar_modern_logo_square'])
            ? $options['adminbar_modern_logo_square']
            : (!empty($options['menu_site_logo_square']) ? $options['menu_site_logo_square'] : '');

        // If only one type exists, use it as fallback for the other
        if (empty($logo_full_url) && !empty($logo_square_url)) {
            $logo_full_url = $logo_square_url;
        }
        if (empty($logo_square_url) && !empty($logo_full_url)) {
            $logo_square_url = $logo_full_url;
        }

        // Check if both logos exist and are different
        $has_both_logos = !empty($logo_full_url) && !empty($logo_square_url) && $logo_full_url !== $logo_square_url;

        if (!empty($logo_full_url)) {
            if ($has_both_logos) {
                // Both logos exist and are different, output two img tags, toggle display with CSS
                $logo_html = '<img class="fd-logo-full" src="' . esc_url($logo_full_url) . '" alt="Logo">'
                           . '<img class="fd-logo-square" src="' . esc_url($logo_square_url) . '" alt="Logo">';
            } else {
                // Only one type or same, output single img
                $logo_html = '<img src="' . esc_url($logo_full_url) . '" alt="Logo">';
            }

            $wp_admin_bar->add_node(array(
                'id'    => 'fd-adminbar-logo',
                'title' => $logo_html,
                'href'  => admin_url(),
                'meta'  => array(
                    'class' => 'fd-adminbar-logo-node',
                ),
            ));
        }

        // Add search trigger button (click to call FDSearch.open()) - placed in the right toolbar
        if (isset($options['adminbar_modern_show_search']) && $options['adminbar_modern_show_search']) {
            $search_html = '
                <button type="button" id="fd-adminbar-search-trigger" class="fd-adminbar-search-btn">
                    <span class="dashicons dashicons-search"></span>
                    <span class="fd-search-text">' . esc_html__('Search...', 'fd-admin-ui') . '</span>
                    <span class="fd-search-shortcut">Ctrl+K</span>
                </button>
            ';

            $wp_admin_bar->add_node(array(
                'id'     => 'fd-adminbar-search',
                'title'  => $search_html,
                'parent' => 'top-secondary', // Place in right toolbar
                'meta'   => array(
                    'class' => 'fd-adminbar-search-node',
                ),
            ));
        }

        // Add user avatar and display name (placed in the right top-secondary group)
        if (isset($options['adminbar_modern_show_user']) && $options['adminbar_modern_show_user']) {
            $current_user = wp_get_current_user();
            $avatar = get_avatar($current_user->ID, 28, '', '', array('class' => 'fd-user-avatar'));
            $user_name = $current_user->display_name;

            $user_html = $avatar . '<span class="fd-user-name">' . esc_html($user_name) . '</span><span class="fd-user-arrow dashicons dashicons-arrow-down-alt2"></span>';

            $wp_admin_bar->add_node(array(
                'id'     => 'fd-adminbar-user',
                'title'  => $user_html,
                'href'   => get_edit_profile_url($current_user->ID),
                'parent' => 'top-secondary', // Place in right group
                'meta'   => array(
                    'class' => 'fd-adminbar-user-node',
                ),
            ));

            // Add user dropdown menu
            $wp_admin_bar->add_node(array(
                'id'     => 'fd-adminbar-user-profile',
                'title'  => '<span class="dashicons dashicons-admin-users"></span> ' . __('Profile', 'fd-admin-ui'),
                'href'   => get_edit_profile_url($current_user->ID),
                'parent' => 'fd-adminbar-user',
            ));

            $wp_admin_bar->add_node(array(
                'id'     => 'fd-adminbar-user-logout',
                'title'  => '<span class="dashicons dashicons-exit"></span> ' . __('Log Out', 'fd-admin-ui'),
                'href'   => wp_logout_url(),
                'parent' => 'fd-adminbar-user',
            ));
        }
    }
}

// Initialize settings
FD_Admin_UI_Settings::init();
