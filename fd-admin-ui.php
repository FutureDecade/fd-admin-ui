<?php
/**
 * Plugin Name: FD Admin UI
 * Plugin URI: https://futuredecade.com
 * Description: A modern, lightweight admin UI framework for WordPress. Customize colors, menus, login page, global search, and more.
 * Version: 1.3.2
 * Author: Future Decade
 * Author URI: https://futuredecade.com
 * Text Domain: fd-admin-ui
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Define plugin constants
define('FD_ADMIN_UI_VERSION', '1.3.2');
define('FD_ADMIN_UI_FILE', __FILE__);
define('FD_ADMIN_UI_PATH', plugin_dir_path(__FILE__));
define('FD_ADMIN_UI_URI', plugin_dir_url(__FILE__));

if (!defined('FD_ADMIN_UI_BRAND_LOGO_FULL_URL')) {
    define('FD_ADMIN_UI_BRAND_LOGO_FULL_URL', 'https://img.futuredecade.com/s3/fd_logo_full.svg');
}

if (!defined('FD_ADMIN_UI_BRAND_LOGO_SQUARE_URL')) {
    define('FD_ADMIN_UI_BRAND_LOGO_SQUARE_URL', 'https://img.futuredecade.com/s3/fd_logo_square.svg');
}

if (!defined('FD_ADMIN_UI_LOGIN_BG_IMAGE_URL')) {
    define('FD_ADMIN_UI_LOGIN_BG_IMAGE_URL', 'https://img.futuredecade.com/s3/fd_login_register_bg.svg');
}

if (!defined('FD_ADMIN_UI_ADMIN_FAVICON_URL')) {
    define('FD_ADMIN_UI_ADMIN_FAVICON_URL', 'https://img.futuredecade.com/s3/logo_square.png');
}

// Load FD Design Tokens shared library (version-competitive loading)
require_once FD_ADMIN_UI_PATH . 'lib/fd-design-tokens/fd-design-tokens.php';

// Load core classes
require_once FD_ADMIN_UI_PATH . 'includes/class-fd-admin-ui.php';
require_once FD_ADMIN_UI_PATH . 'includes/class-fd-icon-picker.php';
require_once FD_ADMIN_UI_PATH . 'includes/class-fd-admin-ui-settings.php';
require_once FD_ADMIN_UI_PATH . 'includes/class-fd-global-search.php';
require_once FD_ADMIN_UI_PATH . 'includes/class-fd-menu-sort.php';
require_once FD_ADMIN_UI_PATH . 'includes/class-fd-login-customizer.php';
require_once FD_ADMIN_UI_PATH . 'includes/class-fd-auto-fold-menu.php';

// Load modules
require_once FD_ADMIN_UI_PATH . 'includes/modules/class-fd-post-meta-box.php';
require_once FD_ADMIN_UI_PATH . 'includes/modules/class-fd-taxonomy-box.php';

/**
 * Plugin initialization
 */
function fd_admin_ui_init() {
    // Text domain loading is handled automatically since WP 4.6+.
}
add_action('plugins_loaded', 'fd_admin_ui_init');

/**
 * Enqueue global assets on all admin pages
 * This applies a unified modern UI style across the entire WordPress admin
 */
