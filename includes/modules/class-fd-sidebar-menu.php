<?php
/**
 * FD Admin UI - 左侧菜单模块
 * 管理左侧菜单的设置、样式和功能
 */

defined('ABSPATH') || exit;

class FD_Sidebar_Menu {

    /**
     * 获取模块默认选项
     */
    public static function get_defaults() {
        return array(
            // === 菜单风格 ===
            'menu_style' => 'classic',  // classic 或 modern

            // === 基础颜色 ===
            'menu_bg_color' => '#f8fafc',           // fd-gray-50 = --fd-surface，与主内容区背景一致
            'menu_submenu_bg_color' => '#ffffff',   // 白色，在浅灰背景上形成"卡片"层次感
            'menu_text_color' => '#334155',         // fd-gray-700，浅色背景下深色可读文字

            // === 悬停效果 ===
            'menu_hover_bg_color' => '#e2e8f0',     // fd-gray-200，细腻的 hover 反馈
            'menu_hover_text_color' => '#0f172a',   // fd-gray-900，hover 文字加深至近黑
            'menu_hover_shadow_show' => false,      // 不显示左侧边框指示条
            'menu_hover_shadow_color' => '#72aee6',

            // === 当前选中项 ===
            'menu_current_bg_color' => '#3b82f6',           // fd-primary，品牌色实心高亮
            'menu_current_text_color' => '#ffffff',         // 白色文字配品牌色背景
            'menu_submenu_current_bg_color' => '',
            'menu_submenu_current_text_color' => '#3b82f6', // 子菜单当前项文字用品牌色标识
            'menu_current_shadow_show' => false,            // 不显示左侧边框
            'menu_current_shadow_color' => '#ffffff',

            // === 分隔线 ===
            'menu_separator_show' => true,
            'menu_separator_color' => '#e2e8f0',    // fd-gray-200，浅色背景下的低调分隔线
            'menu_separator_thickness' => '',

            // === 尺寸与间距 ===
            'menu_width' => '',
            'menu_top_margin' => 12,
            'menu_item_padding' => '',
            'menu_item_left_padding' => '',
            'menu_item_right_padding' => '',
            'menu_item_left_margin' => '',
            'menu_item_right_margin' => '',
            'menu_separator_left_margin' => '',
            'menu_separator_right_margin' => '',
            'menu_submenu_item_padding' => '',
            'menu_submenu_align' => 'icon',
            'menu_border_radius' => '',

            // === 徽章 ===
            'menu_badge_bg_color' => '#ef4444',     // fd-error
            'menu_badge_text_color' => '#ffffff',

            // === 站点标识 ===
            'menu_branding_enable' => false,
            'menu_site_logo_full' => '',
            'menu_site_logo_square' => '',
            'menu_branding_link' => '',
            'menu_branding_show_icon' => true,

            // === SVG图标替换 ===
            'svg_icon_replacements' => array(),

            // === 菜单排序 ===
            'menu_sort_enable' => false,

            // === 自动折叠 ===
            'menu_auto_fold_on_post_editor' => false,
        );
    }

    /**
     * 清理模块选项
     */
    public static function sanitize($input, $sanitized) {
        // === 菜单风格 ===
        if (isset($input['menu_style'])) {
            $sanitized['menu_style'] = in_array($input['menu_style'], array('classic', 'modern'))
                ? $input['menu_style']
                : 'classic';
        }

        // === 基础颜色 ===
        if (isset($input['menu_bg_color'])) {
            $sanitized['menu_bg_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_bg_color']);
        }

