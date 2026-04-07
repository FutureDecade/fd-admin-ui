<?php
/**
 * FD Post TOC - Post Editor Table of Contents Module
 * Displays a post outline on the left side of the classic editor, with clickable heading navigation
 */

defined('ABSPATH') || exit;

class FD_Post_TOC {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'content_toc_enable'        => true,
            'content_toc_heading_levels' => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ),
            'content_toc_default_open'  => true,
            'content_toc_width'         => 220,
        );
    }

    /**
     * Initialize
     */
    public static function init() {
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_footer',          array( __CLASS__, 'render_panel' ) );
        add_filter( 'admin_body_class',      array( __CLASS__, 'add_body_class' ) );
    }

    /**
     * Get current options
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Check if the current page is a post edit page
     */
    private static function is_post_editor() {
        $screen = get_current_screen();
        return $screen && $screen->base === 'post';
    }

    /**
     * Check if TOC feature is enabled
     */
    private static function is_enabled() {
        $options = self::get_options();
        return ! empty( $options['content_toc_enable'] );
    }

    /**
     * Enqueue assets (only on post edit pages)
     */
    public static function enqueue_assets( $hook ) {
        if ( ! self::is_post_editor() || ! self::is_enabled() ) {
            return;
        }

        wp_enqueue_style(
            'fd-post-toc',
            FD_ADMIN_UI_URI . 'assets/css/fd-post-toc.css',
            array( 'fd-admin-global' ),
            FD_ADMIN_UI_VERSION
        );

        wp_enqueue_script(
            'fd-post-toc',
            FD_ADMIN_UI_URI . 'assets/js/fd-post-toc.js',
            array( 'jquery' ),
            FD_ADMIN_UI_VERSION,
            true
        );

        $options        = self::get_options();
        $heading_levels = isset( $options['content_toc_heading_levels'] )
            ? (array) $options['content_toc_heading_levels']
            : array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
        $default_open   = isset( $options['content_toc_default_open'] )
            ? (bool) $options['content_toc_default_open']
            : true;
        $toc_width      = isset( $options['content_toc_width'] )
            ? absint( $options['content_toc_width'] )
            : 220;

        // Determine actual height based on admin bar style setting; inject CSS variable directly to avoid layout shift from JS measurement
        $adminbar_style = isset( $options['adminbar_style'] ) ? $options['adminbar_style'] : 'classic';
        $adminbar_h     = ( $adminbar_style === 'modern' )
            ? ( isset( $options['adminbar_modern_height'] ) ? absint( $options['adminbar_modern_height'] ) : 56 )
            : 32;

        // Get main content area background color
        $content_bg = isset( $options['content_bg_color'] ) ? $options['content_bg_color'] : '#f8fafc';

        // Inject CSS variables
        $inline_css = ':root { --fd-toc-adminbar-h: ' . $adminbar_h . 'px; --fd-toc-bg-color: ' . esc_attr( $content_bg ) . '; }';
        wp_add_inline_style( 'fd-post-toc', $inline_css );

        wp_localize_script( 'fd-post-toc', 'fdPostTOC', array(
            'headingLevels' => $heading_levels,
            'defaultOpen'   => $default_open,
            'tocWidth'      => $toc_width,
            'menuLeft'      => 53,   // Right edge of collapsed menu icon bar (including padding)
            'adminBarH'     => 32,   // WordPress admin bar height
            'i18n'          => array(
                'title'        => __( 'Post TOC', 'fd-admin-ui' ),
                'empty'        => __( 'No headings found', 'fd-admin-ui' ),
                'collapse'     => __( 'Collapse TOC', 'fd-admin-ui' ),
                'expand'       => __( 'Expand TOC', 'fd-admin-ui' ),
                'emptyHeading' => __( '(Empty heading)', 'fd-admin-ui' ),
            ),
        ) );
    }

    /**
     * Add identifier class to body
     */
    public static function add_body_class( $classes ) {
        if ( ! self::is_post_editor() || ! self::is_enabled() ) {
            return $classes;
        }
        $classes .= ' fd-toc-active';
        return $classes;
    }

    /**
     * Output TOC panel HTML skeleton in admin_footer
     */
    public static function render_panel() {
        if ( ! self::is_post_editor() || ! self::is_enabled() ) {
            return;
        }

        $options   = self::get_options();
        $toc_width = isset( $options['content_toc_width'] ) ? absint( $options['content_toc_width'] ) : 220;
        ?>
        <div id="fd-post-toc-wrap">

            <!-- Handle: only this bar is visible when collapsed -->
            <button id="fd-post-toc-handle" type="button" title="<?php esc_attr_e( 'Expand TOC', 'fd-admin-ui' ); ?>" aria-label="<?php esc_attr_e( 'Expand TOC', 'fd-admin-ui' ); ?>">
                <span class="fd-toc-handle-arrow">&#x203A;</span>
            </button>

            <!-- TOC panel body -->
            <div id="fd-post-toc-panel" role="navigation" aria-label="<?php esc_attr_e( 'Post TOC', 'fd-admin-ui' ); ?>">

                <div id="fd-post-toc-header">
                    <span id="fd-post-toc-title"><?php esc_html_e( 'Post TOC', 'fd-admin-ui' ); ?></span>
                    <button id="fd-post-toc-collapse" type="button" title="<?php esc_attr_e( 'Collapse TOC', 'fd-admin-ui' ); ?>" aria-label="<?php esc_attr_e( 'Collapse TOC', 'fd-admin-ui' ); ?>">
                        <span class="fd-toc-arrow">&#x2039;</span>
                    </button>
                </div>

                <div id="fd-post-toc-body">
                    <p id="fd-post-toc-empty"><?php esc_html_e( 'No headings found', 'fd-admin-ui' ); ?></p>
                    <ul id="fd-post-toc-list"></ul>
                </div>

            </div>
        </div>
        <?php
    }

    // ========================================
    // Settings Registration
    // ========================================

    /**
     * Sanitize module options
     */
    public static function sanitize( $input, $sanitized ) {
        // Enable toggle
        $sanitized['content_toc_enable'] = ! empty( $input['content_toc_enable'] );

        // Heading levels (multi-select)
        $allowed_levels = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
        if ( isset( $input['content_toc_heading_levels'] ) && is_array( $input['content_toc_heading_levels'] ) ) {
            $sanitized['content_toc_heading_levels'] = array_values(
                array_intersect( $input['content_toc_heading_levels'], $allowed_levels )
            );
        } else {
            $sanitized['content_toc_heading_levels'] = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
        }

        // Default expanded
        $sanitized['content_toc_default_open'] = ! empty( $input['content_toc_default_open'] );

        // Panel width
        if ( isset( $input['content_toc_width'] ) ) {
            $w = absint( $input['content_toc_width'] );
            $sanitized['content_toc_width'] = ( $w >= 160 && $w <= 400 ) ? $w : 220;
        }

        return $sanitized;
    }

    /**
     * Register settings fields
     */
    public static function register_settings() {
        add_settings_section(
            'fd_post_toc_section',
            __( 'Post Editor TOC', 'fd-admin-ui' ),
            array( __CLASS__, 'section_callback' ),
            'fd-admin-ui-settings-content'
        );

        add_settings_field(
            'content_toc_enable',
            __( 'Enable Post TOC', 'fd-admin-ui' ),
            array( __CLASS__, 'enable_callback' ),
            'fd-admin-ui-settings-content',
            'fd_post_toc_section'
        );

        add_settings_field(
            'content_toc_heading_levels',
            __( 'Heading Levels to Display', 'fd-admin-ui' ),
            array( __CLASS__, 'heading_levels_callback' ),
            'fd-admin-ui-settings-content',
            'fd_post_toc_section'
        );

        add_settings_field(
            'content_toc_default_open',
            __( 'Expand TOC by Default', 'fd-admin-ui' ),
            array( __CLASS__, 'default_open_callback' ),
            'fd-admin-ui-settings-content',
            'fd_post_toc_section'
        );

        add_settings_field(
            'content_toc_width',
            __( 'TOC Panel Width', 'fd-admin-ui' ),
            array( __CLASS__, 'width_callback' ),
            'fd-admin-ui-settings-content',
            'fd_post_toc_section'
        );
    }

    // ========================================
    // Settings Callbacks
    // ========================================

    public static function section_callback() {
        echo '<p>' . esc_html__( 'Display an outline TOC on the left side of the classic post editor. Click an entry to quickly jump to the corresponding heading.', 'fd-admin-ui' ) . '</p>';
    }

    public static function enable_callback() {
        $options = self::get_options();
        $enabled = ! empty( $options['content_toc_enable'] );
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[content_toc_enable]"
                   value="1"
                   <?php checked( $enabled ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'When enabled, a TOC panel will appear on the left side of the post edit page, and the left menu will be automatically collapsed.', 'fd-admin-ui' ); ?></p>
        <?php
    }

    public static function heading_levels_callback() {
        $options = self::get_options();
        $levels  = isset( $options['content_toc_heading_levels'] )
            ? (array) $options['content_toc_heading_levels']
            : array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
        $all     = array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6' );
        foreach ( $all as $val => $label ) {
            ?>
            <label style="margin-right:12px;">
                <input type="checkbox"
                       name="fd_admin_ui_options[content_toc_heading_levels][]"
                       value="<?php echo esc_attr( $val ); ?>"
                       <?php checked( in_array( $val, $levels, true ) ); ?>>
                <?php echo esc_html( $label ); ?>
            </label>
            <?php
        }
        echo '<p class="description">' . esc_html__( 'Select which heading levels to display in the TOC.', 'fd-admin-ui' ) . '</p>';
    }

    public static function default_open_callback() {
        $options = self::get_options();
        $open    = isset( $options['content_toc_default_open'] ) ? (bool) $options['content_toc_default_open'] : true;
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[content_toc_default_open]"
                   value="1"
                   <?php checked( $open ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'Whether the TOC panel should be automatically expanded when entering the edit page (state is also saved to localStorage).', 'fd-admin-ui' ); ?></p>
        <?php
    }

    public static function width_callback() {
        $options = self::get_options();
        $width   = isset( $options['content_toc_width'] ) ? absint( $options['content_toc_width'] ) : 220;
        ?>
        <input type="number"
               name="fd_admin_ui_options[content_toc_width]"
               value="<?php echo esc_attr( $width ); ?>"
               min="160" max="400" step="10"
               class="small-text"> px
        <p class="description"><?php esc_html_e( 'TOC panel width, range 160 - 400px, default 220px.', 'fd-admin-ui' ); ?></p>
        <?php
    }
}

// Initialize
FD_Post_TOC::init();