function fd_admin_ui_enqueue_global_assets($hook) {
    // Global styles - override WordPress core admin styles
    wp_enqueue_style(
        'fd-admin-global',
        FD_ADMIN_UI_URI . 'assets/css/fd-admin-global.css',
        array(),
        FD_ADMIN_UI_VERSION
    );

    // Component style library
    wp_enqueue_style(
        'fd-admin-ui',
        FD_ADMIN_UI_URI . 'assets/css/fd-admin-ui.css',
        array('fd-admin-global'),
        FD_ADMIN_UI_VERSION
    );

    // WordPress settings page optimization styles
    wp_enqueue_style(
        'fd-wp-settings',
        FD_ADMIN_UI_URI . 'assets/css/fd-wp-settings.css',
        array('fd-admin-global'),
        FD_ADMIN_UI_VERSION
    );

    // Dynamically inject VI settings CSS variables
    fd_admin_ui_inject_vi_css_variables();

    // WordPress color picker (loaded on demand)
    wp_enqueue_style('wp-color-picker');

    // Global JavaScript enhancements
    wp_enqueue_script(
        'fd-admin-global',
        FD_ADMIN_UI_URI . 'assets/js/fd-admin-global.js',
        array('jquery'),
        FD_ADMIN_UI_VERSION,
        true
    );

    // Component interaction scripts
    wp_enqueue_script(
        'fd-admin-ui',
        FD_ADMIN_UI_URI . 'assets/js/fd-admin-ui.js',
        array('jquery', 'wp-color-picker', 'jquery-ui-sortable'),
        FD_ADMIN_UI_VERSION,
        true
    );

    // Pass configuration to JavaScript
    wp_localize_script('fd-admin-ui', 'fdAdminUI', array(
        'version' => FD_ADMIN_UI_VERSION,
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fd_admin_ui_nonce'),
        'i18n' => array(
            'loading'    => __( 'Loading...', 'fd-admin-ui' ),
            'copied'     => __( 'Copied', 'fd-admin-ui' ),
            'unsaved'    => __( 'Unsaved...', 'fd-admin-ui' ),
            'saved'      => __( 'Saved', 'fd-admin-ui' ),
            'saveFailed' => __( 'Save failed', 'fd-admin-ui' ),
            'processing' => __( 'Processing...', 'fd-admin-ui' ),
            'success'    => __( 'Operation successful', 'fd-admin-ui' ),
            'error'      => __( 'Operation failed', 'fd-admin-ui' ),
        ),
    ));
}
add_action('admin_enqueue_scripts', 'fd_admin_ui_enqueue_global_assets', 5); // Priority 5 to ensure loading before other scripts

/**
 * Dynamically inject VI settings CSS variables
 * Uses the FD Design Tokens shared library to generate a complete color scale system
 * Falls back to legacy logic if the library is not available
 */
function fd_admin_ui_inject_vi_css_variables() {
    // Use FD Design Tokens library to generate complete tokens
    if ( class_exists( 'FD_Design_Tokens' ) ) {
        $tokens = FD_Design_Tokens::from_options();
        $css  = "/* FD Design Tokens v" . FD_Design_Tokens::VERSION . " */\n";
        // Only output light mode, dark mode support removed
        $css .= $tokens->to_css();
    } else {
        // Fallback: legacy simple variable generation
        $css = fd_admin_ui_inject_vi_css_variables_legacy();
    }

    // Append WordPress native element override styles
    $css .= fd_admin_ui_get_element_overrides_css();

    wp_add_inline_style( 'fd-admin-global', $css );
}

/**
 * Legacy VI CSS variable generation (fallback)
 *
 * @return string CSS string
 */
