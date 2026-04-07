<?php
/**
 * FD Post Meta Box - Post Meta Box Enhancement
 * Merges featured image and excerpt into a unified meta box below the editor
 */

defined('ABSPATH') || exit;

class FD_Post_Meta_Box {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'post_meta_box_enable' => true,
        );
    }

    /**
     * Initialize
     */
    public static function init() {
        // Remove original meta boxes
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );

        // No longer using meta boxes, output directly to the editor area instead
        add_action( 'edit_form_after_title', array( __CLASS__, 'render_author_section' ) );
        add_action( 'edit_form_after_editor', array( __CLASS__, 'render_featured_excerpt_section' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'save_post', array( __CLASS__, 'save_post_data' ), 10, 2 );

        // Merge TinyMCE toolbar into a single row
        add_filter( 'tiny_mce_before_init', array( __CLASS__, 'merge_tinymce_toolbars' ) );

        // AJAX handler: get author suggestions
        add_action( 'wp_ajax_fd_get_author_suggestions', array( __CLASS__, 'ajax_get_author_suggestions' ) );

        // Bottom fixed status bar
        add_action( 'admin_footer', array( __CLASS__, 'render_bottom_bar' ) );
    }

    /**
     * Get current options
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Check if the feature is enabled
     */
    private static function is_enabled() {
        $options = self::get_options();
        return ! empty( $options['post_meta_box_enable'] );
    }

    /**
     * Merge TinyMCE toolbars into a single row and remove the show/hide toggle button
     */
    public static function merge_tinymce_toolbars( $settings ) {
        if ( ! self::is_enabled() ) {
            return $settings;
        }

        // Merge toolbar2 into toolbar1
        if ( ! empty( $settings['toolbar2'] ) ) {
            $settings['toolbar1'] = rtrim( $settings['toolbar1'], ', ' ) . ',' . $settings['toolbar2'];
            $settings['toolbar2'] = '';
        }

        // Remove wp_adv (show/hide toolbar button)
        $buttons = array_map( 'trim', explode( ',', $settings['toolbar1'] ) );
        $buttons = array_filter( $buttons, function( $btn ) {
            return $btn !== '' && $btn !== 'wp_adv';
        });
        $settings['toolbar1'] = implode( ',', $buttons );

        return $settings;
    }

    /**
     * Render the author section (above the editor, all post types)
     */
    public static function render_author_section( $post ) {
        if ( ! self::is_enabled() ) {
            return;
        }

        $custom_author = '';

        if ( 'post' === $post->post_type ) {
            $custom_author = get_post_meta( $post->ID, 'article_author', true );

            if ( '' === $custom_author && function_exists( 'get_field' ) ) {
                $custom_author = get_field( 'article_author', $post->ID );
            }
        }

        // Get WordPress default author
        $post_author = $post->post_author;

        // Determine the current displayed author name
        $current_author_name = '';
        if ( ! empty( $custom_author ) ) {
            // If there is a custom author, display the custom author
            $current_author_name = $custom_author;
        } else {
            // Otherwise display the WordPress author's display name
            $author_obj = get_userdata( $post_author );
            if ( $author_obj ) {
                $current_author_name = $author_obj->display_name;
            }
        }

        ?>
        <div class="fd-editor-author-section">
            <div class="fd-author-row">
                <div class="fd-author-field-wrapper">
                    <div class="fd-author-input-container">
                        <input
                            type="text"
                            id="fd-author-input"
                            class="fd-author-input"
                            value="<?php echo esc_attr( $current_author_name ); ?>"
                            placeholder="<?php esc_attr_e( 'Add author', 'fd-admin-ui' ); ?>"
                            autocomplete="off"
                        >
                        <div class="fd-author-suggestions"></div>
                    </div>

                    <!-- Hidden field: WordPress author ID -->
                    <input type="hidden" name="post_author_override" id="fd-wp-author-id" value="<?php echo esc_attr( $post_author ); ?>">

                    <!-- Hidden field: Custom author name -->
                    <input type="hidden" name="fd_post_template_article_author" id="fd-custom-author-name" value="<?php echo esc_attr( $custom_author ); ?>">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the featured image and excerpt section (below the editor, conditionally rendered based on post type supports)
     */
    public static function render_featured_excerpt_section( $post ) {
        if ( ! self::is_enabled() ) {
            return;
        }

        $supports_thumbnail = post_type_supports( $post->post_type, 'thumbnail' );
        $supports_excerpt   = post_type_supports( $post->post_type, 'excerpt' );

        // Do not render if neither is supported
        if ( ! $supports_thumbnail && ! $supports_excerpt ) {
            return;
        }

        $thumbnail_id = $supports_thumbnail ? get_post_thumbnail_id( $post->ID ) : 0;
        $excerpt      = $post->post_excerpt;
        ?>
        <div class="fd-editor-featured-excerpt-section">
            <?php wp_nonce_field( 'fd_post_meta_box_save', 'fd_post_meta_box_nonce' ); ?>
            <div class="fd-featured-excerpt-row">
                <?php if ( $supports_thumbnail ) : ?>
                <!-- Featured Image Section -->
                <div class="fd-featured-section">
                    <label class="fd-section-label"><?php esc_html_e( 'Cover Image', 'fd-admin-ui' ); ?></label>
                    <div class="fd-featured-image-preview" id="fd-featured-image-preview">
                        <?php if ( $thumbnail_id ) : ?>
                            <?php echo wp_get_attachment_image( $thumbnail_id, 'medium', false, array( 'id' => 'fd-featured-image-img' ) ); ?>
                            <div class="fd-featured-image-overlay">
                                <button type="button" class="fd-image-action" id="fd-set-featured-image" title="<?php esc_attr_e( 'Change Cover', 'fd-admin-ui' ); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button type="button" class="fd-image-action fd-remove-featured-image" id="fd-remove-featured-image" title="<?php esc_attr_e( 'Remove Cover', 'fd-admin-ui' ); ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                            </div>
                        <?php else : ?>
                            <div class="fd-featured-image-placeholder" id="fd-set-featured-image">
                                <span class="dashicons dashicons-format-image"></span>
                                <span><?php esc_html_e( 'Click to set cover image', 'fd-admin-ui' ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" id="fd-featured-image-id" name="_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>">
                </div>
                <?php endif; ?>

                <?php if ( $supports_excerpt ) : ?>
                <!-- Excerpt Section -->
                <div class="fd-excerpt-section">
                    <label for="fd-post-excerpt" class="fd-section-label">
                        <?php esc_html_e( 'Excerpt', 'fd-admin-ui' ); ?>
                        <span class="fd-excerpt-counter">
                            <span class="fd-excerpt-count"><?php echo intval( mb_strlen( $excerpt ) ); ?></span>/120
                        </span>
                    </label>
                    <textarea
                        id="fd-post-excerpt"
                        name="excerpt"
                        rows="3"
                        maxlength="120"
                        placeholder="<?php esc_attr_e( 'Enter an excerpt (optional; if left blank, the beginning of the content will be used)', 'fd-admin-ui' ); ?>"
                    ><?php echo esc_textarea( $excerpt ); ?></textarea>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save post data
     */
    public static function save_post_data( $post_id, $post ) {
        if ( ! self::is_enabled() ) {
            return;
        }

        // Verify nonce
        if ( ! isset( $_POST['fd_post_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fd_post_meta_box_nonce'] ) ), 'fd_post_meta_box_save' ) ) {
            return;
        }

        // Check for autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save featured image
        if ( isset( $_POST['_thumbnail_id'] ) ) {
            $thumbnail_id = intval( $_POST['_thumbnail_id'] );
            if ( $thumbnail_id > 0 ) {
                set_post_thumbnail( $post_id, $thumbnail_id );
            } else {
                delete_post_thumbnail( $post_id );
            }
        }

        // Save excerpt (WordPress handles the excerpt field automatically)

        if ( 'post' === $post->post_type && array_key_exists( 'fd_post_template_article_author', $_POST ) ) {
            update_post_meta(
                $post_id,
                'article_author',
                sanitize_text_field( wp_unslash( $_POST['fd_post_template_article_author'] ) )
            );
        }
    }

    /**
     * AJAX: Get author suggestions
     */
    public static function ajax_get_author_suggestions() {
        check_ajax_referer( 'fd_post_meta_box_ajax', 'nonce' );

        $search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

        if ( empty( $search ) ) {
            wp_send_json_error( 'Invalid parameters' );
        }

        // Search WordPress users
        $users = get_users( array(
            'search'         => '*' . $search . '*',
            'search_columns' => array( 'user_login', 'user_nicename', 'display_name' ),
            'number'         => 10,
            'orderby'        => 'display_name',
            'order'          => 'ASC',
        ) );

        $suggestions = array();
        foreach ( $users as $user ) {
            $suggestions[] = array(
                'id'           => $user->ID,
                'display_name' => $user->display_name,
                'user_login'   => $user->user_login,
                'is_wp_user'   => true,
            );
        }

        wp_send_json_success( $suggestions );
    }

    /**
     * Remove original meta boxes (applies to all post types that support the respective features)
     */
    public static function add_meta_boxes() {
        if ( ! self::is_enabled() ) {
            return;
        }

        $post_types = get_post_types( array( 'show_ui' => true ) );

        foreach ( $post_types as $post_type ) {
            if ( post_type_supports( $post_type, 'thumbnail' ) ) {
                remove_meta_box( 'postimagediv', $post_type, 'side' );
            }
            if ( post_type_supports( $post_type, 'excerpt' ) ) {
                remove_meta_box( 'postexcerpt', $post_type, 'normal' );
            }
            if ( post_type_supports( $post_type, 'author' ) ) {
                remove_meta_box( 'authordiv', $post_type, 'normal' );
            }
        }
    }

    /**
     * Enqueue assets
     */
    public static function enqueue_assets( $hook ) {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) || ! self::is_enabled() ) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'fd-post-meta-box',
            FD_ADMIN_UI_URI . 'assets/css/fd-post-meta-box.css',
            array( 'fd-admin-global' ),
            FD_ADMIN_UI_VERSION
        );

        wp_enqueue_script(
            'fd-post-meta-box',
            FD_ADMIN_UI_URI . 'assets/js/fd-post-meta-box.js',
            array( 'jquery' ),
            FD_ADMIN_UI_VERSION,
            true
        );

        // Pass data to JavaScript
        global $post;
        $saved_time = '';
        if ( $post && ! empty( $post->post_modified ) && '0000-00-00 00:00:00' !== $post->post_modified ) {
            $modified_ts  = strtotime( get_date_from_gmt( $post->post_modified_gmt ) );
            $today_start  = strtotime( 'today midnight' );
            if ( $modified_ts >= $today_start ) {
                $saved_time = date_i18n( 'H:i', $modified_ts );
            } else {
                $saved_time = date_i18n( 'M j, H:i', $modified_ts );
            }
        }

        wp_localize_script( 'fd-post-meta-box', 'fdPostMetaBox', array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'fd_post_meta_box_ajax' ),
            'savedTime' => $saved_time,
            'i18n'      => array(
                'editorPlaceholder' => __( 'Start writing...', 'fd-admin-ui' ),
                'selectCoverImage'  => __( 'Select Cover Image', 'fd-admin-ui' ),
                'setAsCover'        => __( 'Set as Cover', 'fd-admin-ui' ),
                'changeCover'       => __( 'Change Cover', 'fd-admin-ui' ),
                'removeCover'       => __( 'Remove Cover', 'fd-admin-ui' ),
                'coverImage'        => __( 'Cover Image', 'fd-admin-ui' ),
            ),
        ) );

        // Get the main content area background color and inject CSS variable so attribute meta box matches taxonomy/tag meta boxes
        $options = self::get_options();
        $content_bg = isset( $options['content_bg_color'] ) ? $options['content_bg_color'] : '#f8fafc';
        $inline_css = '
            #pageparentdiv { background: ' . esc_attr( $content_bg ) . ' !important; }
            #pageparentdiv .inside { background: ' . esc_attr( $content_bg ) . ' !important; }
        ';
        wp_add_inline_style( 'fd-post-meta-box', $inline_css );
    }

    /**
     * Render bottom fixed status bar
     */
    public static function render_bottom_bar() {
        if ( ! self::is_enabled() ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || $screen->base !== 'post' ) {
            return;
        }

        global $post;
        if ( ! $post ) {
            return;
        }

        $status      = $post->post_status;
        $is_publish  = ( $status === 'publish' );
        $show_draft  = ! $is_publish;
        $action_btn  = $is_publish ? __( 'Update', 'fd-admin-ui' ) : __( 'Publish', 'fd-admin-ui' );

        $saved_time_display = '';
        if ( ! empty( $post->post_modified ) && '0000-00-00 00:00:00' !== $post->post_modified ) {
            $modified_ts = strtotime( get_date_from_gmt( $post->post_modified_gmt ) );
            $today_start = strtotime( 'today midnight' );
            if ( $modified_ts >= $today_start ) {
                $saved_time_display = date_i18n( 'H:i', $modified_ts );
            } else {
                $saved_time_display = date_i18n( 'M j, H:i', $modified_ts );
            }
        }
        ?>
        <div id="fd-editor-bottom-bar">
            <div class="fd-bottom-bar-left">
                <span class="fd-bar-stat">
                    <span id="fd-word-count">0</span> <?php esc_html_e( 'words', 'fd-admin-ui' ); ?>
                </span>
                <span class="fd-bar-stat fd-bar-saved-time<?php echo $saved_time_display ? ' fd-bar-visible' : ''; ?>" id="fd-bar-saved-time">
                    <?php esc_html_e( 'Saved at', 'fd-admin-ui' ); ?> <span id="fd-bar-saved-at"><?php echo esc_html( $saved_time_display ); ?></span>
                </span>
            </div>
            <div class="fd-bottom-bar-right">
                <?php if ( $show_draft ) : ?>
                <button type="button" class="fd-bar-btn fd-bar-btn-ghost" id="fd-bar-save-draft"><?php esc_html_e( 'Save Draft', 'fd-admin-ui' ); ?></button>
                <?php endif; ?>
                <button type="button" class="fd-bar-btn fd-bar-btn-ghost" id="fd-bar-preview"><?php esc_html_e( 'Preview', 'fd-admin-ui' ); ?></button>
                <button type="button" class="fd-bar-btn fd-bar-btn-primary" id="fd-bar-publish"><?php echo esc_html( $action_btn ); ?></button>
            </div>
        </div>
        <?php
    }

    // ========================================
    // Settings Sanitization
    // ========================================

    public static function sanitize( $input, $sanitized ) {
        $sanitized['post_meta_box_enable'] = ! empty( $input['post_meta_box_enable'] );
        return $sanitized;
    }

    // ========================================
    // Settings Registration
    // ========================================

    public static function register_settings() {
        add_settings_section(
            'fd_post_meta_box_section',
            __( 'Post Meta Box Enhancement', 'fd-admin-ui' ),
            array( __CLASS__, 'section_callback' ),
            'fd-admin-ui-settings-editor'
        );

        add_settings_field(
            'post_meta_box_enable',
            __( 'Enable Meta Box Enhancement', 'fd-admin-ui' ),
            array( __CLASS__, 'enable_callback' ),
            'fd-admin-ui-settings-editor',
            'fd_post_meta_box_section'
        );
    }

    // ========================================
    // Settings Callbacks
    // ========================================

    public static function section_callback() {
        echo '<p>' . esc_html__( 'Integrate author, cover image, and excerpt into the editor area as a fixed editing group that cannot be dragged.', 'fd-admin-ui' ) . '</p>';
        echo '<p>' . esc_html__( 'The author section is displayed above the editor, and the cover image and excerpt are displayed below the editor.', 'fd-admin-ui' ) . '</p>';
    }

    public static function enable_callback() {
        $options = self::get_options();
        $enabled = ! empty( $options['post_meta_box_enable'] );
        ?>
        <label class="fd-switch">
            <input type="checkbox"
                   name="fd_admin_ui_options[post_meta_box_enable]"
                   value="1"
                   <?php checked( $enabled ); ?>>
            <span class="fd-switch-slider"></span>
        </label>
        <p class="description"><?php esc_html_e( 'When enabled, integrates author, cover image, and excerpt into the editor area as a fixed editing group.', 'fd-admin-ui' ); ?></p>
        <?php
    }
}

// Initialize
FD_Post_Meta_Box::init();