        if (isset($input['menu_submenu_bg_color'])) {
            $sanitized['menu_submenu_bg_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_submenu_bg_color']);
        }

        if (isset($input['menu_text_color'])) {
            $sanitized['menu_text_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_text_color']);
        }

        // === 悬停效果 ===
        if (isset($input['menu_hover_bg_color'])) {
            $sanitized['menu_hover_bg_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_hover_bg_color']);
        }

        if (isset($input['menu_hover_text_color'])) {
            $sanitized['menu_hover_text_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_hover_text_color']);
        }

        $sanitized['menu_hover_shadow_show'] = isset($input['menu_hover_shadow_show']) && $input['menu_hover_shadow_show'];

        if (isset($input['menu_hover_shadow_color'])) {
            $sanitized['menu_hover_shadow_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_hover_shadow_color']);
        }

        // === 当前选中项 ===
        if (isset($input['menu_current_bg_color'])) {
            $sanitized['menu_current_bg_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_current_bg_color']);
        }

        if (isset($input['menu_current_text_color'])) {
            $sanitized['menu_current_text_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_current_text_color']);
        }

        if (isset($input['menu_submenu_current_bg_color'])) {
            $sanitized['menu_submenu_current_bg_color'] = !empty($input['menu_submenu_current_bg_color'])
                ? FD_Admin_UI_Settings::sanitize_color($input['menu_submenu_current_bg_color'])
                : '';
        }

        if (isset($input['menu_submenu_current_text_color'])) {
            $sanitized['menu_submenu_current_text_color'] = !empty($input['menu_submenu_current_text_color'])
                ? FD_Admin_UI_Settings::sanitize_color($input['menu_submenu_current_text_color'])
                : '';
        }

        $sanitized['menu_current_shadow_show'] = isset($input['menu_current_shadow_show']) && $input['menu_current_shadow_show'];

        if (isset($input['menu_current_shadow_color'])) {
            $sanitized['menu_current_shadow_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_current_shadow_color']);
        }

        // === 分隔线 ===
        $sanitized['menu_separator_show'] = isset($input['menu_separator_show']) && $input['menu_separator_show'];

        if (isset($input['menu_separator_color'])) {
            $sanitized['menu_separator_color'] = !empty($input['menu_separator_color'])
                ? FD_Admin_UI_Settings::sanitize_color($input['menu_separator_color'])
                : '';
        }

        if (isset($input['menu_separator_thickness'])) {
            $sanitized['menu_separator_thickness'] = $input['menu_separator_thickness'] !== ''
                ? max(1, min(10, absint($input['menu_separator_thickness'])))
                : '';
        }

        // === 尺寸与间距 ===
        if (isset($input['menu_width'])) {
            $sanitized['menu_width'] = !empty($input['menu_width']) ? absint($input['menu_width']) : '';
        }

        if (isset($input['menu_top_margin'])) {
            $sanitized['menu_top_margin'] = absint($input['menu_top_margin']);
        }

        if (isset($input['menu_item_padding'])) {
            $sanitized['menu_item_padding'] = $input['menu_item_padding'] !== '' ? absint($input['menu_item_padding']) : '';
        }

        if (isset($input['menu_item_left_padding'])) {
            $sanitized['menu_item_left_padding'] = $input['menu_item_left_padding'] !== '' ? absint($input['menu_item_left_padding']) : '';
        }

        if (isset($input['menu_item_right_padding'])) {
            $sanitized['menu_item_right_padding'] = $input['menu_item_right_padding'] !== '' ? absint($input['menu_item_right_padding']) : '';
        }

        if (isset($input['menu_item_left_margin'])) {
            $sanitized['menu_item_left_margin'] = $input['menu_item_left_margin'] !== '' ? absint($input['menu_item_left_margin']) : '';
        }

        if (isset($input['menu_item_right_margin'])) {
            $sanitized['menu_item_right_margin'] = $input['menu_item_right_margin'] !== '' ? absint($input['menu_item_right_margin']) : '';
        }

        if (isset($input['menu_separator_left_margin'])) {
            $sanitized['menu_separator_left_margin'] = $input['menu_separator_left_margin'] !== '' ? absint($input['menu_separator_left_margin']) : '';
        }

        if (isset($input['menu_separator_right_margin'])) {
            $sanitized['menu_separator_right_margin'] = $input['menu_separator_right_margin'] !== '' ? absint($input['menu_separator_right_margin']) : '';
        }

        if (isset($input['menu_submenu_item_padding'])) {
            $sanitized['menu_submenu_item_padding'] = $input['menu_submenu_item_padding'] !== '' ? absint($input['menu_submenu_item_padding']) : '';
        }

        if (isset($input['menu_submenu_align'])) {
            $sanitized['menu_submenu_align'] = in_array($input['menu_submenu_align'], array('icon', 'text'))
                ? $input['menu_submenu_align']
                : 'icon';
        }

        if (isset($input['menu_border_radius'])) {
            $sanitized['menu_border_radius'] = $input['menu_border_radius'] !== '' ? absint($input['menu_border_radius']) : '';
        }

        // === 徽章 ===
        if (isset($input['menu_badge_bg_color'])) {
            $sanitized['menu_badge_bg_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_badge_bg_color']);
        }

        if (isset($input['menu_badge_text_color'])) {
            $sanitized['menu_badge_text_color'] = FD_Admin_UI_Settings::sanitize_color($input['menu_badge_text_color']);
        }

        // === 站点标识 ===
        $sanitized['menu_branding_enable'] = isset($input['menu_branding_enable']) && $input['menu_branding_enable'];

        if (isset($input['menu_site_logo_full'])) {
            $sanitized['menu_site_logo_full'] = esc_url_raw($input['menu_site_logo_full']);
        }

        if (isset($input['menu_site_logo_square'])) {
            $sanitized['menu_site_logo_square'] = esc_url_raw($input['menu_site_logo_square']);
        }

        if (isset($input['menu_branding_link'])) {
            $sanitized['menu_branding_link'] = esc_url_raw($input['menu_branding_link']);
        }

        $sanitized['menu_branding_show_icon'] = isset($input['menu_branding_show_icon']) && $input['menu_branding_show_icon'];

        // === SVG图标替换 ===
        if (isset($input['svg_icon_replacements']) && is_array($input['svg_icon_replacements'])) {
            $sanitized['svg_icon_replacements'] = array();
            foreach ($input['svg_icon_replacements'] as $slug => $icon) {
                if (!empty($icon)) {
                    $sanitized['svg_icon_replacements'][sanitize_text_field($slug)] = sanitize_text_field($icon);
                }
            }
        }

        // === 菜单排序 ===
        $sanitized['menu_sort_enable'] = isset($input['menu_sort_enable']) && $input['menu_sort_enable'];

        // === 自动折叠 ===
        $sanitized['menu_auto_fold_on_post_editor'] = isset($input['menu_auto_fold_on_post_editor']) && $input['menu_auto_fold_on_post_editor'];

        return $sanitized;
    }

    /**
     * 输出模块 CSS
     */
    public static function output_styles($options) {
        ?>
        /* 左侧菜单背景色 */
        #adminmenu,
        #adminmenuback,
        #adminmenuwrap {
            background-color: <?php echo esc_attr($options['menu_bg_color']); ?> !important;
        }

        <?php self::output_menu_width_styles($options); ?>

        /* 子菜单背景色 */
        #adminmenu .wp-submenu {
            background-color: <?php echo esc_attr($options['menu_submenu_bg_color']); ?> !important;
        }

        /* 子菜单展开时的小箭头颜色 */
        #adminmenu li.wp-has-submenu.opensub::after {
            border-right-color: <?php echo esc_attr($options['menu_submenu_bg_color']); ?> !important;
        }

        /* 主菜单上外边距 */
        #adminmenu {
            margin-top: <?php echo esc_attr($options['menu_top_margin']); ?>px !important;
        }

        /* 隐藏菜单分隔线 */
        <?php if (!$options['menu_separator_show']): ?>
        #adminmenu li.wp-menu-separator {
            display: none !important;
        }
        <?php endif; ?>

        /* 分隔线颜色 */
        <?php if (!empty($options['menu_separator_color'])): ?>
        #adminmenu li.wp-menu-separator {
            background-color: <?php echo esc_attr($options['menu_separator_color']); ?> !important;
        }
        <?php endif; ?>

        /* 分隔线粗细 */
        <?php if ($options['menu_separator_thickness'] !== ''): ?>
        #adminmenu li.wp-menu-separator {
            height: <?php echo esc_attr($options['menu_separator_thickness']); ?>px !important;
            min-height: <?php echo esc_attr($options['menu_separator_thickness']); ?>px !important;
            padding: 0 !important;
        }
        #adminmenu li.wp-menu-separator hr {
            margin: 0 !important;
            border: none !important;
            height: 100% !important;
        }
        <?php endif; ?>

        /* 菜单项内边距 */
        <?php if ($options['menu_item_padding'] !== ''): ?>
        #adminmenu a.menu-top {
            padding-top: <?php echo esc_attr($options['menu_item_padding']); ?>px !important;
            padding-bottom: <?php echo esc_attr($options['menu_item_padding']); ?>px !important;
        }
        <?php endif; ?>

        /* 子菜单项内边距 */
        <?php if ($options['menu_submenu_item_padding'] !== ''): ?>
        #adminmenu .wp-submenu a {
            padding-top: <?php echo esc_attr($options['menu_submenu_item_padding']); ?>px !important;
            padding-bottom: <?php echo esc_attr($options['menu_submenu_item_padding']); ?>px !important;
        }
        <?php endif; ?>

        /* 菜单项圆角 */
        <?php if ($options['menu_border_radius'] !== ''): ?>
        #adminmenu li.menu-top,
        #adminmenu .wp-submenu a {
            border-radius: <?php echo esc_attr($options['menu_border_radius']); ?>px !important;
        }
        <?php endif; ?>

        /* 现代风格 - 菜单项左右外边距 */
        <?php if ($options['menu_style'] === 'modern' && ($options['menu_item_left_margin'] !== '' || $options['menu_item_right_margin'] !== '')): ?>
        #adminmenu li.menu-top {
            <?php if ($options['menu_item_left_margin'] !== ''): ?>
            margin-left: <?php echo esc_attr($options['menu_item_left_margin']); ?>px !important;
            <?php endif; ?>
            <?php if ($options['menu_item_right_margin'] !== ''): ?>
            margin-right: <?php echo esc_attr($options['menu_item_right_margin']); ?>px !important;
            <?php endif; ?>
        }
        <?php endif; ?>

        /* 现代风格 - 分隔线左右外边距 */
        <?php if ($options['menu_style'] === 'modern' && ($options['menu_separator_left_margin'] !== '' || $options['menu_separator_right_margin'] !== '')): ?>
        #adminmenu li.wp-menu-separator {
            <?php if ($options['menu_separator_left_margin'] !== ''): ?>
            margin-left: <?php echo esc_attr($options['menu_separator_left_margin']); ?>px !important;
            <?php endif; ?>
            <?php if ($options['menu_separator_right_margin'] !== ''): ?>
            margin-right: <?php echo esc_attr($options['menu_separator_right_margin']); ?>px !important;
            <?php endif; ?>
        }
        <?php endif; ?>

        /* 现代风格 - 隐藏当前菜单项的小三角 */
        <?php if ($options['menu_style'] === 'modern'): ?>
        ul#adminmenu a.wp-has-current-submenu:after,
        ul#adminmenu > li.current > a.current:after {
            display: none !important;
        }

        /* 现代风格 - 隐藏向右展开子菜单的箭头 */
        #adminmenu li.wp-has-submenu.opensub::after,
        #adminmenu li.wp-not-current-submenu::after {
            display: none !important;
        }

        /* 现代风格 - 修复向右展开子菜单的位置（考虑margin） */
        <?php
        $right_margin = isset($options['menu_item_right_margin']) && $options['menu_item_right_margin'] !== '' ? absint($options['menu_item_right_margin']) : 0;
        
        if ($right_margin > 0):
        ?>
        @media only screen and (min-width: 961px) {
            #adminmenu .wp-not-current-submenu .wp-submenu {
                margin-right: <?php echo esc_attr( $right_margin ); ?>px !important;
            }
        }
        <?php endif; ?>

        /* 现代风格 - 修复有子菜单的菜单项圆角 */
        <?php if (!empty($options['menu_border_radius'])): ?>
        /* 当前选中的有子菜单的菜单项 */
        #adminmenu li.wp-has-current-submenu > a.wp-has-current-submenu,
        #adminmenu li.current.menu-top > a.menu-top {
            border-radius: <?php echo esc_attr($options['menu_border_radius']); ?>px !important;
        }

        /* 悬停时有子菜单的菜单项 */
        #adminmenu li.wp-has-submenu:hover > a.menu-top,
        #adminmenu li.wp-has-submenu.opensub > a.menu-top {
            border-radius: <?php echo esc_attr($options['menu_border_radius']); ?>px !important;
        }

        /* 当前选中项悬停时也保持圆角 */
        #adminmenu li.wp-has-current-submenu:hover > a.wp-has-current-submenu,
        #adminmenu li.current.menu-top:hover > a.menu-top {
            border-radius: <?php echo esc_attr($options['menu_border_radius']); ?>px !important;
        }
        <?php endif; ?>

        /* 现代风格 - 禁用当前选中的li元素悬停时的背景色变化 */
        #adminmenu li.wp-has-current-submenu.menu-top:hover,
        #adminmenu li.current.menu-top:hover {
            background: transparent !important;
            background-color: transparent !important;
        }

        /* 现代风格 - 当前选中项的a元素悬停时保持原背景色 */
        #adminmenu li.wp-has-current-submenu:hover > a.wp-has-current-submenu,
        #adminmenu li.current.menu-top:hover > a.menu-top {
            background: <?php echo esc_attr($options['menu_current_bg_color']); ?> !important;
        }

        /* 现代风格 - 修复向下展开子菜单的宽度（减去margin） */
        <?php
        $left_margin = isset($options['menu_item_left_margin']) && $options['menu_item_left_margin'] !== '' ? absint($options['menu_item_left_margin']) : 0;
        $right_margin = isset($options['menu_item_right_margin']) && $options['menu_item_right_margin'] !== '' ? absint($options['menu_item_right_margin']) : 0;
        $total_margin = $left_margin + $right_margin;
        
        if ($total_margin > 0 && !empty($options['menu_width'])):
            $menu_width = absint($options['menu_width']);
            $submenu_width = $menu_width - $total_margin;
        ?>
        @media only screen and (min-width: 961px) {
            #adminmenu .wp-has-current-submenu .wp-submenu,
            #adminmenu .wp-menu-open .wp-submenu {
                width: <?php echo esc_attr( $submenu_width ); ?>px !important;
            }
        }
        <?php endif; ?>
        <?php endif; ?>

        /* 菜单项文字颜色 */
        #adminmenu a,
        #adminmenu div.wp-menu-name {
            color: <?php echo esc_attr($options['menu_text_color']); ?> !important;
        }

        /* 子菜单项文字颜色 */
        #adminmenu .wp-submenu a {
            color: <?php echo esc_attr($options['menu_text_color']); ?> !important;
        }

        /* 菜单图标颜色 - dashicons */
        #adminmenu div.wp-menu-image:before {
            color: <?php echo esc_attr($options['menu_text_color']); ?> !important;
        }

        /* 菜单项悬停背景色 */
        #adminmenu li.menu-top:hover,
        #adminmenu li.opensub > a.menu-top,
        #adminmenu li > a.menu-top:focus {
            background-color: <?php echo esc_attr($options['menu_hover_bg_color']); ?> !important;
        }

        /* 菜单项悬停左侧边框 */
        <?php if (!empty($options['menu_hover_shadow_show'])): ?>
        #adminmenu a:hover,
        #adminmenu a:focus,
        .folded #adminmenu .wp-submenu-head:hover {
            box-shadow: inset 4px 0 0 0 <?php echo esc_attr($options['menu_hover_shadow_color']); ?> !important;
            transition: box-shadow .1s linear;
        }
        <?php else: ?>
        #adminmenu a:hover,
        #adminmenu a:focus,
        .folded #adminmenu .wp-submenu-head:hover {
            box-shadow: none !important;
        }
        <?php endif; ?>

        /* 菜单项悬停文字颜色 */
        #adminmenu li.menu-top:hover > a,
        #adminmenu li.menu-top:hover .wp-menu-name,
        #adminmenu li.opensub > a.menu-top,
        #adminmenu li.opensub > a.menu-top .wp-menu-name,
        #adminmenu li > a.menu-top:focus,
        #adminmenu li > a.menu-top:focus .wp-menu-name {
            color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
        }

        /* 悬停时图标颜色 - dashicons */
        #adminmenu li.menu-top:hover div.wp-menu-image:before,
        #adminmenu li.opensub > a.menu-top div.wp-menu-image:before,
        #adminmenu li > a.menu-top:focus div.wp-menu-image:before {
            color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
        }

        /* 悬停时图标 - SVG */
        #adminmenu li.menu-top:hover div.wp-menu-image.svg,
        #adminmenu li.opensub > a.menu-top div.wp-menu-image.svg,
        #adminmenu li > a.menu-top:focus div.wp-menu-image.svg {
            opacity: 1;
        }

        /* 子菜单项悬停 */
        #adminmenu .wp-submenu a:hover,
        #adminmenu .wp-submenu a:focus {
            background-color: <?php echo esc_attr($options['menu_hover_bg_color']); ?> !important;
            color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
        }

        /* 当前选中主菜单项背景色 */
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,
        #adminmenu li.current a.menu-top {
            background: <?php echo esc_attr($options['menu_current_bg_color']); ?> !important;
        }

        /* 当前选中主菜单项文字颜色 */
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,
        #adminmenu li.wp-has-current-submenu .wp-menu-name,
        #adminmenu li.current a.menu-top,
        #adminmenu li.current .wp-menu-name {
            color: <?php echo esc_attr($options['menu_current_text_color']); ?> !important;
        }

        /* 当前选中主菜单项图标颜色 - dashicons */
        #adminmenu .current div.wp-menu-image:before,
        #adminmenu .wp-has-current-submenu div.wp-menu-image:before,
        #adminmenu li.wp-has-current-submenu a:focus div.wp-menu-image:before,
        #adminmenu li.wp-has-current-submenu.opensub div.wp-menu-image:before {
            color: <?php echo esc_attr($options['menu_current_text_color']); ?> !important;
        }

        /* 当前选中主菜单项悬停时图标颜色 */
        #adminmenu a.current:hover div.wp-menu-image:before,
        #adminmenu a.wp-has-current-submenu:hover div.wp-menu-image:before,
        #adminmenu li.wp-has-current-submenu:hover div.wp-menu-image:before {
            color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
        }

        /* 当前选中图标 - SVG */
        #adminmenu .current div.wp-menu-image.svg,
        #adminmenu .wp-has-current-submenu div.wp-menu-image.svg,
        #adminmenu li.wp-has-current-submenu a:focus div.wp-menu-image.svg,
        #adminmenu li.wp-has-current-submenu.opensub div.wp-menu-image.svg {
            opacity: 1;
        }

        /* 当前选中主菜单项悬停时的左侧边框 */
        <?php if (!empty($options['menu_current_shadow_show'])): ?>
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu:hover,
        #adminmenu li.current a.menu-top:hover {
            box-shadow: inset 4px 0 0 0 <?php echo esc_attr($options['menu_current_shadow_color']); ?> !important;
        }
        <?php else: ?>
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu:hover,
        #adminmenu li.current a.menu-top:hover {
            box-shadow: none !important;
        }
        <?php endif; ?>

        /* 当前选中子菜单项背景色 */
        <?php if (!empty($options['menu_submenu_current_bg_color'])): ?>
        #adminmenu .wp-submenu li.current a,
        #adminmenu .wp-submenu li a.current {
            background-color: <?php echo esc_attr($options['menu_submenu_current_bg_color']); ?> !important;
        }
        <?php endif; ?>

        /* 当前选中子菜单项文字颜色 */
        <?php if (!empty($options['menu_submenu_current_text_color'])): ?>
        #adminmenu .wp-submenu li.current a,
        #adminmenu .wp-submenu li a.current {
            color: <?php echo esc_attr($options['menu_submenu_current_text_color']); ?> !important;
        }
        <?php endif; ?>

        /* 当前选中子菜单项悬停时的左侧边框 */
        <?php if (!empty($options['menu_current_shadow_show'])): ?>
        #adminmenu .wp-submenu li.current a:hover,
        #adminmenu .wp-submenu li a.current:hover {
            box-shadow: inset 4px 0 0 0 <?php echo esc_attr($options['menu_current_shadow_color']); ?> !important;
        }
        <?php else: ?>
        #adminmenu .wp-submenu li.current a:hover,
        #adminmenu .wp-submenu li a.current:hover {
            box-shadow: none !important;
        }
        <?php endif; ?>

        /* 徽章气泡颜色 */
        #adminmenu .awaiting-mod,
        #adminmenu .update-plugins,
        #adminmenu .menu-counter {
            background-color: <?php echo esc_attr($options['menu_badge_bg_color']); ?> !important;
            color: <?php echo esc_attr($options['menu_badge_text_color']); ?> !important;
        }

        /* 徽章气泡悬停时保持颜色 */
        #adminmenu li:hover .awaiting-mod,
        #adminmenu li:hover .update-plugins,
        #adminmenu li:hover .menu-counter {
            background-color: <?php echo esc_attr($options['menu_badge_bg_color']); ?> !important;
            color: <?php echo esc_attr($options['menu_badge_text_color']); ?> !important;
        }

        /* 统一徽章与菜单项名称的间距 */
        #adminmenu .awaiting-mod,
        #adminmenu .update-plugins,
        #adminmenu .menu-counter,
        #adminmenu .wp-menu-name .update-plugins {
            margin-left: 6px !important;
        }

        /* 子菜单中的徽章 */
        #adminmenu .wp-submenu .awaiting-mod,
        #adminmenu .wp-submenu .update-plugins,
        #adminmenu .wp-submenu .menu-counter {
            margin-left: 6px !important;
        }

        <?php self::output_svg_replacement_styles($options); ?>
        <?php
    }

    /**
     * 输出菜单宽度相关 CSS（私有方法）
     */
    private static function output_menu_width_styles($options) {
        if (empty($options['menu_width'])) {
            return;
        }

        $menu_width = absint($options['menu_width']);

        // 计算 padding
        if ($options['menu_item_left_padding'] !== '' && $options['menu_item_right_padding'] !== '') {
            $main_left_padding = $options['menu_item_left_padding'];
            $main_right_padding = $options['menu_item_right_padding'];
        } else {
            $padding_adjustment = ($menu_width - 160) / 2;
            $main_left_padding = max(0, $padding_adjustment);
            $main_right_padding = max(10, 10 + $padding_adjustment);
        }

        // 子菜单左侧padding
        if ($options['menu_submenu_align'] === 'text') {
            $submenu_left_padding = 36 + $main_left_padding;
        } else {
            $submenu_left_padding = 8 + $main_left_padding;
        }
        ?>
        /* 菜单宽度 - 仅桌面端（大于960px） */
        @media only screen and (min-width: 961px) {
            #adminmenuback,
            #adminmenuwrap,
            #adminmenu {
                width: <?php echo esc_attr( $menu_width ); ?>px !important;
            }

            #adminmenu a.menu-top {
                padding-left: <?php echo esc_attr( $main_left_padding ); ?>px !important;
                padding-right: <?php echo esc_attr( $main_right_padding ); ?>px !important;
            }

            #adminmenu .wp-has-current-submenu .wp-submenu,
            #adminmenu .wp-menu-open .wp-submenu {
                width: <?php echo esc_attr( $menu_width ); ?>px !important;
            }

            #adminmenu .wp-not-current-submenu .wp-submenu {
                width: 160px !important;
                left: <?php echo esc_attr( $menu_width ); ?>px !important;
            }

            #adminmenu .wp-has-current-submenu .wp-submenu a,
            #adminmenu .wp-menu-open .wp-submenu a {
                padding-left: <?php echo esc_attr( $submenu_left_padding ); ?>px !important;
                padding-right: <?php echo esc_attr( $main_right_padding ); ?>px !important;
            }

            #adminmenu .wp-not-current-submenu .wp-submenu a {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            #wpcontent,
            #wpfooter {
                margin-left: <?php echo esc_attr( $menu_width ); ?>px !important;
            }
        }

        /* 自动折叠状态（783-960px） */
        @media only screen and (min-width: 783px) and (max-width: 960px) {
            body.auto-fold #adminmenuback,
            body.auto-fold #adminmenuwrap,
            body.auto-fold #adminmenu {
                width: 36px !important;
            }

            body.auto-fold #adminmenu a.menu-top {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            body.auto-fold #wpcontent,
            body.auto-fold #wpfooter {
                margin-left: 36px !important;
            }

            body.auto-fold #adminmenu .wp-not-current-submenu .wp-submenu {
                left: 36px !important;
            }

            body.auto-fold #adminmenu .wp-submenu-head {
                background: none !important;
                color: <?php echo esc_attr($options['menu_text_color']); ?> !important;
                font-weight: 600 !important;
            }

            body.auto-fold #adminmenu .wp-has-current-submenu .wp-submenu-head,
            body.auto-fold #adminmenu .current .wp-submenu-head {
                background: none !important;
                color: <?php echo esc_attr($options['menu_text_color']); ?> !important;
                font-weight: 600 !important;
            }
        }

        /* 手动折叠状态（>960px） */
        @media only screen and (min-width: 961px) {
            body.folded #adminmenuback,
            body.folded #adminmenuwrap,
            body.folded #adminmenu {
                width: 36px !important;
            }

            body.folded #adminmenu a.menu-top {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            body.folded #wpcontent,
            body.folded #wpfooter {
                margin-left: 36px !important;
            }

            body.folded #adminmenu .wp-not-current-submenu .wp-submenu {
                left: 36px !important;
            }

            body.folded #adminmenu .wp-submenu-head {
                background: none !important;
                color: <?php echo esc_attr($options['menu_text_color']); ?> !important;
                font-weight: 600 !important;
            }

            body.folded #adminmenu .wp-has-current-submenu .wp-submenu-head,
            body.folded #adminmenu .current .wp-submenu-head {
                background: none !important;
                color: <?php echo esc_attr($options['menu_text_color']); ?> !important;
                font-weight: 600 !important;
            }
        }
        <?php
    }

    /**
     * 输出 SVG 图标替换 CSS（私有方法）
     */
    private static function output_svg_replacement_styles($options) {
        if (empty($options['svg_icon_replacements'])) {
            return;
        }

        foreach ($options['svg_icon_replacements'] as $slug => $icon_class):
            $icon_content = FD_Admin_UI_Settings::get_dashicon_content($icon_class);
        ?>
        /* 替换 <?php echo esc_attr($slug); ?> 的SVG图标 */
        #adminmenu li[class*="<?php echo esc_attr(str_replace('/', '-', $slug)); ?>"] .wp-menu-image.svg,
        #adminmenu li a[href*="<?php echo esc_attr($slug); ?>"] .wp-menu-image.svg {
            background-image: none !important;
            filter: none !important;
        }

        #adminmenu li[class*="<?php echo esc_attr(str_replace('/', '-', $slug)); ?>"] .wp-menu-image.svg:before,
        #adminmenu li a[href*="<?php echo esc_attr($slug); ?>"] .wp-menu-image.svg:before {
            content: "\f111" !important;
            font-family: dashicons !important;
            display: inline-block !important;
            font-size: 20px !important;
            line-height: 1 !important;
            font-weight: 400 !important;
            font-style: normal !important;
            vertical-align: top !important;
            text-align: center !important;
            transition: color .1s ease-in !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            width: 20px !important;
            height: 20px !important;
            color: <?php echo esc_attr($options['menu_text_color']); ?> !important;
        }

        <?php if ($icon_content): ?>
        #adminmenu li[class*="<?php echo esc_attr(str_replace('/', '-', $slug)); ?>"] .wp-menu-image.svg:before,
        #adminmenu li a[href*="<?php echo esc_attr($slug); ?>"] .wp-menu-image.svg:before {
            content: "<?php echo esc_attr( $icon_content ); ?>" !important;
        }
        <?php endif; ?>

        /* 悬停状态 */
        #adminmenu li[class*="<?php echo esc_attr(str_replace('/', '-', $slug)); ?>"]:hover .wp-menu-image.svg:before,
        #adminmenu li[class*="<?php echo esc_attr(str_replace('/', '-', $slug)); ?>"].opensub .wp-menu-image.svg:before,
        #adminmenu li a[href*="<?php echo esc_attr($slug); ?>"]:hover .wp-menu-image.svg:before,
        #adminmenu li a[href*="<?php echo esc_attr($slug); ?>"]:focus .wp-menu-image.svg:before {
            color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
        }

        /* 当前选中状态 */
        #adminmenu li.current[class*="<?php echo esc_attr(str_replace('/', '-', $slug)); ?>"] .wp-menu-image.svg:before,
        #adminmenu li.wp-has-current-submenu[class*="<?php echo esc_attr(str_replace('/', '-', $slug)); ?>"] .wp-menu-image.svg:before,
        #adminmenu li.current a[href*="<?php echo esc_attr($slug); ?>"] .wp-menu-image.svg:before,
        #adminmenu li.wp-has-current-submenu a[href*="<?php echo esc_attr($slug); ?>"] .wp-menu-image.svg:before {
            color: <?php echo esc_attr($options['menu_current_text_color']); ?> !important;
        }

        /* 当前选中项悬停状态 */
        #adminmenu li.current[class*="<?php echo esc_attr(str_replace('/', '-', $slug)); ?>"]:hover .wp-menu-image.svg:before,
        #adminmenu li.wp-has-current-submenu[class*="<?php echo esc_attr(str_replace('/', '-', $slug)); ?>"]:hover .wp-menu-image.svg:before,
        #adminmenu li.current a[href*="<?php echo esc_attr($slug); ?>"]:hover .wp-menu-image.svg:before,
        #adminmenu li.wp-has-current-submenu a[href*="<?php echo esc_attr($slug); ?>"]:hover .wp-menu-image.svg:before {
            color: <?php echo esc_attr($options['menu_hover_text_color']); ?> !important;
        }
        <?php
        endforeach;
    }

    /**
     * 获取选项（从主类获取）
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * 注册左侧菜单相关设置
     */
    public static function register_settings() {
        // 左侧菜单设置 - 菜单风格
        add_settings_section(
            'fd_menu_style_section',
            __('Menu Style', 'fd-admin-ui'),
            array(__CLASS__, 'style_section_callback'),
            'fd-admin-ui-settings-menu'
        );
        add_settings_field('menu_style', __('Select Style', 'fd-admin-ui'), array(__CLASS__, 'menu_style_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_style_section');

        // 左侧菜单设置 - 基础设置
        add_settings_section(
            'fd_menu_basic_section',
            __('Basic Settings', 'fd-admin-ui'),
            array(__CLASS__, 'basic_section_callback'),
            'fd-admin-ui-settings-menu'
        );

        add_settings_field('menu_bg_color', __('Menu Background Color', 'fd-admin-ui'), array(__CLASS__, 'bg_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_basic_section');
        add_settings_field('menu_submenu_bg_color', __('Submenu Background Color', 'fd-admin-ui'), array(__CLASS__, 'submenu_bg_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_basic_section');
        add_settings_field('menu_text_color', __('Menu Item Text Color', 'fd-admin-ui'), array(__CLASS__, 'text_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_basic_section');
        add_settings_field('menu_separator_show', __('Show Menu Separator', 'fd-admin-ui'), array(__CLASS__, 'separator_show_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_basic_section');
        add_settings_field('menu_separator_color', __('Separator Color', 'fd-admin-ui'), array(__CLASS__, 'separator_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_basic_section');
        add_settings_field('menu_separator_thickness', __('Separator Thickness', 'fd-admin-ui'), array(__CLASS__, 'separator_thickness_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_basic_section');

        // 左侧菜单设置 - 悬停效果
        add_settings_section('fd_menu_hover_section', __('Hover Effect', 'fd-admin-ui'), array(__CLASS__, 'hover_section_callback'), 'fd-admin-ui-settings-menu');
        add_settings_field('menu_hover_bg_color', __('Menu Item Hover Background Color', 'fd-admin-ui'), array(__CLASS__, 'hover_bg_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_hover_section');
        add_settings_field('menu_hover_text_color', __('Menu Item Hover Text Color', 'fd-admin-ui'), array(__CLASS__, 'hover_text_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_hover_section');
        add_settings_field('menu_hover_shadow_show', __('Show Hover Border', 'fd-admin-ui'), array(__CLASS__, 'hover_shadow_show_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_hover_section');
        add_settings_field('menu_hover_shadow_color', __('Hover Border Color', 'fd-admin-ui'), array(__CLASS__, 'hover_shadow_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_hover_section');

        // 左侧菜单设置 - 当前选中项
        add_settings_section('fd_menu_current_section', __('Current Selected Item', 'fd-admin-ui'), array(__CLASS__, 'current_section_callback'), 'fd-admin-ui-settings-menu');
        add_settings_field('menu_current_bg_color', __('Current Item Background Color', 'fd-admin-ui'), array(__CLASS__, 'current_bg_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_current_section');
        add_settings_field('menu_current_text_color', __('Current Item Text Color', 'fd-admin-ui'), array(__CLASS__, 'current_text_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_current_section');
        add_settings_field('menu_submenu_current_bg_color', __('Submenu Current Item Background Color', 'fd-admin-ui'), array(__CLASS__, 'submenu_current_bg_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_current_section');
        add_settings_field('menu_submenu_current_text_color', __('Submenu Current Item Text Color', 'fd-admin-ui'), array(__CLASS__, 'submenu_current_text_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_current_section');
        add_settings_field('menu_current_shadow_show', __('Show Current Item Border', 'fd-admin-ui'), array(__CLASS__, 'current_shadow_show_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_current_section');
        add_settings_field('menu_current_shadow_color', __('Current Item Border Color', 'fd-admin-ui'), array(__CLASS__, 'current_shadow_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_current_section');

        // 左侧菜单设置 - 尺寸与间距
        add_settings_section('fd_menu_size_section', __('Size & Spacing', 'fd-admin-ui'), array(__CLASS__, 'size_section_callback'), 'fd-admin-ui-settings-menu');
        add_settings_field('menu_width', __('Menu Width', 'fd-admin-ui'), array(__CLASS__, 'width_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');
        add_settings_field('menu_top_margin', __('Menu Top Margin', 'fd-admin-ui'), array(__CLASS__, 'top_margin_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');
        add_settings_field('menu_item_padding', __('Menu Item Padding', 'fd-admin-ui'), array(__CLASS__, 'item_padding_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');
        add_settings_field('menu_item_left_padding', __('Menu Item Left Padding', 'fd-admin-ui'), array(__CLASS__, 'item_left_padding_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');
        add_settings_field('menu_item_right_padding', __('Menu Item Right Padding', 'fd-admin-ui'), array(__CLASS__, 'item_right_padding_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');
        add_settings_field('menu_submenu_item_padding', __('Submenu Item Padding', 'fd-admin-ui'), array(__CLASS__, 'submenu_item_padding_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');
        add_settings_field('menu_submenu_align', __('Submenu Alignment', 'fd-admin-ui'), array(__CLASS__, 'submenu_align_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');
        add_settings_field('menu_border_radius', __('Menu Item Border Radius', 'fd-admin-ui'), array(__CLASS__, 'border_radius_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');
        add_settings_field('menu_badge_bg_color', __('Badge Background Color', 'fd-admin-ui'), array(__CLASS__, 'badge_bg_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');
        add_settings_field('menu_badge_text_color', __('Badge Text Color', 'fd-admin-ui'), array(__CLASS__, 'badge_text_color_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_size_section');

        // 左侧菜单设置 - 现代风格设置
        add_settings_section('fd_menu_modern_section', __('Modern Style Settings', 'fd-admin-ui'), array(__CLASS__, 'modern_section_callback'), 'fd-admin-ui-settings-menu');
        add_settings_field('menu_item_left_margin', __('Menu Item Left Margin', 'fd-admin-ui'), array(__CLASS__, 'item_left_margin_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_modern_section');
        add_settings_field('menu_item_right_margin', __('Menu Item Right Margin', 'fd-admin-ui'), array(__CLASS__, 'item_right_margin_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_modern_section');
        add_settings_field('menu_separator_left_margin', __('Separator Left Margin', 'fd-admin-ui'), array(__CLASS__, 'separator_left_margin_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_modern_section');
        add_settings_field('menu_separator_right_margin', __('Separator Right Margin', 'fd-admin-ui'), array(__CLASS__, 'separator_right_margin_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_modern_section');

        // 菜单排序设置
        add_settings_section('fd_menu_sort_section', __('Menu Sorting', 'fd-admin-ui'), array(__CLASS__, 'sort_section_callback'), 'fd-admin-ui-settings-menu');
        add_settings_field('menu_sort_enable', __('Enable Menu Drag-and-Drop Sorting', 'fd-admin-ui'), array(__CLASS__, 'sort_enable_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_sort_section');

        // 自动折叠设置
        add_settings_section('fd_menu_auto_fold_section', __('Auto Collapse', 'fd-admin-ui'), array(__CLASS__, 'auto_fold_section_callback'), 'fd-admin-ui-settings-menu');
        add_settings_field('menu_auto_fold_on_post_editor', __('Auto Collapse on Post Editor', 'fd-admin-ui'), array(__CLASS__, 'auto_fold_on_post_editor_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_auto_fold_section');

        // SVG图标替换设置
        add_settings_section('fd_menu_svg_section', __('SVG Icon Replacement', 'fd-admin-ui'), array(__CLASS__, 'svg_section_callback'), 'fd-admin-ui-settings-menu');
        add_settings_field('svg_icon_replacements', __('SVG Icon Replacement', 'fd-admin-ui'), array(__CLASS__, 'svg_icon_replacements_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_svg_section');

        // 站点标识设置
        add_settings_section('fd_menu_branding_section', __('Site Branding', 'fd-admin-ui'), array(__CLASS__, 'branding_section_callback'), 'fd-admin-ui-settings-menu');
        add_settings_field('menu_branding_enable', __('Show Site Branding', 'fd-admin-ui'), array(__CLASS__, 'branding_enable_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_branding_section');
        add_settings_field('menu_site_logo_full', __('Full Logo', 'fd-admin-ui'), array(__CLASS__, 'site_logo_full_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_branding_section');
        add_settings_field('menu_site_logo_square', __('Square Logo', 'fd-admin-ui'), array(__CLASS__, 'site_logo_square_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_branding_section');
        add_settings_field('menu_branding_link', __('Click Link URL', 'fd-admin-ui'), array(__CLASS__, 'branding_link_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_branding_section');
        add_settings_field('menu_branding_show_icon', __('Show Quick Icon', 'fd-admin-ui'), array(__CLASS__, 'branding_show_icon_callback'), 'fd-admin-ui-settings-menu', 'fd_menu_branding_section');
    }

    // ========================================
    // Section 回调
    // ========================================
    public static function style_section_callback() { echo '<p>' . esc_html__('Choose the visual style for the left sidebar menu. Classic style keeps the traditional WordPress layout; Modern style uses rounded background design (similar to Notion).', 'fd-admin-ui') . '</p>'; }
    public static function basic_section_callback() { echo '<p>' . esc_html__('Set the basic appearance of the menu, including background and text colors.', 'fd-admin-ui') . '</p>'; }
    public static function hover_section_callback() { echo '<p>' . esc_html__('Set the menu style on mouse hover.', 'fd-admin-ui') . '</p>'; }
    public static function current_section_callback() { echo '<p>' . esc_html__('Set the style for the currently selected menu item.', 'fd-admin-ui') . '</p>'; }
    public static function size_section_callback() { echo '<p>' . esc_html__('Set menu dimensions, spacing, and border radius.', 'fd-admin-ui') . '</p>'; }
    public static function modern_section_callback() { echo '<p>' . esc_html__('Settings exclusive to the Modern Minimal style, used to achieve a Notion-like indent effect. Only takes effect when "Modern Minimal Style" is selected.', 'fd-admin-ui') . '</p>'; }
    public static function svg_section_callback() { echo '<p>' . esc_html__('Some plugins use SVG icons which may cause color control issues. You can replace them with standard Dashicons.', 'fd-admin-ui') . '</p>'; }
    public static function sort_section_callback() { echo '<p>' . esc_html__('When enabled, administrators can customise the left sidebar menu order by dragging. The order applies to all users.', 'fd-admin-ui') . '</p>'; }
    public static function auto_fold_section_callback() { echo '<p>' . esc_html__('Control the auto-collapse behavior of the left sidebar menu on specific pages to improve the editing experience.', 'fd-admin-ui') . '</p>'; }
    public static function branding_section_callback() { echo '<p>' . esc_html__('Display site branding at the top of the left sidebar menu, similar to a Notion workspace identifier.', 'fd-admin-ui') . '</p>'; }

    // ========================================
    // 颜色相关回调
    // ========================================
    public static function menu_style_callback() {
        $options = self::get_options();
        $value = isset($options['menu_style']) ? $options['menu_style'] : 'classic';
        ?>
        <div class="fd-style-selector">
            <label class="fd-style-option">
                <input type="radio" name="fd_admin_ui_options[menu_style]" value="classic" <?php checked($value, 'classic'); ?>>
                <div class="fd-style-preview fd-style-classic">
                    <div class="fd-preview-menu">
                        <div class="fd-menu-item fd-menu-active">🎨 <?php esc_html_e('Dashboard', 'fd-admin-ui'); ?></div>
                        <div class="fd-menu-item">📝 <?php esc_html_e('Posts', 'fd-admin-ui'); ?></div>
                        <div class="fd-menu-item">🖼️ <?php esc_html_e('Media', 'fd-admin-ui'); ?></div>
                        <div class="fd-menu-separator"></div>
                        <div class="fd-menu-item">📄 <?php esc_html_e('Pages', 'fd-admin-ui'); ?></div>
                        <div class="fd-menu-item">💬 <?php esc_html_e('Comments', 'fd-admin-ui'); ?></div>
                    </div>
            <div class="fd-style-label"><?php esc_html_e('WP Classic Style', 'fd-admin-ui'); ?></div>
                </div>
            </label>

            <label class="fd-style-option">
                <input type="radio" name="fd_admin_ui_options[menu_style]" value="modern" <?php checked($value, 'modern'); ?>>
                <div class="fd-style-preview fd-style-modern">
                    <div class="fd-preview-menu fd-preview-menu-modern">
                        <div class="fd-menu-item fd-menu-active">🎨 <?php esc_html_e('Dashboard', 'fd-admin-ui'); ?></div>
                        <div class="fd-menu-item">📝 <?php esc_html_e('Posts', 'fd-admin-ui'); ?></div>
                        <div class="fd-menu-item">🖼️ <?php esc_html_e('Media', 'fd-admin-ui'); ?></div>
                        <div class="fd-menu-separator"></div>
                        <div class="fd-menu-item">📄 <?php esc_html_e('Pages', 'fd-admin-ui'); ?></div>
                        <div class="fd-menu-item">💬 <?php esc_html_e('Comments', 'fd-admin-ui'); ?></div>
                    </div>
            <div class="fd-style-label"><?php esc_html_e('Modern Minimal Style', 'fd-admin-ui'); ?></div>
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
            background: white;
        }
        .fd-style-option input[type="radio"]:checked + .fd-style-preview {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }
        .fd-style-preview:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .fd-preview-menu {
            background: #1d2327;
            padding: 8px;
            min-height: 180px;
        }
        .fd-preview-menu-modern {
            background: #f7f7f7;
        }
        .fd-menu-item {
            padding: 6px 8px;
            font-size: 12px;
            color: #f0f0f1;
            margin: 1px 0;
            transition: all 0.15s;
        }
        .fd-preview-menu-modern .fd-menu-item {
            color: #37352f;
            border-radius: 4px;
            margin: 1px 6px;
        }
        .fd-menu-item:hover {
            background: #2c3338;
        }
        .fd-preview-menu-modern .fd-menu-item:hover {
            background: rgba(0, 0, 0, 0.04);
        }
        .fd-menu-item.fd-menu-active {
            background: #2271b1;
            color: white;
        }
        .fd-preview-menu-modern .fd-menu-item.fd-menu-active {
            background: rgba(0, 0, 0, 0.08);
            color: #37352f;
            font-weight: 500;
        }
        .fd-menu-separator {
            height: 5px;
            margin: 6px 0;
            background: rgba(255,255,255,0.05);
        }
        .fd-preview-menu-modern .fd-menu-separator {
            height: 1px;
            margin: 6px 12px;
            background: rgba(0, 0, 0, 0.08);
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

        <p class="description" style="margin-top: 15px;"><?php esc_html_e('Choose the visual style for the left sidebar menu. "Modern Minimal Style" references Notion with a rounded background design.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function bg_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_bg_color']) ? $options['menu_bg_color'] : '#f8fafc';
        echo '<input type="text" name="fd_admin_ui_options[menu_bg_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #f8fafc (fd-gray-50)', 'fd-admin-ui') . '</p>';
    }

    public static function submenu_bg_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_submenu_bg_color']) ? $options['menu_submenu_bg_color'] : '#ffffff';
        echo '<input type="text" name="fd_admin_ui_options[menu_submenu_bg_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #ffffff (white)', 'fd-admin-ui') . '</p>';
    }

    public static function text_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_text_color']) ? $options['menu_text_color'] : '#334155';
        echo '<input type="text" name="fd_admin_ui_options[menu_text_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #334155 (fd-gray-700)', 'fd-admin-ui') . '</p>';
    }

    public static function hover_bg_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_hover_bg_color']) ? $options['menu_hover_bg_color'] : '#e2e8f0';
        echo '<input type="text" name="fd_admin_ui_options[menu_hover_bg_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #e2e8f0 (fd-gray-200)', 'fd-admin-ui') . '</p>';
    }

    public static function hover_text_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_hover_text_color']) ? $options['menu_hover_text_color'] : '#0f172a';
        echo '<input type="text" name="fd_admin_ui_options[menu_hover_text_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #0f172a (fd-gray-900)', 'fd-admin-ui') . '</p>';
    }

    public static function hover_shadow_show_callback() {
        $options = self::get_options();
        $checked = isset($options['menu_hover_shadow_show']) && $options['menu_hover_shadow_show'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[menu_hover_shadow_show]" value="1" <?php echo esc_attr( $checked ); ?> data-target=".fd-hover-shadow-color-row">
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('When enabled, a left border is shown on hover; when disabled, the border is hidden.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function hover_shadow_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_hover_shadow_color']) ? $options['menu_hover_shadow_color'] : '#72aee6';
        $show = isset($options['menu_hover_shadow_show']) && $options['menu_hover_shadow_show'];
        $opacity = $show ? '1' : '0.5';
        echo '<div style="opacity: ' . esc_attr( $opacity ) . ';"><input type="text" name="fd_admin_ui_options[menu_hover_shadow_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #72aee6 (hover left border color)', 'fd-admin-ui') . '</p></div>';
    }

    public static function current_bg_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_current_bg_color']) ? $options['menu_current_bg_color'] : '#3b82f6';
        echo '<input type="text" name="fd_admin_ui_options[menu_current_bg_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #3b82f6 (fd-primary)', 'fd-admin-ui') . '</p>';
    }

    public static function current_text_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_current_text_color']) ? $options['menu_current_text_color'] : '#ffffff';
        echo '<input type="text" name="fd_admin_ui_options[menu_current_text_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #ffffff (white)', 'fd-admin-ui') . '</p>';
    }

    public static function submenu_current_bg_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_submenu_current_bg_color']) ? $options['menu_submenu_current_bg_color'] : '';
        echo '<input type="text" name="fd_admin_ui_options[menu_submenu_current_bg_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: leave empty (no background color)', 'fd-admin-ui') . '</p>';
    }

    public static function submenu_current_text_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_submenu_current_text_color']) ? $options['menu_submenu_current_text_color'] : '#3b82f6';
        echo '<input type="text" name="fd_admin_ui_options[menu_submenu_current_text_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #3b82f6 (fd-primary)', 'fd-admin-ui') . '</p>';
    }

    public static function current_shadow_show_callback() {
        $options = self::get_options();
        $checked = isset($options['menu_current_shadow_show']) && $options['menu_current_shadow_show'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[menu_current_shadow_show]" value="1" <?php echo esc_attr( $checked ); ?> data-target=".fd-current-shadow-color-row">
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('When enabled, a left border is shown on hover of the current selected item; when disabled, the border is hidden.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function current_shadow_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_current_shadow_color']) ? $options['menu_current_shadow_color'] : '#ffffff';
        $show = isset($options['menu_current_shadow_show']) && $options['menu_current_shadow_show'];
        $opacity = $show ? '1' : '0.5';
        echo '<div style="opacity: ' . esc_attr( $opacity ) . ';"><input type="text" name="fd_admin_ui_options[menu_current_shadow_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #ffffff (current selected item left border color)', 'fd-admin-ui') . '</p></div>';
    }

    public static function badge_bg_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_badge_bg_color']) ? $options['menu_badge_bg_color'] : '#ef4444';
        echo '<input type="text" name="fd_admin_ui_options[menu_badge_bg_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #ef4444 (fd-error). Background color for update count, notification badges, etc.', 'fd-admin-ui') . '</p>';
    }

    public static function badge_text_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_badge_text_color']) ? $options['menu_badge_text_color'] : '#ffffff';
        echo '<input type="text" name="fd_admin_ui_options[menu_badge_text_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #ffffff (white). Text color inside the badge.', 'fd-admin-ui') . '</p>';
    }

    // ========================================
    // 开关和选择相关回调
    // ========================================
    public static function separator_show_callback() {
        $options = self::get_options();
        $checked = isset($options['menu_separator_show']) && $options['menu_separator_show'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[menu_separator_show]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('When enabled, separators are shown between menu items.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function separator_color_callback() {
        $options = self::get_options();
        $value = isset($options['menu_separator_color']) ? $options['menu_separator_color'] : '#e2e8f0';
        echo '<input type="text" name="fd_admin_ui_options[menu_separator_color]" value="' . esc_attr($value) . '" class="fd-color-picker"><p class="description">' . esc_html__('Default: #e2e8f0 (fd-gray-200)', 'fd-admin-ui') . '</p>';
    }

    public static function separator_thickness_callback() {
        $options = self::get_options();
        $value = isset($options['menu_separator_thickness']) ? $options['menu_separator_thickness'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_separator_thickness]" value="' . esc_attr($value) . '" class="fd-form-input" min="1" max="10" step="1" placeholder="1"><p class="description">' . esc_html__('Default: WordPress native thickness (leave empty). Unit: px, range 1-10.', 'fd-admin-ui') . '</p>';
    }

    public static function submenu_align_callback() {
        $options = self::get_options();
        $value = isset($options['menu_submenu_align']) ? $options['menu_submenu_align'] : 'icon';
        ?>
        <select name="fd_admin_ui_options[menu_submenu_align]" class="fd-form-input">
            <option value="icon" <?php selected($value, 'icon'); ?>><?php esc_html_e('Align with icon left edge', 'fd-admin-ui'); ?></option>
            <option value="text" <?php selected($value, 'text'); ?>><?php esc_html_e('Align with menu item name', 'fd-admin-ui'); ?></option>
        </select>
        <p class="description"><?php esc_html_e('Choose the alignment of submenu text relative to the main menu.', 'fd-admin-ui'); ?></p>
        <?php
    }

    // ========================================
    // 尺寸相关回调
    // ========================================
    public static function width_callback() {
        $options = self::get_options();
        $value = isset($options['menu_width']) ? $options['menu_width'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_width]" value="' . esc_attr($value) . '" class="fd-form-input" min="100" max="300" step="1" placeholder="160"><p class="description">' . esc_html__('Default: WordPress native width (leave empty). Only effective on desktop, does not affect collapsed state.', 'fd-admin-ui') . '</p>';
    }

    public static function top_margin_callback() {
        $options = self::get_options();
        $value = isset($options['menu_top_margin']) ? $options['menu_top_margin'] : 12;
        echo '<input type="number" name="fd_admin_ui_options[menu_top_margin]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="100" step="1"><p class="description">' . esc_html__('Default: 12 (unit: px)', 'fd-admin-ui') . '</p>';
    }

    public static function item_padding_callback() {
        $options = self::get_options();
        $value = isset($options['menu_item_padding']) ? $options['menu_item_padding'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_item_padding]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="50" step="1" placeholder="6"><p class="description">' . esc_html__('Default: WordPress native padding (leave empty). Sets the top/bottom padding of main menu items.', 'fd-admin-ui') . '</p>';
    }

    public static function item_left_padding_callback() {
        $options = self::get_options();
        $value = isset($options['menu_item_left_padding']) ? $options['menu_item_left_padding'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_item_left_padding]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="50" step="1" placeholder=""><p class="description">' . esc_html__('Default: auto-calculated based on menu width (leave empty). Manually set the left padding of main menu items.', 'fd-admin-ui') . '</p>';
    }

    public static function item_right_padding_callback() {
        $options = self::get_options();
        $value = isset($options['menu_item_right_padding']) ? $options['menu_item_right_padding'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_item_right_padding]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="50" step="1" placeholder=""><p class="description">' . esc_html__('Default: auto-calculated based on menu width (leave empty). Manually set the right padding of main menu items.', 'fd-admin-ui') . '</p>';
    }

    public static function item_left_margin_callback() {
        $options = self::get_options();
        $value = isset($options['menu_item_left_margin']) ? $options['menu_item_left_margin'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_item_left_margin]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="50" step="1" placeholder="0"><p class="description">' . esc_html__('Set the left margin of main menu items for a Notion-like indent effect. Default: 0', 'fd-admin-ui') . '</p>';
    }

    public static function item_right_margin_callback() {
        $options = self::get_options();
        $value = isset($options['menu_item_right_margin']) ? $options['menu_item_right_margin'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_item_right_margin]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="50" step="1" placeholder="0"><p class="description">' . esc_html__('Set the right margin of main menu items for a Notion-like indent effect. Default: 0', 'fd-admin-ui') . '</p>';
    }

    public static function separator_left_margin_callback() {
        $options = self::get_options();
        $value = isset($options['menu_separator_left_margin']) ? $options['menu_separator_left_margin'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_separator_left_margin]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="50" step="1" placeholder="0"><p class="description">' . esc_html__('Set the left margin of separators. Default: 0', 'fd-admin-ui') . '</p>';
    }

    public static function separator_right_margin_callback() {
        $options = self::get_options();
        $value = isset($options['menu_separator_right_margin']) ? $options['menu_separator_right_margin'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_separator_right_margin]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="50" step="1" placeholder="0"><p class="description">' . esc_html__('Set the right margin of separators. Default: 0', 'fd-admin-ui') . '</p>';
    }

    public static function submenu_item_padding_callback() {
        $options = self::get_options();
        $value = isset($options['menu_submenu_item_padding']) ? $options['menu_submenu_item_padding'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_submenu_item_padding]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="20" step="1" placeholder="5"><p class="description">' . esc_html__('Default: WordPress native padding (leave empty)', 'fd-admin-ui') . '</p>';
    }

    public static function border_radius_callback() {
        $options = self::get_options();
        $value = isset($options['menu_border_radius']) ? $options['menu_border_radius'] : '';
        echo '<input type="number" name="fd_admin_ui_options[menu_border_radius]" value="' . esc_attr($value) . '" class="fd-form-input" min="0" max="20" step="1" placeholder="0"><p class="description">' . esc_html__('Default: WordPress native border radius (leave empty)', 'fd-admin-ui') . '</p>';
    }

    // ========================================
    // 菜单排序回调
    // ========================================
    public static function sort_enable_callback() {
        $options = self::get_options();
        $checked = isset($options['menu_sort_enable']) && $options['menu_sort_enable'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[menu_sort_enable]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description">启用后，可直接拖拽左侧菜单项进行排序（类似 Notion 风格）。排序将自动保存。</p>
        <?php
        $menu_order = get_option('fd_admin_menu_order', array());
        if (!empty($menu_order)) {
            ?>
            <p style="margin-top: 15px;">
                <button type="button" class="button" id="fd-reset-menu-order"><?php esc_html_e('Reset Menu Order', 'fd-admin-ui'); ?></button>
                <span class="description" style="margin-left: 10px;"><?php esc_html_e('Restore the WordPress default menu order', 'fd-admin-ui'); ?></span>
            </p>
            <script>
            jQuery(document).ready(function($) {
                $('#fd-reset-menu-order').on('click', function() {
                    if (confirm(<?php echo wp_json_encode(__('Are you sure you want to reset the menu order? This will restore the WordPress default menu order.', 'fd-admin-ui')); ?>)) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'fd_reset_menu_order',
                                nonce: '<?php echo esc_attr( wp_create_nonce('fd_menu_sort_nonce') ); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert(<?php echo wp_json_encode(__('Menu order has been reset.', 'fd-admin-ui')); ?>);
                                    location.reload();
                                } else {
                                    alert(<?php echo wp_json_encode(__('Reset failed: ', 'fd-admin-ui')); ?> + response.data);
                                }
                            }
                        });
                    }
                });
            });
            </script>
            <?php
        }
    }

    // ========================================
    // 自动折叠回调
    // ========================================
    public static function auto_fold_on_post_editor_callback() {
        $options = self::get_options();
        $checked = isset($options['menu_auto_fold_on_post_editor']) && $options['menu_auto_fold_on_post_editor'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[menu_auto_fold_on_post_editor]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('When enabled, the left sidebar menu is automatically collapsed when entering the post editor, providing more editing space. Users can still expand the menu manually; after expanding, the state is preserved on page refresh.', 'fd-admin-ui'); ?></p>
        <?php
    }

    // ========================================
    // SVG图标替换回调 - 委托给主类（因代码复杂）
    // ========================================
    public static function svg_icon_replacements_callback() {
        FD_Admin_UI_Settings::svg_icon_replacements_callback();
    }

    // ========================================
    // 站点标识相关回调
    // ========================================
    public static function branding_enable_callback() {
        $options = self::get_options();
        $checked = isset($options['menu_branding_enable']) && $options['menu_branding_enable'] ? 'checked' : '';
        ?>
        <label class="fd-switch">
            <input type="checkbox" name="fd_admin_ui_options[menu_branding_enable]" value="1" <?php echo esc_attr( $checked ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('When enabled, site branding is displayed at the top of the menu.', 'fd-admin-ui'); ?></p>
        <?php
    }

    public static function site_logo_full_callback() {
        FD_Admin_UI_Settings::menu_site_logo_full_callback();
    }

    public static function site_logo_square_callback() {
        FD_Admin_UI_Settings::menu_site_logo_square_callback();
    }

    public static function branding_link_callback() {
        FD_Admin_UI_Settings::menu_branding_link_callback();
    }

    public static function branding_show_icon_callback() {
        FD_Admin_UI_Settings::menu_branding_show_icon_callback();
    }
}