function fd_admin_ui_inject_vi_css_variables_legacy() {
    $current_theme = wp_get_theme();
    $is_fd_theme = (
        $current_theme->get_template() === 'fd-theme' ||
        $current_theme->get('TextDomain') === 'fd-theme' ||
        function_exists('fd_register_graphql_vi_settings')
    );

    if ($is_fd_theme) {
        $vi_settings = array(
            'primary_color' => get_option('fd_primary_color', '#3b82f6'),
            'action_color'  => get_option('fd_action_color', ''),
            'secondary_color' => get_option('fd_secondary_color', '#4285f4'),
            'success_color' => get_option('fd_success_color', '#10b981'),
            'error_color' => get_option('fd_error_color', '#ef4444'),
            'warning_color' => get_option('fd_amber_color', '#f59e0b'),
            'radius_small' => get_option('fd_radius_small', '4px'),
            'radius_medium' => get_option('fd_radius_medium', '8px'),
            'radius_large' => get_option('fd_radius_large', '16px'),
            'shadow_small' => get_option('fd_shadow_small', '0 1px 2px 0 rgba(0, 0, 0, 0.05)'),
            'shadow_medium' => get_option('fd_shadow_medium', '0 4px 6px -1px rgba(0, 0, 0, 0.1)'),
            'shadow_large' => get_option('fd_shadow_large', '0 10px 15px -3px rgba(0, 0, 0, 0.1)'),
        );
    } else {
        $options = FD_Admin_UI_Settings::get_options();
        $vi_settings = array(
            'primary_color' => isset($options['vi_primary_color']) ? $options['vi_primary_color'] : '#3b82f6',
            'action_color'  => isset($options['vi_action_color']) ? $options['vi_action_color'] : '',
            'secondary_color' => isset($options['vi_secondary_color']) ? $options['vi_secondary_color'] : '#4285f4',
            'success_color' => isset($options['vi_success_color']) ? $options['vi_success_color'] : '#10b981',
            'error_color' => isset($options['vi_error_color']) ? $options['vi_error_color'] : '#ef4444',
            'warning_color' => isset($options['vi_warning_color']) ? $options['vi_warning_color'] : '#f59e0b',
            'radius_small' => isset($options['vi_radius_small']) ? $options['vi_radius_small'] : '4px',
            'radius_medium' => isset($options['vi_radius_medium']) ? $options['vi_radius_medium'] : '8px',
            'radius_large' => isset($options['vi_radius_large']) ? $options['vi_radius_large'] : '16px',
            'shadow_small' => isset($options['vi_shadow_small']) ? $options['vi_shadow_small'] : '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
            'shadow_medium' => isset($options['vi_shadow_medium']) ? $options['vi_shadow_medium'] : '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
            'shadow_large' => isset($options['vi_shadow_large']) ? $options['vi_shadow_large'] : '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
        );
    }

    $css = "/* FD Admin UI - Legacy VI Settings (fallback mode) */\n";
    $css .= ":root {\n";
    $css .= "    --fd-primary: {$vi_settings['primary_color']};\n";
    $css .= "    --fd-primary-hover: " . fd_admin_ui_darken_color($vi_settings['primary_color'], 10) . ";\n";
    $css .= "    --fd-primary-light: " . fd_admin_ui_lighten_color($vi_settings['primary_color'], 40) . ";\n";
    $action_color = !empty($vi_settings['action_color']) ? $vi_settings['action_color'] : $vi_settings['primary_color'];
    $css .= "    --fd-action: {$action_color};\n";
    $css .= "    --fd-action-hover: " . fd_admin_ui_darken_color($action_color, 10) . ";\n";
    $css .= "    --fd-success: {$vi_settings['success_color']};\n";
    $css .= "    --fd-success-light: " . fd_admin_ui_lighten_color($vi_settings['success_color'], 40) . ";\n";
    $css .= "    --fd-error: {$vi_settings['error_color']};\n";
    $css .= "    --fd-error-light: " . fd_admin_ui_lighten_color($vi_settings['error_color'], 40) . ";\n";
    $css .= "    --fd-warning: {$vi_settings['warning_color']};\n";
    $css .= "    --fd-warning-light: " . fd_admin_ui_lighten_color($vi_settings['warning_color'], 40) . ";\n";
    $css .= "    --fd-info: {$vi_settings['secondary_color']};\n";
    $css .= "    --fd-info-light: " . fd_admin_ui_lighten_color($vi_settings['secondary_color'], 40) . ";\n";
    $css .= "    --fd-radius-sm: {$vi_settings['radius_small']};\n";
    $css .= "    --fd-radius-md: {$vi_settings['radius_medium']};\n";
    $css .= "    --fd-radius-lg: {$vi_settings['radius_medium']};\n";
    $css .= "    --fd-radius-xl: {$vi_settings['radius_large']};\n";
    $css .= "    --fd-shadow-xs: {$vi_settings['shadow_small']};\n";
    $css .= "    --fd-shadow-sm: {$vi_settings['shadow_small']};\n";
    $css .= "    --fd-shadow-md: {$vi_settings['shadow_medium']};\n";
    $css .= "    --fd-shadow-lg: {$vi_settings['shadow_large']};\n";
    $css .= "    --fd-shadow-xl: {$vi_settings['shadow_large']};\n";
    $css .= "}\n";

    return $css;
}

