<?php
/**
 * Lingcoo Admin UI Framework - PHP Helper
 * Unified admin UI framework PHP helper class
 *
 * Provides reusable UI component rendering methods
 */

defined('ABSPATH') || exit;

// Prevent duplicate class definition
if (!class_exists('FD_Admin_UI')) {

class FD_Admin_UI {

    /**
     * Enqueue framework assets
     *
     * Note: Global assets are auto-loaded by the main plugin file.
     * This method is mainly for pages that need to ensure assets are loaded.
     */
    public static function enqueue_assets() {
        if (!did_action('admin_enqueue_scripts')) {
            return; // Only load in admin
        }

        // Ensure global styles are loaded
        if (!wp_style_is('fd-admin-global', 'enqueued')) {
            wp_enqueue_style(
                'fd-admin-global',
                FD_ADMIN_UI_URI . 'assets/css/fd-admin-global.css',
                array(),
                FD_ADMIN_UI_VERSION
            );
        }

        // Ensure component styles are loaded
        if (!wp_style_is('fd-admin-ui', 'enqueued')) {
            wp_enqueue_style(
                'fd-admin-ui',
                FD_ADMIN_UI_URI . 'assets/css/fd-admin-ui.css',
                array('fd-admin-global'),
                FD_ADMIN_UI_VERSION
            );
        }

        // WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // jQuery UI (for sortable)
        wp_enqueue_script('jquery-ui-sortable');

        // Component scripts
        if (!wp_script_is('fd-admin-ui', 'enqueued')) {
            wp_enqueue_script(
                'fd-admin-ui',
                FD_ADMIN_UI_URI . 'assets/js/fd-admin-ui.js',
                array('jquery', 'wp-color-picker', 'jquery-ui-sortable'),
                FD_ADMIN_UI_VERSION,
                true
            );
        }
    }

    /**
     * Render page header
     *
     * @param string $title Page title
     * @param string $subtitle Page subtitle (optional)
     * @param array $actions Action buttons array (optional)
     */
    public static function render_page_header($title, $subtitle = '', $actions = array()) {
        ?>
        <div class="fd-page-header">
            <div>
                <h1 class="fd-page-title"><?php echo esc_html($title); ?></h1>
                <?php if ($subtitle): ?>
                    <p class="fd-page-subtitle"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($actions)): ?>
                <div class="fd-page-actions">
                    <?php foreach ($actions as $action): ?>
                        <a href="<?php echo esc_url($action['url']); ?>"
                           class="fd-btn <?php echo esc_attr($action['class'] ?? 'fd-btn-primary'); ?>">
                            <?php if (!empty($action['icon'])): ?>
                                <span class="dashicons dashicons-<?php echo esc_attr($action['icon']); ?>"></span>
                            <?php endif; ?>
                            <?php echo esc_html($action['text']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render stat card
     *
     * @param string $label Label text
     * @param string|int $value Numeric value
     * @param string $icon Dashicon icon name
     * @param string $color Color (hex)
     * @param int|null $change Change percentage (optional)
     */
    public static function render_stat_card($label, $value, $icon, $color = '#3b82f6', $change = null) {
        ?>
        <div class="fd-stat-card">
            <div class="fd-stat-icon" style="background: <?php echo esc_attr($color); ?>20;">
                <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"
                      style="color: <?php echo esc_attr($color); ?>;"></span>
            </div>
            <div class="fd-stat-content">
                <div class="fd-stat-label"><?php echo esc_html($label); ?></div>
                <div class="fd-stat-value"><?php echo esc_html($value); ?></div>
                <?php if ($change !== null): ?>
                    <div class="fd-stat-change <?php echo $change >= 0 ? 'fd-positive' : 'fd-negative'; ?>">
                        <?php echo $change >= 0 ? '↑' : '↓'; ?> <?php echo absint( $change ); ?>%
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render notice/alert
     *
     * @param string $message Notice message
     * @param string $type Notice type (info, success, warning, error)
     * @param bool $dismissible Whether dismissible
     */
    public static function render_notice($message, $type = 'info', $dismissible = true) {
        $icons = array(
            'info' => 'info',
            'success' => 'yes-alt',
            'warning' => 'warning',
            'error' => 'dismiss'
        );

        ?>
        <div class="fd-alert fd-alert-<?php echo esc_attr($type); ?>">
            <div class="fd-alert-icon">
                <span class="dashicons dashicons-<?php echo esc_attr($icons[$type]); ?>"></span>
            </div>
            <div class="fd-alert-content">
                <div class="fd-alert-message"><?php echo wp_kses_post($message); ?></div>
            </div>
            <?php if ($dismissible): ?>
                <button type="button" class="fd-close-btn">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render tab navigation
     *
     * @param array $tabs Tabs array ['key' => 'Label']
     * @param string $active Currently active tab key
     */
    public static function render_tabs($tabs, $active = '') {
        if (empty($active)) {
            $active = key($tabs);
        }

        ?>
        <div class="fd-tabs">
            <?php foreach ($tabs as $key => $label): ?>
                <button type="button"
                        class="fd-tab <?php echo $key === $active ? 'fd-tab-active' : ''; ?>"
                        data-tab="<?php echo esc_attr($key); ?>">
                    <?php echo esc_html($label); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Start rendering a card
     *
     * @param string $title Card title (optional)
     * @param string $subtitle Card subtitle (optional)
     */
    public static function render_card_start($title = '', $subtitle = '') {
        ?>
        <div class="fd-card">
            <?php if ($title): ?>
                <div class="fd-card-header">
                    <div>
                        <h3 class="fd-card-title"><?php echo wp_kses_post($title); ?></h3>
                        <?php if ($subtitle): ?>
                            <p class="fd-card-subtitle"><?php echo esc_html($subtitle); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="fd-card-body">
        <?php
    }

    /**
     * End card rendering
     */
    public static function render_card_end() {
        ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render form field
     *
     * @param array $args Field arguments
     */
    public static function render_field($args) {
        $defaults = array(
            'type' => 'text',
            'id' => '',
            'name' => '',
            'label' => '',
            'value' => '',
            'placeholder' => '',
            'description' => '',
            'required' => false,
            'options' => array(),
            'class' => '',
        );

        $args = wp_parse_args($args, $defaults);
        extract($args);

        ?>
        <div class="fd-form-group">
            <?php if ($label): ?>
                <label for="<?php echo esc_attr($id); ?>"
                       class="fd-form-label <?php echo $required ? 'fd-required' : ''; ?>">
                    <?php echo esc_html($label); ?>
                </label>
            <?php endif; ?>

            <?php switch ($type):
                case 'textarea': ?>
                    <textarea id="<?php echo esc_attr($id); ?>"
                              name="<?php echo esc_attr($name); ?>"
                              class="fd-form-textarea <?php echo esc_attr($class); ?>"
                              placeholder="<?php echo esc_attr($placeholder); ?>"
                              <?php echo $required ? 'required' : ''; ?>><?php echo esc_textarea($value); ?></textarea>
                    <?php break;

                case 'select': ?>
                    <select id="<?php echo esc_attr($id); ?>"
                            name="<?php echo esc_attr($name); ?>"
                            class="fd-form-select <?php echo esc_attr($class); ?>"
                            <?php echo $required ? 'required' : ''; ?>>
                        <?php foreach ($options as $opt_value => $opt_label): ?>
                            <option value="<?php echo esc_attr($opt_value); ?>"
                                    <?php selected($value, $opt_value); ?>">
                                <?php echo esc_html($opt_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php break;

                case 'switch': ?>
                    <label class="fd-switch">
                        <input type="checkbox"
                               id="<?php echo esc_attr($id); ?>"
                               name="<?php echo esc_attr($name); ?>"
                               value="1"
                               <?php checked($value, 1); ?>">
                        <span class="fd-switch-slider"></span>
                    </label>
                    <?php break;

                case 'color': ?>
                    <input type="text"
                           id="<?php echo esc_attr($id); ?>"
                           name="<?php echo esc_attr($name); ?>"
                           value="<?php echo esc_attr($value); ?>"
                           class="fd-color-picker <?php echo esc_attr($class); ?>"
                           placeholder="<?php echo esc_attr($placeholder); ?>">
                    <?php break;

                default: ?>
                    <input type="<?php echo esc_attr($type); ?>"
                           id="<?php echo esc_attr($id); ?>"
                           name="<?php echo esc_attr($name); ?>"
                           value="<?php echo esc_attr($value); ?>"
                           class="fd-form-input <?php echo esc_attr($class); ?>"
                           placeholder="<?php echo esc_attr($placeholder); ?>"
                           <?php echo $required ? 'required' : ''; ?>">
                    <?php break;
            endswitch; ?>

            <?php if ($description): ?>
                <span class="fd-form-help"><?php echo wp_kses_post($description); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render badge
     *
     * @param string $text Badge text
     * @param string $type Badge type (primary, success, warning, error, info)
     * @param bool $dot Whether to show status dot
     */
    public static function render_badge($text, $type = 'primary', $dot = false) {
        ?>
        <span class="fd-badge fd-badge-<?php echo esc_attr($type); ?>">
            <?php if ($dot): ?>
                <span class="fd-badge-dot"></span>
            <?php endif; ?>
            <?php echo esc_html($text); ?>
        </span>
        <?php
    }

    /**
     * Render button
     *
     * @param string $text Button text
     * @param array $args Button arguments
     */
    public static function render_button($text, $args = array()) {
        $defaults = array(
            'type' => 'button',
            'class' => 'fd-btn-primary',
            'size' => '',
            'icon' => '',
            'href' => '',
            'onclick' => '',
            'name' => '',
            'value' => '',
        );

        $args = wp_parse_args($args, $defaults);

        $classes = 'fd-btn ' . $args['class'];
        if ($args['size']) {
            $classes .= ' fd-btn-' . $args['size'];
        }

        if ($args['href']):
            ?>
            <a href="<?php echo esc_url($args['href']); ?>"
               class="<?php echo esc_attr($classes); ?>"
               <?php if ($args['onclick']): ?>onclick="<?php echo esc_attr($args['onclick']); ?>"<?php endif; ?>">
                <?php if ($args['icon']): ?>
                    <span class="dashicons dashicons-<?php echo esc_attr($args['icon']); ?>"></span>
                <?php endif; ?>
                <?php echo esc_html($text); ?>
            </a>
        <?php else: ?>
            <button type="<?php echo esc_attr($args['type']); ?>"
                    class="<?php echo esc_attr($classes); ?>"
                    <?php if ($args['name']): ?>name="<?php echo esc_attr($args['name']); ?>"<?php endif; ?>
                    <?php if ($args['value']): ?>value="<?php echo esc_attr($args['value']); ?>"<?php endif; ?>
                    <?php if ($args['onclick']): ?>onclick="<?php echo esc_attr($args['onclick']); ?>"<?php endif; ?>>
                <?php if ($args['icon']): ?>
                    <span class="dashicons dashicons-<?php echo esc_attr($args['icon']); ?>"></span>
                <?php endif; ?>
                <?php echo esc_html($text); ?>
            </button>
        <?php endif;
    }
}

} // End if (!class_exists('FD_Admin_UI'))
