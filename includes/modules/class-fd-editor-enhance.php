<?php
/**
 * FD Editor Enhance - Editor Enhancement Module
 * Provides slash commands, floating toolbar, quick link insertion, table enhancement, and paste cleanup for the classic editor (TinyMCE)
 */

defined('ABSPATH') || exit;

class FD_Editor_Enhance {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'editor_enhance_enable'        => true,
            'editor_enhance_slash_cmd'     => true,
            'editor_enhance_float_toolbar' => true,
            'editor_enhance_quick_link'    => true,
            'editor_enhance_table'         => true,
            'editor_enhance_paste_clean'   => true,
            'editor_enhance_hide_permalink' => true,
        );
    }

    /**
     * Initialize
     */
    public static function init() {
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_head', array( __CLASS__, 'output_inline_styles' ) );
    }

    /**
     * Get current options
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Check if the current page is a post editor (all post types)
     */
    private static function is_post_editor() {
        $screen = get_current_screen();
        // $screen->base === 'post' is true for post, page, and all custom post types
        return $screen && $screen->base === 'post';
    }

    /**
     * Check if the feature is enabled
     */
    private static function is_enabled() {
        $options = self::get_options();
        return ! empty( $options['editor_enhance_enable'] );
    }

    /**
     * Enqueue assets (for all post type editor pages)
     */
    public static function enqueue_assets( $hook ) {
        // post.php / post-new.php covers post, page, and all custom post types
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) || ! self::is_enabled() ) {
            return;
        }

        wp_enqueue_style(
            'fd-editor-enhance',
            FD_ADMIN_UI_URI . 'assets/css/fd-editor-enhance.css',
            array( 'fd-admin-global' ),
            FD_ADMIN_UI_VERSION
        );

        wp_enqueue_script(
            'fd-editor-enhance',
            FD_ADMIN_UI_URI . 'assets/js/fd-editor-enhance.js',
            array( 'jquery' ),
            FD_ADMIN_UI_VERSION,
            true
        );

        // Image insertion requires the media uploader
        wp_enqueue_media();

        $options = self::get_options();

        wp_localize_script( 'fd-editor-enhance', 'fdEditorEnhance', array(
            'features' => array(
                'slashCmd'     => ! empty( $options['editor_enhance_slash_cmd'] ),
                'floatToolbar' => ! empty( $options['editor_enhance_float_toolbar'] ),
                'quickLink'    => ! empty( $options['editor_enhance_quick_link'] ),
                'table'        => ! empty( $options['editor_enhance_table'] ),
                'pasteClean'   => ! empty( $options['editor_enhance_paste_clean'] ),
            ),
            'i18n' => array(
                'slash_h2'          => __( 'Heading 2', 'fd-admin-ui' ),
                'slash_h3'          => __( 'Heading 3', 'fd-admin-ui' ),
                'slash_h4'          => __( 'Heading 4', 'fd-admin-ui' ),
                'slash_ul'          => __( 'Unordered List', 'fd-admin-ui' ),
                'slash_ol'          => __( 'Ordered List', 'fd-admin-ui' ),
                'slash_hr'          => __( 'Horizontal Rule', 'fd-admin-ui' ),
                'slash_blockquote'  => __( 'Blockquote', 'fd-admin-ui' ),
                'slash_code'        => __( 'Code Block', 'fd-admin-ui' ),
                'slash_table'       => __( 'Table', 'fd-admin-ui' ),
                'slash_img'         => __( 'Insert Image', 'fd-admin-ui' ),
                'slash_placeholder' => __( 'Type a command to filter...', 'fd-admin-ui' ),
                'bold'              => __( 'Bold', 'fd-admin-ui' ),
                'italic'            => __( 'Italic', 'fd-admin-ui' ),
                'strikethrough'     => __( 'Strikethrough', 'fd-admin-ui' ),
                'link'              => __( 'Insert Link', 'fd-admin-ui' ),
                'code'              => __( 'Inline Code', 'fd-admin-ui' ),
                'clear_format'      => __( 'Clear Formatting', 'fd-admin-ui' ),
                'heading'           => __( 'Heading', 'fd-admin-ui' ),
                'link_created'      => __( 'Link created automatically', 'fd-admin-ui' ),
                'link_wrapped'      => __( 'Selected text converted to link', 'fd-admin-ui' ),
                'link_prompt'       => __( 'Enter the link URL:', 'fd-admin-ui' ),
                'add_row_above'     => __( 'Insert Row Above', 'fd-admin-ui' ),
                'add_row_below'     => __( 'Insert Row Below', 'fd-admin-ui' ),
                'add_col_left'      => __( 'Insert Column Left', 'fd-admin-ui' ),
                'add_col_right'     => __( 'Insert Column Right', 'fd-admin-ui' ),
                'delete_row'        => __( 'Delete Row', 'fd-admin-ui' ),
                'delete_col'        => __( 'Delete Column', 'fd-admin-ui' ),
                'delete_table'      => __( 'Delete Table', 'fd-admin-ui' ),
                'paste_cleaned'     => __( 'Pasted content cleaned', 'fd-admin-ui' ),
                'no_match'          => __( 'No matching commands', 'fd-admin-ui' ),
                'col_1'             => __( 'Column 1', 'fd-admin-ui' ),
                'col_2'             => __( 'Column 2', 'fd-admin-ui' ),
                'col_3'             => __( 'Column 3', 'fd-admin-ui' ),
            ),
        ) );
    }

    /**
     * Output inline styles (for styles that need to take effect immediately, such as hiding the permalink)
     * Applies to all post types
     */
    public static function output_inline_styles() {
        if ( ! self::is_enabled() || ! self::is_post_editor() ) {
            return;
        }

        $options = self::get_options();

        if ( ! empty( $options['editor_enhance_hide_permalink'] ) ) {
            echo '<style>#edit-slug-box { display: none !important; }</style>';
        }
    }

    // ========================================
    // Settings Sanitization
    // ========================================

    public static function sanitize( $input, $sanitized ) {
        $sanitized['editor_enhance_enable']         = ! empty( $input['editor_enhance_enable'] );
        $sanitized['editor_enhance_slash_cmd']      = ! empty( $input['editor_enhance_slash_cmd'] );
        $sanitized['editor_enhance_float_toolbar']  = ! empty( $input['editor_enhance_float_toolbar'] );
        $sanitized['editor_enhance_quick_link']     = ! empty( $input['editor_enhance_quick_link'] );
        $sanitized['editor_enhance_table']          = ! empty( $input['editor_enhance_table'] );
        $sanitized['editor_enhance_paste_clean']    = ! empty( $input['editor_enhance_paste_clean'] );
        $sanitized['editor_enhance_hide_permalink'] = ! empty( $input['editor_enhance_hide_permalink'] );
        return $sanitized;
    }

    // ========================================
    // Settings Registration
    // ========================================

    public static function register_settings() {
        add_settings_section(
            'fd_editor_enhance_section',
            __( 'Editor Enhancement', 'fd-admin-ui' ),
            array( __CLASS__, 'section_callback' ),
            'fd-admin-ui-settings-editor'
        );

        add_settings_field(
            'editor_enhance_enable',
            __( 'Enable Editor Enhancement', 'fd-admin-ui' ),
            array( __CLASS__, 'enable_callback' ),
            'fd-admin-ui-settings-editor',
            'fd_editor_enhance_section'
        );

        add_settings_field(
            'editor_enhance_slash_cmd',
            __( 'Slash Commands', 'fd-admin-ui' ),
            array( __CLASS__, 'slash_cmd_callback' ),
            'fd-admin-ui-settings-editor',
            'fd_editor_enhance_section'
        );

        add_settings_field(
            'editor_enhance_float_toolbar',
            __( 'Floating Toolbar', 'fd-admin-ui' ),
            array( __CLASS__, 'float_toolbar_callback' ),
            'fd-admin-ui-settings-editor',
            'fd_editor_enhance_section'
        );

        add_settings_field(
            'editor_enhance_quick_link',
            __( 'Quick Link Insertion', 'fd-admin-ui' ),
            array( __CLASS__, 'quick_link_callback' ),
            'fd-admin-ui-settings-editor',
            'fd_editor_enhance_section'
        );

        add_settings_field(
            'editor_enhance_table',
            __( 'Table Enhancement', 'fd-admin-ui' ),
            array( __CLASS__, 'table_callback' ),
            'fd-admin-ui-settings-editor',
            'fd_editor_enhance_section'
        );

        add_settings_field(
            'editor_enhance_paste_clean',
            __( 'Paste Cleanup', 'fd-admin-ui' ),
            array( __CLASS__, 'paste_clean_callback' ),
            'fd-admin-ui-settings-editor',
            'fd_editor_enhance_section'
        );

        add_settings_field(
            'editor_enhance_hide_permalink',
            __( 'Hide Permalink', 'fd-admin-ui' ),
            array( __CLASS__, 'hide_permalink_callback' ),
            'fd-admin-ui-settings-editor',
            'fd_editor_enhance_section'
        );
    }

    // ========================================
    // Settings Callbacks
    // ========================================

    public static function section_callback() {
        echo '<p>' . esc_html__( 'Add enhancement features to the classic editor (TinyMCE) to improve editing efficiency. Applies to all post type editors (posts, pages, and custom post types).', 'fd-admin-ui' ) . '</p>';
    }

    public static function enable_callback() {
        $options = self::get_options();
        $enabled = ! empty( $options['editor_enhance_enable'] );
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[editor_enhance_enable]"
                   value="1"
                   <?php checked( $enabled ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'Master switch. When enabled, activates the following enhancement features in the classic editor for all post types.', 'fd-admin-ui' ); ?></p>
        <?php
    }

    public static function slash_cmd_callback() {
        $options = self::get_options();
        $enabled = ! empty( $options['editor_enhance_slash_cmd'] );
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[editor_enhance_slash_cmd]"
                   value="1"
                   <?php checked( $enabled ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'Type / on an empty line to open a quick insert menu. Supports headings, lists, blockquotes, code blocks, tables, images, and more.', 'fd-admin-ui' ); ?></p>
        <?php
    }

    public static function float_toolbar_callback() {
        $options = self::get_options();
        $enabled = ! empty( $options['editor_enhance_float_toolbar'] );
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[editor_enhance_float_toolbar]"
                   value="1"
                   <?php checked( $enabled ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'Show a floating toolbar near the cursor when text is selected for quick formatting such as bold, italic, and links.', 'fd-admin-ui' ); ?></p>
        <?php
    }

    public static function quick_link_callback() {
        $options = self::get_options();
        $enabled = ! empty( $options['editor_enhance_quick_link'] );
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[editor_enhance_quick_link]"
                   value="1"
                   <?php checked( $enabled ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'Automatically convert pasted URLs into links; paste a URL with text selected to create a hyperlink.', 'fd-admin-ui' ); ?></p>
        <?php
    }

    public static function table_callback() {
        $options = self::get_options();
        $enabled = ! empty( $options['editor_enhance_table'] );
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[editor_enhance_table]"
                   value="1"
                   <?php checked( $enabled ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'Show an enhanced context menu on right-click within tables for quick row and column management.', 'fd-admin-ui' ); ?></p>
        <?php
    }

    public static function paste_clean_callback() {
        $options = self::get_options();
        $enabled = ! empty( $options['editor_enhance_paste_clean'] );
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[editor_enhance_paste_clean]"
                   value="1"
                   <?php checked( $enabled ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'Automatically clean unnecessary styles and markup when pasting content from Word, web pages, etc., while preserving semantic structure.', 'fd-admin-ui' ); ?></p>
        <?php
    }

    public static function hide_permalink_callback() {
        $options = self::get_options();
        $enabled = ! empty( $options['editor_enhance_hide_permalink'] );
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[editor_enhance_hide_permalink]"
                   value="1"
                   <?php checked( $enabled ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'Hide the permalink section on the post editor page. Useful for headless architectures where the backend permalink is no longer relevant.', 'fd-admin-ui' ); ?></p>
        <?php
    }
}

// Initialize
FD_Editor_Enhance::init();
