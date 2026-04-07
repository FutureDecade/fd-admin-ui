<?php
/**
 * FD Auto Fold Menu
 * Auto-fold sidebar menu on post editor pages
 */

defined('ABSPATH') || exit;

class FD_Auto_Fold_Menu {

    /**
     * Initialize
     */
    public static function init() {
        // Add auto-fold script on post editor pages
        add_action('admin_footer', array(__CLASS__, 'add_auto_fold_script'));
    }

    /**
     * Add auto-fold script
     * Only runs on post editor pages, without affecting manual user operations
     */
    public static function add_auto_fold_script() {
        $screen = get_current_screen();

        // Only run on post editor pages (post.php and post-new.php)
        if (!$screen || $screen->base !== 'post') {
            return;
        }

        // Check if auto-fold or TOC feature is enabled
        $options = FD_Admin_UI_Settings::get_options();
        $auto_fold_enabled = isset($options['menu_auto_fold_on_post_editor']) && $options['menu_auto_fold_on_post_editor'];
        $toc_enabled = isset($options['content_toc_enable']) && $options['content_toc_enable'];

        // If neither feature is enabled, do nothing
        if (!$auto_fold_enabled && !$toc_enabled) {
            return;
        }

        ?>
        <script>
        (function() {
            'use strict';

            var body = document.body;
            var isFolded = body.classList.contains('folded');
            var tocEnabled = <?php echo $toc_enabled ? 'true' : 'false'; ?>;
            var autoFoldEnabled = <?php echo $auto_fold_enabled ? 'true' : 'false'; ?>;

            // If TOC is enabled, always fold menu on page load
            if (tocEnabled) {
                if (!isFolded) {
                    body.classList.add('folded');

                    // Trigger WordPress collapse event
                    if (typeof jQuery !== 'undefined') {
                        jQuery(document).trigger('wp-collapse-menu', {state: 'folded'});
                    }

                }

                // Listen for manual expand/collapse operations
                var collapseButton = document.getElementById('collapse-button');
                if (collapseButton) {
                    collapseButton.addEventListener('click', function() {
                        setTimeout(function() {
                            var isNowFolded = body.classList.contains('folded');
                        }, 100);
                    });
                }
            }
            // If only auto-fold is enabled (without TOC), use sessionStorage mechanism
            else if (autoFoldEnabled) {
                var sessionKey = 'fd_menu_folded_' + window.location.pathname;
                var hasAutoFolded = sessionStorage.getItem(sessionKey);

                // If already auto-folded, do not run again
                if (hasAutoFolded) {
                    return;
                }

                // If menu is not folded, auto-fold it
                if (!isFolded) {
                    body.classList.add('folded');
                    sessionStorage.setItem(sessionKey, '1');

                    if (typeof jQuery !== 'undefined') {
                        jQuery(document).trigger('wp-collapse-menu', {state: 'folded'});
                    }

                }

                // Listen for manual expand operations
                var collapseButton = document.getElementById('collapse-button');
                if (collapseButton) {
                    collapseButton.addEventListener('click', function() {
                        setTimeout(function() {
                            var isNowFolded = body.classList.contains('folded');

                            if (!isNowFolded) {
                                sessionStorage.removeItem(sessionKey);
                            }
                        }, 100);
                    });
                }
            }
        })();
        </script>
        <?php
    }
}

// Initialize
FD_Auto_Fold_Menu::init();