/**
 * Get WordPress native element override CSS
 * These rules reference CSS variables and do not depend on specific color values
 *
 * @return string CSS string
 */
function fd_admin_ui_get_element_overrides_css() {
    $css = "\n/* Apply VI colors to key elements */\n";

    // Primary buttons (using --fd-action, independent of brand primary color)
    $css .= ".button-primary,\n";
    $css .= ".wp-core-ui .button-primary {\n";
    $css .= "    background: var(--fd-action) !important;\n";
    $css .= "    border-color: var(--fd-action) !important;\n";
    $css .= "    box-shadow: none !important;\n";
    $css .= "    text-shadow: none !important;\n";
    $css .= "    color: #fff !important;\n";
    $css .= "}\n";
    $css .= ".button-primary:hover,\n";
    $css .= ".wp-core-ui .button-primary:hover {\n";
    $css .= "    background: var(--fd-action-hover) !important;\n";
    $css .= "    border-color: var(--fd-action-hover) !important;\n";
    $css .= "    color: #fff !important;\n";
    $css .= "}\n";

    // Link colors: default grayscale, hover brand color (exclude button-like links)
    $css .= "#wpcontent a:not([class*=\"button\"]):not(.page-title-action):not(.add-new-h2),\n";
    $css .= "#wpfooter a:not([class*=\"button\"]) {\n";
    $css .= "    color: var(--fd-gray-800);\n";
    $css .= "    transition: color 0.15s ease;\n";
    $css .= "}\n";
    $css .= "#wpcontent a:not([class*=\"button\"]):not(.page-title-action):not(.add-new-h2):hover,\n";
    $css .= "#wpfooter a:not([class*=\"button\"]):hover {\n";
    $css .= "    color: var(--fd-primary);\n";
    $css .= "}\n";
    $css .= "#wpcontent a:focus,\n";
    $css .= "#wpfooter a:focus {\n";
    $css .= "    outline: none !important;\n";
    $css .= "    box-shadow: none !important;\n";
    $css .= "}\n";

    // Secondary buttons (grayscale)
    $css .= ".button,\n";
    $css .= ".wp-core-ui .button {\n";
    $css .= "    background: var(--fd-gray-100);\n";
    $css .= "    border-color: var(--fd-gray-200);\n";
    $css .= "    color: var(--fd-gray-700);\n";
    $css .= "}\n";
    $css .= ".button:hover,\n";
    $css .= ".wp-core-ui .button:hover {\n";
    $css .= "    background: var(--fd-gray-200);\n";
    $css .= "    border-color: var(--fd-gray-300);\n";
    $css .= "    color: var(--fd-gray-900);\n";
    $css .= "}\n";

    // Input submit buttons (using --fd-action)
    $css .= "input[type=\"submit\"],\n";
    $css .= "input[type=\"button\"] {\n";
    $css .= "    background: var(--fd-action);\n";
    $css .= "    border-color: var(--fd-action);\n";
    $css .= "    color: #fff;\n";
    $css .= "}\n";
    $css .= "input[type=\"submit\"]:hover,\n";
    $css .= "input[type=\"button\"]:hover {\n";
    $css .= "    background: var(--fd-action-hover);\n";
    $css .= "    border-color: var(--fd-action-hover);\n";
    $css .= "}\n";

    // Table row action links (edit, delete, etc.)
    $css .= ".row-actions a {\n";
    $css .= "    color: var(--fd-gray-600);\n";
    $css .= "}\n";
    $css .= ".row-actions a:hover {\n";
    $css .= "    color: var(--fd-primary);\n";
    $css .= "}\n";

    // Quick edit / delete links
    $css .= ".inline-edit-row .submit {\n";
    $css .= "    color: var(--fd-gray-600);\n";
    $css .= "}\n";
    $css .= ".inline-edit-row .submit a {\n";
    $css .= "    color: var(--fd-primary);\n";
    $css .= "}\n";

    // Admin content area background (using --fd-surface token)
    $css .= "\n/* Admin content area background */\n";
    $css .= "#wpcontent,\n";
    $css .= "#wpbody-content {\n";
    $css .= "    background: var(--fd-surface, #f8fafc);\n";
    $css .= "}\n";

    // Unified form focus state (using --fd-primary)
    $css .= "\n/* Unified form focus state */\n";
    $css .= "input[type=\"text\"]:focus,\n";
    $css .= "input[type=\"password\"]:focus,\n";
    $css .= "input[type=\"email\"]:focus,\n";
    $css .= "input[type=\"url\"]:focus,\n";
    $css .= "input[type=\"search\"]:focus,\n";
    $css .= "input[type=\"number\"]:focus,\n";
    $css .= "input[type=\"tel\"]:focus,\n";
    $css .= "textarea:focus,\n";
    $css .= "select:focus {\n";
    $css .= "    border-color: var(--fd-primary) !important;\n";
    $css .= "    box-shadow: 0 0 0 1px var(--fd-primary) !important;\n";
    $css .= "    outline: none !important;\n";
    $css .= "}\n";

    return $css;
}

