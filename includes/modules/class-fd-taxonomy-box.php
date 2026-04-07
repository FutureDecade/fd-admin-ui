<?php
/**
 * FD Taxonomy Box - Taxonomy Meta Box Integration
 * Consolidates categories, tags, and custom taxonomies into a single compact meta box
 */

defined('ABSPATH') || exit;

class FD_Taxonomy_Box {

    /**
     * Get module default options
     */
    public static function get_defaults() {
        return array(
            'taxonomy_box_enable' => true,
        );
    }

    /**
     * Register settings
     */
    public static function register_settings() {
        $options = self::get_options();

        // If enabled, initialize the module
        if ( ! empty( $options['taxonomy_box_enable'] ) ) {
            self::init();
        }

        add_settings_section(
            'fd_taxonomy_box_section',
            __( 'Taxonomy Meta Box Integration', 'fd-admin-ui' ),
            array( __CLASS__, 'render_section_description' ),
            'fd-admin-ui-settings-editor'
        );

        add_settings_field(
            'taxonomy_box_enable',
            __( 'Enable Taxonomy Meta Box Integration', 'fd-admin-ui' ),
            array( __CLASS__, 'render_enable_field' ),
            'fd-admin-ui-settings-editor',
            'fd_taxonomy_box_section'
        );
    }

    /**
     * Render section description
     */
    public static function render_section_description() {
        echo '<p>' . esc_html__( 'Consolidates categories, tags, and custom taxonomies into a single compact meta box to improve editing efficiency.', 'fd-admin-ui' ) . '</p>';
    }

    /**
     * Render enable toggle
     */
    public static function render_enable_field() {
        $options = self::get_options();
        $checked = ! empty( $options['taxonomy_box_enable'] );
        ?>
        <label>
            <input type="checkbox"
                   name="fd_admin_ui_options[taxonomy_box_enable]"
                   value="1"
                   <?php checked( $checked ); ?>>
            <?php esc_html_e( 'Enable unified taxonomy meta box (compact list layout)', 'fd-admin-ui' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'When enabled, the default WordPress taxonomy meta boxes will be replaced with a unified compact layout.', 'fd-admin-ui' ); ?><br>
            <?php esc_html_e( 'Supports categories, tags, and all custom taxonomies.', 'fd-admin-ui' ); ?>
        </p>
        <?php
    }

    /**
     * Sanitize options
     */
    public static function sanitize( $input, $sanitized ) {
        if ( isset( $input['taxonomy_box_enable'] ) ) {
            $sanitized['taxonomy_box_enable'] = (bool) $input['taxonomy_box_enable'];
        } else {
            $sanitized['taxonomy_box_enable'] = false;
        }

        return $sanitized;
    }

