<?php
/**
 * FD Admin UI - Adminbar Module
 * Manages Adminbar settings, styles and functionality
 */

defined('ABSPATH') || exit;

class FD_Adminbar {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            // === Adminbar Basic Settings ===
            'adminbar_hide' => false,
            'adminbar_style' => 'modern',  // classic or modern

            // === Adminbar Classic Style Settings (shared by both styles) ===
            'adminbar_bg_color' => '#ffffff',
            'adminbar_text_color' => '#333333',
            'adminbar_hover_bg_color' => '',           // Hover background color (empty = transparent)
            'adminbar_hover_text_color' => '#2962ff',  // Hover text color
            'adminbar_submenu_bg_color' => '#ffffff',
            'adminbar_submenu_text_color' => '#333333',
            'adminbar_hide_wp_logo' => true,  // Hide left WP Logo

            // === Adminbar Modern Style Exclusive Settings ===
            'adminbar_modern_height' => 44,           // Height px
            'adminbar_modern_font_size' => 13,        // Font size px
            'adminbar_modern_logo' => FD_ADMIN_UI_BRAND_LOGO_FULL_URL,             // Custom Logo (horizontal/full)
            'adminbar_modern_logo_square' => FD_ADMIN_UI_BRAND_LOGO_SQUARE_URL,     // Custom Logo (square, used when collapsed)
            'adminbar_modern_logo_height' => 20,      // Logo height px
            'adminbar_modern_show_search' => true,    // Show search box
            'adminbar_modern_show_user' => true,      // Show avatar + nickname
            'adminbar_hide_updates' => false,         // Hide "Updates"
            'adminbar_hide_comments' => false,        // Hide "Comments"
            'adminbar_hide_new_content' => false,     // Hide "New"
            'adminbar_hide_plugin_items' => false,    // Hide plugin-added items
        );
    }

    /**
     * Sanitize module options
     */
    public static function sanitize($input, $sanitized) {
        // === Adminbar Basic Settings ===
        if (isset($input['adminbar_hide'])) {
            $sanitized['adminbar_hide'] = (bool) $input['adminbar_hide'];
        }

        if (isset($input['adminbar_style'])) {
            $sanitized['adminbar_style'] = in_array($input['adminbar_style'], array('classic', 'modern'))
                ? $input['adminbar_style']
                : 'classic';
        }

        // === Adminbar Color Settings ===
        if (isset($input['adminbar_bg_color'])) {
            $sanitized['adminbar_bg_color'] = FD_Admin_UI_Settings::sanitize_color($input['adminbar_bg_color']);
        }

        if (isset($input['adminbar_text_color'])) {
            $sanitized['adminbar_text_color'] = FD_Admin_UI_Settings::sanitize_color($input['adminbar_text_color']);
        }

        if (isset($input['adminbar_hover_bg_color'])) {
            $sanitized['adminbar_hover_bg_color'] = FD_Admin_UI_Settings::sanitize_color($input['adminbar_hover_bg_color']);
        }

        if (isset($input['adminbar_hover_text_color'])) {
            $sanitized['adminbar_hover_text_color'] = FD_Admin_UI_Settings::sanitize_color($input['adminbar_hover_text_color']);
        }

        if (isset($input['adminbar_submenu_bg_color'])) {
            $sanitized['adminbar_submenu_bg_color'] = FD_Admin_UI_Settings::sanitize_color($input['adminbar_submenu_bg_color']);
        }

        if (isset($input['adminbar_submenu_text_color'])) {
            $sanitized['adminbar_submenu_text_color'] = FD_Admin_UI_Settings::sanitize_color($input['adminbar_submenu_text_color']);
        }

        $sanitized['adminbar_hide_wp_logo'] = isset($input['adminbar_hide_wp_logo']) && $input['adminbar_hide_wp_logo'];

        // === Adminbar Modern Style Exclusive Settings ===
        if (isset($input['adminbar_modern_height'])) {
            $sanitized['adminbar_modern_height'] = max(32, min(100, absint($input['adminbar_modern_height'])));
        }

        if (isset($input['adminbar_modern_font_size'])) {
            $sanitized['adminbar_modern_font_size'] = max(12, min(18, absint($input['adminbar_modern_font_size'])));
        }

        if (isset($input['adminbar_modern_logo'])) {
            $sanitized['adminbar_modern_logo'] = esc_url_raw($input['adminbar_modern_logo']);
        }

        if (isset($input['adminbar_modern_logo_square'])) {
            $sanitized['adminbar_modern_logo_square'] = esc_url_raw($input['adminbar_modern_logo_square']);
        }

        if (isset($input['adminbar_modern_logo_height'])) {
            $sanitized['adminbar_modern_logo_height'] = max(16, min(60, absint($input['adminbar_modern_logo_height'])));
        }

        $sanitized['adminbar_modern_show_search'] = isset($input['adminbar_modern_show_search']) && $input['adminbar_modern_show_search'];
        $sanitized['adminbar_modern_show_user'] = isset($input['adminbar_modern_show_user']) && $input['adminbar_modern_show_user'];

        // === Adminbar Item Display Controls ===
        $sanitized['adminbar_hide_updates'] = isset($input['adminbar_hide_updates']) && $input['adminbar_hide_updates'];
        $sanitized['adminbar_hide_comments'] = isset($input['adminbar_hide_comments']) && $input['adminbar_hide_comments'];
        $sanitized['adminbar_hide_new_content'] = isset($input['adminbar_hide_new_content']) && $input['adminbar_hide_new_content'];
        $sanitized['adminbar_hide_plugin_items'] = isset($input['adminbar_hide_plugin_items']) && $input['adminbar_hide_plugin_items'];

        return $sanitized;
    }

    /**
     * Output module CSS
     */
    public static function output_styles($options) {
        $adminbar_style = isset($options['adminbar_style']) ? $options['adminbar_style'] : 'modern';

        // Hide Adminbar (only effective on large screens, preserving the hamburger menu on small screens)
        if (isset($options['adminbar_hide']) && $options['adminbar_hide']) {
            echo '@media only screen and (min-width: 783px) { #wpadminbar { display: none !important; } html.wp-toolbar { padding-top: 0 !important; } }';
        }

        // Hide WP Logo
        if (isset($options['adminbar_hide_wp_logo']) && $options['adminbar_hide_wp_logo']) {
            echo '#wpadminbar #wp-admin-bar-wp-logo { display: none !important; }';
        }

        $hover_bg_general = isset($options['adminbar_hover_bg_color']) ? $options['adminbar_hover_bg_color'] : '';
        $hover_text_general = !empty($options['adminbar_hover_text_color']) ? $options['adminbar_hover_text_color'] : '#2962ff';
        ?>
        /* Adminbar custom colors - desktop only, small screens use WP native styles */
        @media screen and (min-width: 783px) {
        /* Adminbar background color */
        #wpadminbar {
            background: <?php echo esc_attr($options['adminbar_bg_color']); ?> !important;
        }

        /* Adminbar text color - high priority selectors to override WP defaults */
        #wpadminbar .ab-item,
        #wpadminbar #wp-admin-bar-root-default > li > .ab-item,
        #wpadminbar #wp-admin-bar-top-secondary > li > .ab-item,
        #wpadminbar #wp-admin-bar-root-default > li > .ab-item .ab-icon:before,
        #wpadminbar #wp-admin-bar-top-secondary > li > .ab-item .ab-icon:before,
        #wpadminbar #wp-admin-bar-root-default > li > .ab-item .ab-label,
        #wpadminbar #wp-admin-bar-top-secondary > li > .ab-item .ab-label,
        #wpadminbar .ab-icon:before,
        #wpadminbar .ab-item:before,
        #wpadminbar #adminbarsearch:before {
            color: <?php echo esc_attr($options['adminbar_text_color']); ?> !important;
        }

        /* Adminbar base state - explicitly set transparent background to avoid flicker on hover leave */
        #wpadminbar .ab-top-menu > li,
        #wpadminbar .ab-top-menu > li > .ab-item {
            background-color: transparent !important;
            transition: color 0.15s ease, background-color 0.15s ease !important;
        }

        /* Adminbar hover and dropdown trigger state (.hover class is WP dropdown trigger class) */
        #wpadminbar .ab-top-menu > li:hover,
        #wpadminbar .ab-top-menu > li.hover {
            background-color: transparent !important;
        }

        /* Adminbar hover color */
        #wpadminbar #wp-admin-bar-root-default > li:hover > .ab-item,
        #wpadminbar #wp-admin-bar-top-secondary > li:hover > .ab-item,
        #wpadminbar #wp-admin-bar-root-default > li.hover > .ab-item,
        #wpadminbar #wp-admin-bar-top-secondary > li.hover > .ab-item,
        #wpadminbar #wp-admin-bar-root-default > li:hover > .ab-item .ab-icon:before,
        #wpadminbar #wp-admin-bar-top-secondary > li:hover > .ab-item .ab-icon:before,
        #wpadminbar #wp-admin-bar-root-default > li.hover > .ab-item .ab-icon:before,
        #wpadminbar #wp-admin-bar-top-secondary > li.hover > .ab-item .ab-icon:before,
        #wpadminbar #wp-admin-bar-root-default > li:hover > .ab-item .ab-label,
        #wpadminbar #wp-admin-bar-top-secondary > li:hover > .ab-item .ab-label,
        #wpadminbar #wp-admin-bar-root-default > li.hover > .ab-item .ab-label,
        #wpadminbar #wp-admin-bar-top-secondary > li.hover > .ab-item .ab-label,
        #wpadminbar .ab-top-menu > li > .ab-item:hover,
        #wpadminbar .ab-top-menu > li > .ab-item:focus {
            color: <?php echo esc_attr($hover_text_general); ?> !important;
        }

        /* Adminbar hover background color */
        <?php if (!empty($hover_bg_general)): ?>
        #wpadminbar .ab-top-menu > li:hover > .ab-item,
        #wpadminbar .ab-top-menu > li.hover > .ab-item,
        #wpadminbar .ab-top-menu > li > .ab-item:hover,
        #wpadminbar .ab-top-menu > li > .ab-item:focus {
            background-color: <?php echo esc_attr($hover_bg_general); ?> !important;
        }
        <?php else: ?>
        /* Hover background color is empty, ensure transparent */
        #wpadminbar .ab-top-menu > li:hover > .ab-item,
        #wpadminbar .ab-top-menu > li.hover > .ab-item,
        #wpadminbar .ab-top-menu > li > .ab-item:hover,
        #wpadminbar .ab-top-menu > li > .ab-item:focus {
            background-color: transparent !important;
        }
        <?php endif; ?>

        /* Dropdown menu background color */
        #wpadminbar .ab-submenu,
        #wpadminbar .quicklinks .menupop .ab-sub-wrapper {
            background: <?php echo esc_attr($options['adminbar_submenu_bg_color']); ?> !important;
        }

        /* Dropdown menu text color */
        #wpadminbar .ab-submenu .ab-item,
        #wpadminbar .quicklinks .menupop .ab-sub-wrapper .ab-item {
            color: <?php echo esc_attr($options['adminbar_submenu_text_color']); ?> !important;
        }

        /* Dropdown menu hover color */
        #wpadminbar .ab-submenu .ab-item:hover,
        #wpadminbar .quicklinks .menupop ul li a:hover,
        #wpadminbar .quicklinks .menupop.hover ul li a:hover {
            color: <?php echo esc_attr($hover_text_general); ?> !important;
        }
        } /* end @media min-width: 783px */

        <?php
        // Modern style CSS
        self::output_modern_styles($options);
    }

    /**
     * Output modern style CSS (private method, split for maintainability)
     */
    private static function output_modern_styles($options) {
        $adminbar_style = isset($options['adminbar_style']) ? $options['adminbar_style'] : 'modern';
        $adminbar_hidden = isset($options['adminbar_hide']) && $options['adminbar_hide'];

        // Only apply modern style when modern style is selected and adminbar is not hidden
        if ($adminbar_style !== 'modern' || $adminbar_hidden) {
            return;
        }

        $modern_height = isset($options['adminbar_modern_height']) ? absint($options['adminbar_modern_height']) : 44;
        $modern_font_size = isset($options['adminbar_modern_font_size']) ? absint($options['adminbar_modern_font_size']) : 13;
        $height_diff = $modern_height - 32;

        // Logo: horizontal/full version (desktop)
        $modern_logo_full = !empty($options['adminbar_modern_logo'])
            ? $options['adminbar_modern_logo']
            : (!empty($options['menu_site_logo_full']) ? $options['menu_site_logo_full'] : '');

        // Logo: square version (when menu is collapsed)
        $modern_logo_square = !empty($options['adminbar_modern_logo_square'])
            ? $options['adminbar_modern_logo_square']
            : (!empty($options['menu_site_logo_square']) ? $options['menu_site_logo_square'] : '');

        // If horizontal logo is empty, try using square logo as fallback
        if (empty($modern_logo_full) && !empty($modern_logo_square)) {
            $modern_logo_full = $modern_logo_square;
        }
        if (empty($modern_logo_square) && !empty($modern_logo_full)) {
            $modern_logo_square = $modern_logo_full;
        }

        $has_both_logos = !empty($modern_logo_full) && !empty($modern_logo_square) && $modern_logo_full !== $modern_logo_square;

        // Logo area width matches the left menu
        $menu_width = !empty($options['menu_width']) ? absint($options['menu_width']) : 160;

        // Calculate logo left padding
        if (isset($options['menu_item_left_padding']) && $options['menu_item_left_padding'] !== '') {
            $logo_left_padding = intval($options['menu_item_left_padding']) + 8;
        } else if (!empty($options['menu_width'])) {
            $logo_left_padding = max(0, ($menu_width - 160) / 2) + 8;
        } else {
            $logo_left_padding = 8;
        }

        // In modern style, need to additionally consider menu item left margin
        $menu_style = isset($options['menu_style']) ? $options['menu_style'] : 'classic';
        if ($menu_style === 'modern' && isset($options['menu_item_left_margin']) && $options['menu_item_left_margin'] !== '') {
            $logo_left_padding += intval($options['menu_item_left_margin']);
        }

        // Detect adminbar background color brightness
        $bg_hex = ltrim($options['adminbar_bg_color'], '#');
        if (strlen($bg_hex) === 3) {
            $bg_hex = $bg_hex[0].$bg_hex[0].$bg_hex[1].$bg_hex[1].$bg_hex[2].$bg_hex[2];
        }
        $bg_r = hexdec(substr($bg_hex, 0, 2));
        $bg_g = hexdec(substr($bg_hex, 2, 2));
        $bg_b = hexdec(substr($bg_hex, 4, 2));
        $is_light_bg = (($bg_r * 299 + $bg_g * 587 + $bg_b * 114) / 1000) > 128;

        $hover_bg = isset($options['adminbar_hover_bg_color']) ? $options['adminbar_hover_bg_color'] : '';
        $hover_text = isset($options['adminbar_hover_text_color']) ? $options['adminbar_hover_text_color'] : '#2962ff';
        ?>
        /* ========================================
           Modern SaaS Style Adminbar
           ======================================== */
        @media screen and (min-width: 783px) {
            /* ---- Overall height ---- */
            #wpadminbar {
                height: <?php echo esc_attr( $modern_height ); ?>px !important;
            }

            /* ---- quicklinks container uses flex for left-right separation ---- */
            #wpadminbar .quicklinks {
                display: flex !important;
                align-items: center !important;
                height: <?php echo esc_attr( $modern_height ); ?>px !important;
                width: 100% !important;
            }

            #wpadminbar #wp-admin-bar-root-default {
                display: flex !important;
                align-items: center !important;
                height: <?php echo esc_attr( $modern_height ); ?>px !important;
                flex: 1 1 auto !important;
            }

            #wpadminbar #wp-admin-bar-top-secondary {
                display: flex !important;
                align-items: center !important;
                height: <?php echo esc_attr( $modern_height ); ?>px !important;
                margin-left: auto !important;
                flex-shrink: 0 !important;
                padding-right: 12px !important;
            }

            #wpadminbar .quicklinks > ul > li {
                height: <?php echo esc_attr( $modern_height ); ?>px !important;
                display: flex !important;
                align-items: center !important;
            }

            #wpadminbar .ab-top-menu > li > .ab-item {
                height: <?php echo esc_attr( $modern_height ); ?>px !important;
                line-height: <?php echo esc_attr( $modern_height ); ?>px !important;
                padding: 0 12px !important;
                font-size: <?php echo esc_attr( $modern_font_size ); ?>px !important;
            }

            /* ---- Hover effects ---- */
            #wpadminbar .ab-top-menu > li,
            #wpadminbar .ab-top-menu > li > .ab-item {
                background-color: transparent !important;
                transition: color 0.15s ease !important;
            }

            #wpadminbar .ab-top-menu > li:hover,
            #wpadminbar .ab-top-menu > li.hover {
                background-color: transparent !important;
            }

            #wpadminbar .ab-top-menu > li:hover > .ab-item,
            #wpadminbar .ab-top-menu > li.hover > .ab-item,
            #wpadminbar .ab-top-menu > li > .ab-item:focus {
                <?php if (!empty($hover_bg)): ?>
                background-color: <?php echo esc_attr($hover_bg); ?> !important;
                <?php else: ?>
                background-color: transparent !important;
                <?php endif; ?>
                color: <?php echo esc_attr($hover_text); ?> !important;
            }

            #wpadminbar .ab-top-menu > li:hover > .ab-item .ab-icon:before,
            #wpadminbar .ab-top-menu > li.hover > .ab-item .ab-icon:before,
            #wpadminbar .ab-top-menu > li > .ab-item:focus .ab-icon:before {
                color: <?php echo esc_attr($hover_text); ?> !important;
            }

            #wpadminbar .ab-submenu li,
            #wpadminbar .ab-submenu li > .ab-item {
                background-color: transparent !important;
                transition: color 0.15s ease !important;
            }

            #wpadminbar .ab-submenu li:hover > .ab-item,
            #wpadminbar .ab-submenu li.hover > .ab-item {
                color: <?php echo esc_attr($hover_text); ?> !important;
                background-color: transparent !important;
            }

            /* ---- Icon vertical alignment fix ---- */
            #wpadminbar .ab-top-menu > li > .ab-item {
                display: inline-flex !important;
                align-items: center !important;
                gap: 4px !important;
            }

            #wpadminbar .ab-top-menu .ab-icon {
                position: static !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: auto !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                float: none !important;
            }

            #wpadminbar .ab-top-menu .ab-icon:before {
                position: static !important;
                display: inline-block !important;
                width: 20px !important;
                height: 20px !important;
                font-size: 20px !important;
                line-height: 20px !important;
                text-align: center !important;
                top: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            #wpadminbar .ab-top-menu .ab-label {
                display: inline-block !important;
                margin: 0 !important;
                padding: 0 2px !important;
            }

            /* ---- Element ordering ---- */
            #wpadminbar #wp-admin-bar-menu-toggle {
                display: none !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-logo {
                order: -100 !important;
            }

            <?php if (!empty($modern_logo_full)): ?>
            #wpadminbar #wp-admin-bar-wp-logo {
                display: none !important;
            }
            #wpadminbar #wp-admin-bar-site-name {
                display: none !important;
            }
            <?php endif; ?>

            /* ---- Page spacing adjustment ---- */
            html.wp-toolbar {
                padding-top: <?php echo esc_attr( $modern_height ); ?>px !important;
            }

            #adminmenuwrap {
                margin-top: <?php echo esc_attr( $height_diff ); ?>px !important;
            }

            #wpadminbar .ab-sub-wrapper {
                top: <?php echo esc_attr( $modern_height ); ?>px !important;
            }

            /* ---- Logo styles ---- */
            #wpadminbar #wp-admin-bar-fd-adminbar-logo {
                padding: 0 !important;
                width: <?php echo esc_attr( $menu_width ); ?>px !important;
                box-sizing: border-box !important;
                flex-shrink: 0 !important;
                margin-right: 10px !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-logo > .ab-item {
                padding: 0 12px 0 <?php echo esc_attr( $logo_left_padding ); ?>px !important;
                height: <?php echo esc_attr( $modern_height ); ?>px !important;
                display: flex !important;
                align-items: center !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-logo img {
                height: <?php echo isset($options['adminbar_modern_logo_height']) ? absint($options['adminbar_modern_logo_height']) : 20; ?>px !important;
                width: auto !important;
                display: block !important;
            }

            <?php if ($has_both_logos): ?>
            #wpadminbar #wp-admin-bar-fd-adminbar-logo .fd-logo-square {
                display: none !important;
            }
            <?php endif; ?>

            /* ---- Search button ---- */
            #wpadminbar #wp-admin-bar-fd-adminbar-search {
                padding: 0 !important;
                margin: 0 16px !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-search > .ab-item {
                padding: 0 !important;
                height: <?php echo esc_attr( $modern_height ); ?>px !important;
                display: flex !important;
                align-items: center !important;
            }

            .fd-adminbar-search-btn {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                width: 260px !important;
                height: 28px !important;
                padding: 0 14px !important;
                box-sizing: border-box !important;
                background: <?php echo $is_light_bg ? 'rgba(0,0,0,0.04)' : 'rgba(255,255,255,0.1)'; ?> !important;
                border: 1px solid <?php echo $is_light_bg ? 'rgba(0,0,0,0.12)' : 'rgba(255,255,255,0.15)'; ?> !important;
                border-radius: 9999px !important;
                color: <?php echo $is_light_bg ? 'rgba(0,0,0,0.4)' : 'rgba(255,255,255,0.6)'; ?> !important;
                font-size: 13px !important;
                cursor: pointer !important;
                transition: all 0.2s !important;
                font-family: inherit !important;
            }

            .fd-adminbar-search-btn:hover {
                background: <?php echo $is_light_bg ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.15)'; ?> !important;
                border-color: <?php echo $is_light_bg ? 'rgba(0,0,0,0.2)' : 'rgba(255,255,255,0.25)'; ?> !important;
                color: <?php echo $is_light_bg ? 'rgba(0,0,0,0.6)' : 'rgba(255,255,255,0.8)'; ?> !important;
            }

            .fd-adminbar-search-btn .dashicons {
                font-family: dashicons !important;
                font-size: 16px !important;
                width: 16px !important;
                height: 16px !important;
                line-height: 1 !important;
                display: inline-block !important;
                -webkit-font-smoothing: antialiased !important;
                -moz-osx-font-smoothing: grayscale !important;
            }

            .fd-adminbar-search-btn .fd-search-text {
                flex: 1 !important;
                text-align: left !important;
            }

            .fd-adminbar-search-btn .fd-search-shortcut {
                font-size: 10px !important;
                background: <?php echo $is_light_bg ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.1)'; ?> !important;
                padding: 1px 5px !important;
                border-radius: 3px !important;
                color: <?php echo $is_light_bg ? 'rgba(0,0,0,0.35)' : 'rgba(255,255,255,0.5)'; ?> !important;
                line-height: 1.4 !important;
            }

            /* ---- User avatar ---- */
            #wpadminbar #wp-admin-bar-fd-adminbar-user > .ab-item {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                padding: 0 12px !important;
                height: <?php echo esc_attr( $modern_height ); ?>px !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-user .fd-user-avatar {
                width: 28px !important;
                height: 28px !important;
                border-radius: 50% !important;
                flex-shrink: 0 !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-user .fd-user-name {
                font-size: <?php echo esc_attr( $modern_font_size ); ?>px !important;
                color: <?php echo esc_attr($options['adminbar_text_color']); ?> !important;
                font-weight: 500 !important;
                transition: color 0.2s !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-user:hover .fd-user-name {
                color: <?php echo esc_attr($hover_text); ?> !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-user .fd-user-arrow {
                font-family: dashicons !important;
                font-size: 14px !important;
                width: 14px !important;
                height: 14px !important;
                line-height: 1 !important;
                opacity: 0.6 !important;
                display: inline-block !important;
                -webkit-font-smoothing: antialiased !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-user .ab-submenu {
                min-width: 140px !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-user .ab-submenu .ab-item {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                transition: color 0.2s !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-user .ab-submenu .ab-item:hover {
                color: <?php echo esc_attr($hover_text); ?> !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-user .ab-submenu .dashicons {
                font-family: dashicons !important;
                font-size: 16px !important;
                width: 16px !important;
                height: 16px !important;
                line-height: 1 !important;
                display: inline-block !important;
                -webkit-font-smoothing: antialiased !important;
            }

            <?php if (isset($options['adminbar_modern_show_user']) && $options['adminbar_modern_show_user']): ?>
            #wpadminbar #wp-admin-bar-my-account {
                display: none !important;
            }
            <?php endif; ?>
        }

        /* Desktop medium width (961-1109px) */
        @media screen and (min-width: 961px) and (max-width: 1109px) {
            #wpadminbar #wp-admin-bar-graphiql-ide,
            #wpadminbar #wp-admin-bar-redis-cache,
            #wpadminbar #wp-admin-bar-object-cache,
            #wpadminbar #wp-admin-bar-query-monitor,
            #wpadminbar #wp-admin-bar-debug-bar,
            #wpadminbar #wp-admin-bar-w3tc,
            #wpadminbar #wp-admin-bar-wp-rocket,
            #wpadminbar #wp-admin-bar-litespeed-menu,
            #wpadminbar #wp-admin-bar-autoptimize,
            #wpadminbar #wp-admin-bar-wpseo-menu,
            #wpadminbar #wp-admin-bar-updraft_admin_node,
            #wpadminbar #wp-admin-bar-tribe-events {
                display: none !important;
            }

            .fd-adminbar-search-btn .fd-search-shortcut {
                display: none !important;
            }
        }

        /* Menu collapsed (783-960px) */
        @media screen and (min-width: 783px) and (max-width: 960px) {
            #wpadminbar #wp-admin-bar-graphiql-ide,
            #wpadminbar #wp-admin-bar-redis-cache,
            #wpadminbar #wp-admin-bar-object-cache,
            #wpadminbar #wp-admin-bar-query-monitor,
            #wpadminbar #wp-admin-bar-debug-bar,
            #wpadminbar #wp-admin-bar-w3tc,
            #wpadminbar #wp-admin-bar-wp-rocket,
            #wpadminbar #wp-admin-bar-litespeed-menu,
            #wpadminbar #wp-admin-bar-autoptimize,
            #wpadminbar #wp-admin-bar-wpseo-menu,
            #wpadminbar #wp-admin-bar-updraft_admin_node,
            #wpadminbar #wp-admin-bar-tribe-events {
                display: none !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-logo {
                width: 36px !important;
                margin-right: 10px !important;
                <?php
                // In collapsed state, square logo also needs to consider left margin
                if ($menu_style === 'modern' && isset($options['menu_item_left_margin']) && $options['menu_item_left_margin'] !== '') {
                    echo 'margin-left: ' . intval($options['menu_item_left_margin']) . 'px !important;';
                }
                ?>
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-logo > .ab-item {
                padding: 0 8px !important;
                justify-content: center !important;
            }

            #wpadminbar #wp-admin-bar-fd-adminbar-logo img {
                height: 20px !important;
            }

            <?php if ($has_both_logos): ?>
            #wpadminbar #wp-admin-bar-fd-adminbar-logo .fd-logo-full {
                display: none !important;
            }
            #wpadminbar #wp-admin-bar-fd-adminbar-logo .fd-logo-square {
                display: block !important;
            }
            <?php endif; ?>

            .fd-adminbar-search-btn {
                width: 160px !important;
            }

            .fd-adminbar-search-btn .fd-search-shortcut {
                display: none !important;
            }
        }

        /* Small screens (782px and below) */
        @media screen and (max-width: 782px) {
            #wpadminbar #wp-admin-bar-fd-adminbar-logo,
            #wpadminbar #wp-admin-bar-fd-adminbar-search,
            #wpadminbar #wp-admin-bar-fd-adminbar-user {
                display: none !important;
            }
        }
        <?php
    }

    /**
     * Get options (from main class)
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Register Adminbar related settings
     */
    public static function register_settings() {
        // ========================================
        // Adminbar Settings
        // ========================================

        // Adminbar Basic Settings
        add_settings_section(
            'fd_adminbar_basic_section',
            __('Basic Settings', 'fd-admin-ui'),
            array(__CLASS__, 'basic_section_callback'),
            'fd-admin-ui-settings-adminbar'
        );

        add_settings_field(
            'adminbar_hide',
            __('Hide Adminbar', 'fd-admin-ui'),
            array(__CLASS__, 'hide_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_basic_section'
        );

        add_settings_field(
            'adminbar_style',
            __('Adminbar Style', 'fd-admin-ui'),
            array(__CLASS__, 'style_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_basic_section'
        );

        // Adminbar Color Settings (shared by classic and modern)
        add_settings_section(
            'fd_adminbar_color_section',
            __('Color Settings', 'fd-admin-ui'),
            array(__CLASS__, 'color_section_callback'),
            'fd-admin-ui-settings-adminbar'
        );

        add_settings_field(
            'adminbar_bg_color',
            __('Background Color', 'fd-admin-ui'),
            array(__CLASS__, 'bg_color_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_color_section'
        );

        add_settings_field(
            'adminbar_text_color',
            __('Text Color', 'fd-admin-ui'),
            array(__CLASS__, 'text_color_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_color_section'
        );

        add_settings_field(
            'adminbar_hover_bg_color',
            __('Hover Background Color', 'fd-admin-ui'),
            array(__CLASS__, 'hover_bg_color_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_color_section'
        );

        add_settings_field(
            'adminbar_hover_text_color',
            __('Hover Text Color', 'fd-admin-ui'),
            array(__CLASS__, 'hover_text_color_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_color_section'
        );

        add_settings_field(
            'adminbar_submenu_bg_color',
            __('Dropdown Menu Background Color', 'fd-admin-ui'),
            array(__CLASS__, 'submenu_bg_color_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_color_section'
        );

        add_settings_field(
            'adminbar_submenu_text_color',
            __('Dropdown Menu Text Color', 'fd-admin-ui'),
            array(__CLASS__, 'submenu_text_color_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_color_section'
        );

        add_settings_field(
            'adminbar_hide_wp_logo',
            __('Hide WP Logo', 'fd-admin-ui'),
            array(__CLASS__, 'hide_wp_logo_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_color_section'
        );

        // Adminbar Modern Style Exclusive Settings
        add_settings_section(
            'fd_adminbar_modern_section',
            __('Modern Style Settings', 'fd-admin-ui'),
            array(__CLASS__, 'modern_section_callback'),
            'fd-admin-ui-settings-adminbar'
        );

        add_settings_field(
            'adminbar_modern_height',
            __('Adminbar Height', 'fd-admin-ui'),
            array(__CLASS__, 'modern_height_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_modern_section'
        );

        add_settings_field(
            'adminbar_modern_font_size',
            __('Font Size', 'fd-admin-ui'),
            array(__CLASS__, 'modern_font_size_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_modern_section'
        );

        add_settings_field(
            'adminbar_modern_logo',
            __('Custom Logo', 'fd-admin-ui'),
            array(__CLASS__, 'modern_logo_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_modern_section'
        );

        add_settings_field(
            'adminbar_modern_show_search',
            __('Show Search Box', 'fd-admin-ui'),
            array(__CLASS__, 'modern_show_search_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_modern_section'
        );

        add_settings_field(
            'adminbar_modern_show_user',
            __('Show User Info', 'fd-admin-ui'),
            array(__CLASS__, 'modern_show_user_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_modern_section'
        );

        // Adminbar Item Display Controls
        add_settings_section(
            'fd_adminbar_items_section',
            __('Item Display Controls', 'fd-admin-ui'),
            array(__CLASS__, 'items_section_callback'),
            'fd-admin-ui-settings-adminbar'
        );

        add_settings_field(
            'adminbar_hide_updates',
            __('Hide "Updates"', 'fd-admin-ui'),
            array(__CLASS__, 'hide_updates_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_items_section'
        );

        add_settings_field(
            'adminbar_hide_comments',
            __('Hide "Comments"', 'fd-admin-ui'),
            array(__CLASS__, 'hide_comments_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_items_section'
        );

        add_settings_field(
            'adminbar_hide_new_content',
            __('Hide "New"', 'fd-admin-ui'),
            array(__CLASS__, 'hide_new_content_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_items_section'
        );

        add_settings_field(
            'adminbar_hide_plugin_items',
            __('Hide Plugin-Added Items', 'fd-admin-ui'),
            array(__CLASS__, 'hide_plugin_items_callback'),
            'fd-admin-ui-settings-adminbar',
            'fd_adminbar_items_section'
        );
    }

    // ========================================
    // Section Callbacks
    // ========================================

    public static function basic_section_callback() {
        echo '<p>' . esc_html__('Adminbar basic settings.', 'fd-admin-ui') . '</p>';
    }

    public static function color_section_callback() {
        echo '<p>' . esc_html__('Customize the Adminbar color scheme.', 'fd-admin-ui') . '</p>';
    }

    public static function modern_section_callback() {
        echo '<p>' . esc_html__('Modern SaaS style exclusive settings, only effective when "Modern SaaS Style" is selected.', 'fd-admin-ui') . '</p>';
    }

    public static function items_section_callback() {
        echo '<p>' . esc_html__('Control the visibility of items in the Adminbar.', 'fd-admin-ui') . '</p>';
    }

    // ========================================
    // Field Callbacks
    // ========================================

    public static function hide_callback() {
        $options = self::get_options();
        $checked = isset($options['adminbar_hide']) && $options['adminbar_hide'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[adminbar_hide]" value="1" <?php echo esc_attr( $checked ); ?> id="adminbar_hide_toggle">
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('When enabled, the top Adminbar will be hidden (hamburger menu is preserved on small screens)', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function style_callback() {
        $options = self::get_options();
        $value = isset($options['adminbar_style']) ? $options['adminbar_style'] : 'modern';
        ?>
        <div class="fd-style-selector">
            <label class="fd-style-option">
                <input type="radio" name="fd_admin_ui_options[adminbar_style]" value="classic" <?php checked($value, 'classic'); ?>>
                <div class="fd-style-preview fd-style-classic">
                    <div class="fd-preview-bar">
                        <span class="fd-preview-logo">W</span>
                        <span class="fd-preview-items">● ● ●</span>
                    </div>
                    <div class="fd-style-label"><?php esc_html_e('WP Classic Style', 'fd-admin-ui'); ?></div>
                </div>
            </label>

            <label class="fd-style-option">
                <input type="radio" name="fd_admin_ui_options[adminbar_style]" value="modern" <?php checked($value, 'modern'); ?>>
                <div class="fd-style-preview fd-style-modern">
                    <div class="fd-preview-bar">
                        <span class="fd-preview-logo">LOGO</span>
                        <span class="fd-preview-search">&#x1f50d; <?php esc_html_e('Search...', 'fd-admin-ui'); ?></span>
                        <span class="fd-preview-user">&#x1f464;</span>
                    </div>
                    <div class="fd-style-label"><?php esc_html_e('Modern SaaS Style', 'fd-admin-ui'); ?></div>
                </div>
            </label>
        </div>

        <style>
        .fd-style-selector {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .fd-style-option {
            cursor: pointer;
            display: block;
        }
        .fd-style-option input[type="radio"] {
            display: none;
        }
        .fd-style-preview {
            width: 200px;
            border: 3px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.2s;
        }
        .fd-style-option input[type="radio"]:checked + .fd-style-preview {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }
        .fd-style-preview:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .fd-preview-bar {
            background: #1d2327;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            color: #f0f0f1;
        }
        .fd-style-modern .fd-preview-bar {
            padding: 12px;
            justify-content: space-between;
        }
        .fd-preview-logo {
            font-weight: bold;
        }
        .fd-preview-search {
            background: rgba(255,255,255,0.1);
            padding: 4px 8px;
            border-radius: 4px;
            flex: 1;
            text-align: left;
            font-size: 10px;
        }
        .fd-preview-user {
            font-size: 14px;
        }
        .fd-preview-items {
            opacity: 0.5;
            letter-spacing: 2px;
        }
        .fd-style-label {
            background: #f5f5f5;
            padding: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            color: #1e293b;
        }
        </style>

        <p class="description" style="margin-top: 15px;"><?php esc_html_e('Choose the overall Adminbar style. "Modern SaaS Style" is inspired by Laravel Nova and offers more customization options.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function hide_wp_logo_callback() {
        $options = self::get_options();
        $checked = isset($options['adminbar_hide_wp_logo']) && $options['adminbar_hide_wp_logo'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[adminbar_hide_wp_logo]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Hide the WordPress Logo and its dropdown menu on the far left of the Adminbar', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function modern_height_callback() {
        $options = self::get_options();
        $value = isset($options['adminbar_modern_height']) ? $options['adminbar_modern_height'] : 44;
        ?>
        <input type="number"
               name="fd_admin_ui_options[adminbar_modern_height]"
               value="<?php echo esc_attr($value); ?>"
               class="fd-form-input"
               min="32"
               max="100"
               step="2"
               style="width: 80px;"> px
        <p class="description"><?php esc_html_e('Adminbar height (32-100 px), default 56px. WordPress native height is 32px.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function modern_font_size_callback() {
        $options = self::get_options();
        $value = isset($options['adminbar_modern_font_size']) ? $options['adminbar_modern_font_size'] : 13;
        ?>
        <select name="fd_admin_ui_options[adminbar_modern_font_size]">
            <option value="12" <?php selected($value, 12); ?>>12px</option>
            <option value="13" <?php selected($value, 13); ?>>13px (<?php esc_html_e('WP Default', 'fd-admin-ui'); ?>)</option>
            <option value="14" <?php selected($value, 14); ?>>14px</option>
            <option value="15" <?php selected($value, 15); ?>>15px</option>
            <option value="16" <?php selected($value, 16); ?>>16px</option>
        </select>
        <p class="description"><?php esc_html_e('Adminbar font size, default 14px', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function modern_logo_callback() {
        $options = self::get_options();
        $logo = isset($options['adminbar_modern_logo']) ? $options['adminbar_modern_logo'] : '';
        $logo_square = isset($options['adminbar_modern_logo_square']) ? $options['adminbar_modern_logo_square'] : '';
        $logo_height = isset($options['adminbar_modern_logo_height']) ? $options['adminbar_modern_logo_height'] : 20;
        ?>
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <!-- Horizontal/Full Logo -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;"><?php esc_html_e('Horizontal Logo (Desktop)', 'fd-admin-ui'); ?></label>
                <div class="fd-image-upload-wrap">
                    <input type="text"
                           name="fd_admin_ui_options[adminbar_modern_logo]"
                           value="<?php echo esc_attr($logo); ?>"
                           class="fd-form-input"
                           id="adminbar_modern_logo_input"
                           style="width: 300px;">
                    <button type="button" class="fd-btn fd-btn-secondary fd-upload-btn" data-target="adminbar_modern_logo_input"><?php esc_html_e('Select', 'fd-admin-ui'); ?></button>
                    <?php if ($logo) : ?>
                        <button type="button" class="fd-btn fd-btn-secondary fd-remove-btn" data-target="adminbar_modern_logo_input"><?php esc_html_e('Remove', 'fd-admin-ui'); ?></button>
                    <?php endif; ?>
                </div>
                <?php if ($logo) : ?>
                    <div class="fd-image-preview" style="margin-top: 10px;">
                        <img src="<?php echo esc_url($logo); ?>" style="max-width: 160px; max-height: 32px; background: #1d2327; padding: 6px 10px; border-radius: 4px;">
                    </div>
                <?php endif; ?>
                <p class="description" style="margin-top: 8px;"><?php esc_html_e('Recommended size: height 56-80px, width auto', 'fd-admin-ui'); ?></p>
            </div>

            <!-- Square Logo -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;"><?php esc_html_e('Square Logo (Menu Collapsed)', 'fd-admin-ui'); ?></label>
                <div class="fd-image-upload-wrap">
                    <input type="text"
                           name="fd_admin_ui_options[adminbar_modern_logo_square]"
                           value="<?php echo esc_attr($logo_square); ?>"
                           class="fd-form-input"
                           id="adminbar_modern_logo_square_input"
                           style="width: 300px;">
                    <button type="button" class="fd-btn fd-btn-secondary fd-upload-btn" data-target="adminbar_modern_logo_square_input"><?php esc_html_e('Select', 'fd-admin-ui'); ?></button>
                    <?php if ($logo_square) : ?>
                        <button type="button" class="fd-btn fd-btn-secondary fd-remove-btn" data-target="adminbar_modern_logo_square_input"><?php esc_html_e('Remove', 'fd-admin-ui'); ?></button>
                    <?php endif; ?>
                </div>
                <?php if ($logo_square) : ?>
                    <div class="fd-image-preview" style="margin-top: 10px;">
                        <img src="<?php echo esc_url($logo_square); ?>" style="max-width: 32px; max-height: 32px; background: #1d2327; padding: 6px; border-radius: 4px;">
                    </div>
                <?php endif; ?>
                <p class="description" style="margin-top: 8px;"><?php esc_html_e('Recommended size: 56x56px square', 'fd-admin-ui'); ?></p>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;"><?php esc_html_e('Logo Height', 'fd-admin-ui'); ?></label>
            <input type="number"
                   name="fd_admin_ui_options[adminbar_modern_logo_height]"
                   value="<?php echo esc_attr($logo_height); ?>"
                   class="fd-form-input"
                   min="16"
                   max="60"
                   step="2"
                   style="width: 80px;"> px
            <p class="description"><?php esc_html_e('Desktop logo display height (16-60 px), default 28px.', 'fd-admin-ui'); ?></p>
        </div>
        <p class="description" style="margin-top: 10px; color: #646970;">
            <?php esc_html_e('If not set, the logo from "Left Menu Settings > Site Identity" will be used automatically.', 'fd-admin-ui'); ?>
        </p>
        <?php
    }

    public static function modern_show_search_callback() {
        $options = self::get_options();
        $checked = isset($options['adminbar_modern_show_search']) && $options['adminbar_modern_show_search'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[adminbar_modern_show_search]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Display a global search box in the center of the Adminbar, similar to Laravel Nova / Notion style', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function modern_show_user_callback() {
        $options = self::get_options();
        $checked = isset($options['adminbar_modern_show_user']) && $options['adminbar_modern_show_user'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[adminbar_modern_show_user]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Display user avatar and nickname on the right side of the Adminbar, replacing the native WordPress "Howdy, xxx"', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function hide_updates_callback() {
        $options = self::get_options();
        $checked = isset($options['adminbar_hide_updates']) && $options['adminbar_hide_updates'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[adminbar_hide_updates]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Hide the "Updates" button in the Adminbar', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function hide_comments_callback() {
        $options = self::get_options();
        $checked = isset($options['adminbar_hide_comments']) && $options['adminbar_hide_comments'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[adminbar_hide_comments]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Hide the "Comments" button in the Adminbar', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function hide_new_content_callback() {
        $options = self::get_options();
        $checked = isset($options['adminbar_hide_new_content']) && $options['adminbar_hide_new_content'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[adminbar_hide_new_content]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Hide the "New" button in the Adminbar', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function hide_plugin_items_callback() {
        $options = self::get_options();
        $checked = isset($options['adminbar_hide_plugin_items']) && $options['adminbar_hide_plugin_items'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[adminbar_hide_plugin_items]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Hide items added to the Adminbar by third-party plugins (e.g., GraphiQL IDE, Object Cache, etc.)', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function bg_color_callback() {
        $options = self::get_options();
        $value = isset($options['adminbar_bg_color']) ? $options['adminbar_bg_color'] : '#ffffff';
        ?>
        <input type="text"
               name="fd_admin_ui_options[adminbar_bg_color]"
               value="<?php echo esc_attr($value); ?>"
               class="fd-color-picker">
        <p class="description"><?php esc_html_e('Default: #ffffff', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function text_color_callback() {
        $options = self::get_options();
        $value = isset($options['adminbar_text_color']) ? $options['adminbar_text_color'] : '#333333';
        ?>
        <input type="text"
               name="fd_admin_ui_options[adminbar_text_color]"
               value="<?php echo esc_attr($value); ?>"
               class="fd-color-picker">
        <p class="description"><?php esc_html_e('Default: #333333', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function hover_bg_color_callback() {
        $options = self::get_options();
        $value = isset($options['adminbar_hover_bg_color']) ? $options['adminbar_hover_bg_color'] : '';
        ?>
        <input type="text"
               name="fd_admin_ui_options[adminbar_hover_bg_color]"
               value="<?php echo esc_attr($value); ?>"
               class="fd-color-picker">
        <p class="description"><?php esc_html_e('Leave empty for transparent (no background color change)', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function hover_text_color_callback() {
        $options = self::get_options();
        $value = isset($options['adminbar_hover_text_color']) ? $options['adminbar_hover_text_color'] : '#2962ff';
        ?>
        <input type="text"
               name="fd_admin_ui_options[adminbar_hover_text_color]"
               value="<?php echo esc_attr($value); ?>"
               class="fd-color-picker">
        <p class="description"><?php esc_html_e('Default: #2962ff', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function submenu_bg_color_callback() {
        $options = self::get_options();
        $value = isset($options['adminbar_submenu_bg_color']) ? $options['adminbar_submenu_bg_color'] : '#ffffff';
        ?>
        <input type="text"
               name="fd_admin_ui_options[adminbar_submenu_bg_color]"
               value="<?php echo esc_attr($value); ?>"
               class="fd-color-picker">
        <p class="description"><?php esc_html_e('Default: #ffffff', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function submenu_text_color_callback() {
        $options = self::get_options();
        $value = isset($options['adminbar_submenu_text_color']) ? $options['adminbar_submenu_text_color'] : '#333333';
        ?>
        <input type="text"
               name="fd_admin_ui_options[adminbar_submenu_text_color]"
               value="<?php echo esc_attr($value); ?>"
               class="fd-color-picker">
        <p class="description"><?php esc_html_e('Default: #333333', 'fd-admin-ui'); ?></p>
        <?php
    }
}