/**
 * Helper function: darken a color
 *
 * @deprecated 2.0.0 Use FD_Color::from_hex($hex)->darken($percent)->to_hex()
 * @param string $hex Hex color value
 * @param int $percent Darken percentage
 * @return string Darkened color
 */
function fd_admin_ui_darken_color($hex, $percent) {
    if ( class_exists( 'FD_Color' ) ) {
        return FD_Color::from_hex( $hex )->darken( $percent )->to_hex();
    }

    // Legacy RGB fallback
    $hex = str_replace('#', '', $hex);

    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = (int) round(max(0, min(255, $r - ($r * $percent / 100))));
    $g = (int) round(max(0, min(255, $g - ($g * $percent / 100))));
    $b = (int) round(max(0, min(255, $b - ($b * $percent / 100))));

    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
              . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
              . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

/**
 * Helper function: lighten a color
 *
 * @deprecated 2.0.0 Use FD_Color::from_hex($hex)->lighten($percent)->to_hex()
 * @param string $hex Hex color value
 * @param int $percent Lighten percentage
 * @return string Lightened color
 */
function fd_admin_ui_lighten_color($hex, $percent) {
    if ( class_exists( 'FD_Color' ) ) {
        return FD_Color::from_hex( $hex )->lighten( $percent )->to_hex();
    }

    // Legacy RGB fallback
    $hex = str_replace('#', '', $hex);

    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = (int) round(max(0, min(255, $r + ((255 - $r) * $percent / 100))));
    $g = (int) round(max(0, min(255, $g + ((255 - $g) * $percent / 100))));
    $b = (int) round(max(0, min(255, $b + ((255 - $b) * $percent / 100))));

    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
              . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
              . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

/**
 * Add plugin settings link
 */
function fd_admin_ui_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=fd-admin-ui-settings') . '">' . __('Settings', 'fd-admin-ui') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'fd_admin_ui_plugin_action_links');

/**
 * Plugin activation hook
 */
function fd_admin_ui_activate() {
    add_option('fd_admin_ui_options', FD_Admin_UI_Settings::get_default_options());
    add_option('fd_admin_menu_order', FD_Menu_Sort::get_default_menu_order());
    add_option('fd_admin_submenu_order', FD_Menu_Sort::get_default_submenu_order());
    add_option('fd_admin_custom_separators', FD_Menu_Sort::get_default_custom_separators());
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'fd_admin_ui_activate');

/**
 * Plugin deactivation hook
 */
function fd_admin_ui_deactivate() {
    // Actions on plugin deactivation
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'fd_admin_ui_deactivate');

/**
 * Add table wrapper script and anti-flicker styles in the head
 * Executes before DOM rendering to completely avoid flicker
 */
function fd_admin_ui_add_table_wrapper_early() {
    // Get the current screen
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }

    // Check if the page requires horizontal scrolling
    $scroll_pages = array('edit', 'users', 'edit-comments');
    if (!in_array($screen->base, $scroll_pages)) {
        return;
    }

    // Check if horizontal scrolling is enabled
    $options = FD_Admin_UI_Settings::get_options();
    $table_scroll = isset($options['table_horizontal_scroll']) ? $options['table_horizontal_scroll'] : true;

    if (!$table_scroll) {
        return;
    }

    // Set different selectors for different pages
    $selectors = array(
        'edit' => '#posts-filter .wp-list-table',
        'users' => '.users-php .wp-list-table',
        'edit-comments' => '.edit-comments-php .wp-list-table'
    );

    $selector = isset($selectors[$screen->base]) ? $selectors[$screen->base] : '.wp-list-table';

    ?>
    <style id="fd-table-scroll-preload">
        /* Anti-flicker: hide table before wrapper is added */
        <?php echo esc_attr( $selector ); ?>:not(.fd-wrapped) {
            visibility: hidden;
        }
    </style>
    <script>
    (function() {
        // Execute immediately before DOMContentLoaded
        function wrapTable() {
            var table = document.querySelector('<?php echo esc_attr( $selector ); ?>');
            if (table && !table.classList.contains('fd-wrapped')) {
                var wrapper = document.createElement('div');
                wrapper.className = 'fd-table-scroll-wrapper';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
                table.classList.add('fd-wrapped');

                // Remove anti-flicker style
                var preloadStyle = document.getElementById('fd-table-scroll-preload');
                if (preloadStyle) {
                    preloadStyle.remove();
                }
            }
        }

        // Execute as early as possible
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', wrapTable);
        } else {
            wrapTable();
        }
    })();
    </script>
    <?php
}
add_action('admin_head', 'fd_admin_ui_add_table_wrapper_early', 999);