    /**
     * Initialize
     */
    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ), 20 );
        add_action( 'save_post', array( __CLASS__, 'save_taxonomies' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

        // AJAX handler: get tag suggestions
        add_action( 'wp_ajax_fd_get_tag_suggestions', array( __CLASS__, 'ajax_get_tag_suggestions' ) );
    }

    /**
     * Get current options
     */
    private static function get_options() {
        return FD_Admin_UI_Settings::get_options();
    }

    /**
     * Check if feature is enabled
     */
    private static function is_enabled() {
        $options = self::get_options();
        return ! empty( $options['taxonomy_box_enable'] );
    }

    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        if ( ! self::is_enabled() ) {
            return;
        }

        $post_types = get_post_types( array( 'public' => true ), 'names' );

        foreach ( $post_types as $post_type ) {
            // Get all taxonomies for this post type
            $taxonomies = get_object_taxonomies( $post_type, 'objects' );

            if ( empty( $taxonomies ) ) {
                continue;
            }

            // Remove original taxonomy meta boxes
            foreach ( $taxonomies as $taxonomy ) {
                if ( $taxonomy->show_ui ) {
                    // Remove hierarchical taxonomy meta box
                    if ( $taxonomy->hierarchical ) {
                        remove_meta_box( $taxonomy->name . 'div', $post_type, 'side' );
                        remove_meta_box( $taxonomy->name . 'div', $post_type, 'normal' );
                    } else {
                        // Remove tag meta box
                        remove_meta_box( 'tagsdiv-' . $taxonomy->name, $post_type, 'side' );
                        remove_meta_box( 'tagsdiv-' . $taxonomy->name, $post_type, 'normal' );
                    }
                }
            }

            // Add unified taxonomy meta box
            add_meta_box(
                'fd-taxonomy-box',
                __( 'Categories & Tags', 'fd-admin-ui' ),
                array( __CLASS__, 'render_meta_box' ),
                $post_type,
                'side',
                'high'
            );
        }
    }

    /**
     * Render meta box content
     */
    public static function render_meta_box( $post ) {
        // Add nonce field
        wp_nonce_field( 'fd_taxonomy_box_save', 'fd_taxonomy_box_nonce' );

        // Get all taxonomies for this post type
        $taxonomies = get_object_taxonomies( $post->post_type, 'objects' );

        if ( empty( $taxonomies ) ) {
            echo '<p>' . esc_html__( 'No taxonomies available for this post type.', 'fd-admin-ui' ) . '</p>';
            return;
        }

        echo '<div class="fd-taxonomy-box">';

        foreach ( $taxonomies as $taxonomy ) {
            if ( ! $taxonomy->show_ui ) {
                continue;
            }

            self::render_taxonomy_section( $post, $taxonomy );
        }

        echo '</div>';
    }

    /**
     * Render a single taxonomy section
     */
    private static function render_taxonomy_section( $post, $taxonomy ) {
        $terms = wp_get_object_terms( $post->ID, $taxonomy->name );
        $term_ids = wp_list_pluck( $terms, 'term_id' );
        $selected_count = count( $term_ids );

        echo '<div class="fd-taxonomy-section" data-taxonomy="' . esc_attr( $taxonomy->name ) . '">';

        // Title and count
        echo '<div class="fd-taxonomy-header">';
        echo '<span class="fd-taxonomy-title">' . esc_html( $taxonomy->label ) . '</span>';
        /* translators: %d: number of selected terms */
        echo '<span class="fd-taxonomy-count">' . esc_html( sprintf( __( '%d selected', 'fd-admin-ui' ), $selected_count ) ) . '</span>';
        echo '</div>';

        // Render different content based on taxonomy type
        if ( $taxonomy->hierarchical ) {
            // Hierarchical taxonomy (e.g. categories)
            self::render_hierarchical_taxonomy( $post, $taxonomy, $term_ids );
        } else {
            // Flat taxonomy (e.g. tags)
            self::render_flat_taxonomy( $post, $taxonomy, $terms );
        }

        echo '</div>';
    }

    /**
     * Render hierarchical taxonomy (checkboxes)
     */
    private static function render_hierarchical_taxonomy( $post, $taxonomy, $selected_term_ids ) {
        $all_terms = get_terms( array(
            'taxonomy' => $taxonomy->name,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ) );

        if ( empty( $all_terms ) || is_wp_error( $all_terms ) ) {
            /* translators: %s: taxonomy label */
            echo '<p class="fd-no-terms">' . esc_html( sprintf( __( 'No %s found', 'fd-admin-ui' ), $taxonomy->label ) ) . '</p>';
            return;
        }

        echo '<div class="fd-category-list">';

        foreach ( $all_terms as $term ) {
            $is_selected = in_array( $term->term_id, $selected_term_ids );
            $class = $is_selected ? 'fd-category-item selected' : 'fd-category-item';

            echo '<div class="' . esc_attr( $class ) . '" data-term-id="' . esc_attr( $term->term_id ) . '">';
            echo '<span class="fd-checkbox">' . ( $is_selected ? '&#x2713;' : '' ) . '</span>';
            echo '<span class="fd-term-name">' . esc_html( $term->name ) . '</span>';
            echo '<input type="checkbox" name="tax_input[' . esc_attr( $taxonomy->name ) . '][]" ';
            echo 'value="' . esc_attr( $term->term_id ) . '" ';
            checked( $is_selected );
            echo 'style="display:none;">';
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Render flat taxonomy (tags)
     */
    private static function render_flat_taxonomy( $post, $taxonomy, $selected_terms ) {
        // Display selected tags
        if ( ! empty( $selected_terms ) ) {
            echo '<div class="fd-tag-list">';
            foreach ( $selected_terms as $term ) {
                echo '<div class="fd-tag-item" data-term-id="' . esc_attr( $term->term_id ) . '">';
                echo '<span class="fd-tag-name">' . esc_html( $term->name ) . '</span>';
                echo '<span class="fd-tag-remove" title="' . esc_attr__( 'Remove', 'fd-admin-ui' ) . '">&times;</span>';
                echo '</div>';
            }
            echo '</div>';
        }

        // Tag input field
        $term_names = wp_list_pluck( $selected_terms, 'name' );
        $term_names_string = implode( ',', $term_names );

        echo '<div class="fd-tag-input-wrapper">';
        echo '<input type="text" ';
        echo 'class="fd-tag-input" ';
        echo 'name="tax_input[' . esc_attr( $taxonomy->name ) . ']" ';
        echo 'value="' . esc_attr( $term_names_string ) . '" ';
        /* translators: %s: taxonomy label */
        echo 'placeholder="' . esc_attr( sprintf( __( 'Enter %s, press Enter to add', 'fd-admin-ui' ), $taxonomy->label ) ) . '" ';
        echo 'data-taxonomy="' . esc_attr( $taxonomy->name ) . '">';
        echo '<div class="fd-tag-suggestions"></div>';
        echo '</div>';
    }

    /**
     * Save taxonomy data
     */
    public static function save_taxonomies( $post_id, $post ) {
        // Verify nonce
        if ( ! isset( $_POST['fd_taxonomy_box_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fd_taxonomy_box_nonce'] ) ), 'fd_taxonomy_box_save' ) ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Get all taxonomies for this post type
        $taxonomies = get_object_taxonomies( $post->post_type, 'objects' );

        foreach ( $taxonomies as $taxonomy ) {
            if ( ! $taxonomy->show_ui ) {
                continue;
            }

            if ( ! isset( $_POST['tax_input'][ $taxonomy->name ] ) ) {
                // If no data submitted, clear the taxonomy associations
                wp_set_object_terms( $post_id, array(), $taxonomy->name );
                continue;
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized on the next line
            $raw_terms = wp_unslash( $_POST['tax_input'][ $taxonomy->name ] );
            $terms = is_array( $raw_terms ) ? array_map( 'sanitize_text_field', $raw_terms ) : sanitize_text_field( $raw_terms );

            if ( $taxonomy->hierarchical ) {
                // Hierarchical taxonomy: array of term_ids
                $term_ids = array_map( 'intval', (array) $terms );
                wp_set_object_terms( $post_id, $term_ids, $taxonomy->name );
            } else {
                // Flat taxonomy: comma-separated string
                if ( is_string( $terms ) ) {
                    $term_names = array_map( 'trim', explode( ',', $terms ) );
                    $term_names = array_filter( $term_names ); // Remove empty values
                    wp_set_object_terms( $post_id, $term_names, $taxonomy->name );
                }
            }
        }
    }

    /**
     * AJAX: Get tag suggestions
     */
    public static function ajax_get_tag_suggestions() {
        check_ajax_referer( 'fd_taxonomy_box_ajax', 'nonce' );

        $taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : '';
        $search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

        if ( empty( $taxonomy ) || empty( $search ) ) {
            wp_send_json_error( __( 'Invalid parameters', 'fd-admin-ui' ) );
        }

        // Get matching tags
        $terms = get_terms( array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'search' => $search,
            'number' => 10,
            'orderby' => 'count',
            'order' => 'DESC',
        ) );

        if ( is_wp_error( $terms ) ) {
            wp_send_json_error( $terms->get_error_message() );
        }

        $suggestions = array();
        foreach ( $terms as $term ) {
            $suggestions[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'count' => $term->count,
            );
        }

        wp_send_json_success( $suggestions );
    }

    /**
     * Enqueue frontend assets
     */
    public static function enqueue_assets( $hook ) {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
            return;
        }

        if ( ! self::is_enabled() ) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'fd-taxonomy-box',
            FD_ADMIN_UI_URI . 'assets/css/fd-taxonomy-box.css',
            array(),
            FD_ADMIN_UI_VERSION
        );

        // JavaScript
        wp_enqueue_script(
            'fd-taxonomy-box',
            FD_ADMIN_UI_URI . 'assets/js/fd-taxonomy-box.js',
            array( 'jquery' ),
            FD_ADMIN_UI_VERSION,
            true
        );

        // Pass data to JavaScript
        wp_localize_script( 'fd-taxonomy-box', 'fdTaxonomyBox', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'fd_taxonomy_box_ajax' ),
            'i18n' => array(
                'remove'   => __( 'Remove', 'fd-admin-ui' ),
                'selected' => __( 'Selected', 'fd-admin-ui' ),
            ),
        ) );

        // Get main content area background color and inject CSS variables
        $options = self::get_options();
        $content_bg = isset( $options['content_bg_color'] ) ? $options['content_bg_color'] : '#f8fafc';
        $inline_css = '
            .fd-taxonomy-box { background: ' . esc_attr( $content_bg ) . '; }
            #fd-taxonomy-box .postbox-header { background: var(--fd-gray-100, #f6f7f7) !important; border-bottom: 1px solid var(--fd-gray-200, #dcdcde); }
            #fd-taxonomy-box .hndle { background: transparent !important; }
            #fd-taxonomy-box .handle-actions { background: transparent !important; }
            #fd-taxonomy-box .handlediv,
            #fd-taxonomy-box .handle-actions button { background: transparent !important; }
            #fd-taxonomy-box .handlediv:hover,
            #fd-taxonomy-box .handle-actions button:hover { background: rgba(0, 0, 0, 0.05) !important; }
        ';
        wp_add_inline_style( 'fd-taxonomy-box', $inline_css );
    }
}