/**
 * Add modern style body class for all list pages
 */
function fd_admin_ui_add_table_modern_class($classes) {
    // Get the current screen
    $screen = get_current_screen();
    if (!$screen) {
        return $classes;
    }

    // Check if this is a list page (posts, pages, users, comments, etc.)
    $list_bases = array('edit', 'users', 'edit-comments', 'upload');
    if (!in_array($screen->base, $list_bases)) {
        return $classes;
    }

    // Get settings options
    $options = FD_Admin_UI_Settings::get_options();

    // Check if modern style is enabled
    $modern_style = isset($options['table_modern_style']) ? $options['table_modern_style'] : true;
    if ($modern_style) {
        $classes .= ' fd-table-modern-style';
    }

    return $classes;
}
add_filter('admin_body_class', 'fd_admin_ui_add_table_modern_class');

/**
 * Add modern style body class for settings and tools pages
 */
function fd_admin_ui_add_settings_modern_class($classes) {
    global $pagenow;

    // Get the current screen
    $screen = get_current_screen();
    if (!$screen) {
        return $classes;
    }

    // Check if this is a settings or tools page
    $settings_pages = array(
        'options-general',    // General Settings
        'options-writing',    // Writing Settings
        'options-reading',    // Reading Settings
        'options-discussion', // Discussion Settings
        'options-media',      // Media Settings
        'options-permalink',  // Permalink Settings
        'options-privacy',    // Privacy Settings
        'tools',              // Tools
        'import',             // Import
        'export',             // Export
        'export-personal-data', // Export Personal Data
        'erase-personal-data',  // Erase Personal Data
        'site-health',        // Site Health
        'user-new',           // Add New User
        'user',               // Add New User (alternative)
        'profile',            // Profile
        'user-edit',          // Edit User
    );

    // Check page filenames
    $settings_page_files = array(
        'options-general.php',
        'options-writing.php',
        'options-reading.php',
        'options-discussion.php',
        'options-media.php',
        'options-permalink.php',
        'options-privacy.php',
        'tools.php',
        'import.php',
        'export.php',
        'export-personal-data.php',
        'erase-personal-data.php',
        'site-health.php',
        'user-new.php',
        'profile.php',
        'user-edit.php',
        'edit-tags.php',      // Taxonomy and tag management
        'term.php',           // Edit taxonomy/tag
    );

    // Check screen->base, screen->id and pagenow simultaneously
    $is_settings_page = in_array($screen->base, $settings_pages)
                     || in_array($screen->id, $settings_pages)
                     || in_array($pagenow, $settings_page_files);

    if (!$is_settings_page) {
        return $classes;
    }

    // Get settings options
    $options = FD_Admin_UI_Settings::get_options();

    // Check if settings page modernization is enabled
    $modern_style = isset($options['settings_modern_style']) ? $options['settings_modern_style'] : true;
    if ($modern_style) {
        $classes .= ' fd-settings-modern-style';
    }

    return $classes;
}
add_filter('admin_body_class', 'fd_admin_ui_add_settings_modern_class');

/**
 * Customize post list author column output
 */
function fd_admin_ui_custom_author_column($actions, $post) {
    // Get settings options
    $options = FD_Admin_UI_Settings::get_options();
    $modern_style = isset($options['table_modern_style']) ? $options['table_modern_style'] : true;

    // Only process when modern style is enabled
    if (!$modern_style) {
        return $actions;
    }

    // This hook is used to trigger our CSS and JS
    return $actions;
}
add_filter('post_row_actions', 'fd_admin_ui_custom_author_column', 10, 2);
add_filter('page_row_actions', 'fd_admin_ui_custom_author_column', 10, 2);

/**
 * Add avatars to the author column via JavaScript
 */
function fd_admin_ui_add_author_avatar_script() {
    // Only execute on post list pages
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, array('edit'))) {
        return;
    }

    // Get settings options
    $options = FD_Admin_UI_Settings::get_options();
    $modern_style = isset($options['table_modern_style']) ? $options['table_modern_style'] : true;

    if (!$modern_style) {
        return;
    }

    ?>
    <script>
    (function() {
        function addAuthorAvatars() {
            var authorCells = document.querySelectorAll('.wp-list-table .column-author');

            if (authorCells.length === 0) {
                return;
            }

            authorCells.forEach(function(cell) {
                // Skip already processed cells
                if (cell.querySelector('.fd-author-with-avatar')) {
                    return;
                }

                // Get author link
                var authorLink = cell.querySelector('a');
                if (!authorLink) {
                    return;
                }

                // Extract user ID from link URL (supports both author= and user_id= formats)
                var href = authorLink.getAttribute('href');
                var match = href.match(/[?&]author=(\d+)/) || href.match(/[?&]user_id=(\d+)/);

                if (!match) {
                    return;
                }

                var userId = match[1];

                // Get author name
                var authorName = authorLink.textContent.trim();

                // Use WordPress REST API to get user avatar
                fetch('/wp-json/wp/v2/users/' + userId)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(user) {
                        if (!user.avatar_urls || !user.avatar_urls['48']) {
                            return;
                        }

                        var avatarUrl = user.avatar_urls['48'];

                        // Create new structure
                        var wrapper = document.createElement('div');
                        wrapper.className = 'fd-author-with-avatar';

                        var avatar = document.createElement('img');
                        avatar.src = avatarUrl;
                        avatar.className = 'fd-author-avatar';
                        avatar.width = 32;
                        avatar.height = 32;
                        avatar.alt = authorName;

                        var span = document.createElement('span');
                        span.textContent = authorName;

                        wrapper.appendChild(avatar);
                        wrapper.appendChild(span);

                        // Clear link content and add new structure
                        authorLink.innerHTML = '';
                        authorLink.appendChild(wrapper);

                    })
                    .catch(function() {});
            });
        }

        // Execute after page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', addAuthorAvatars);
        } else {
            // Delay execution to ensure table is rendered
            setTimeout(addAuthorAvatars, 100);
        }
    })();
    </script>
    <?php
}
add_action('admin_footer', 'fd_admin_ui_add_author_avatar_script');
