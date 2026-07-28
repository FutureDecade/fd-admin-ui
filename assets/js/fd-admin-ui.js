/**
 * Lingcoo Admin UI Framework - JavaScript
 * Unified admin interface interactions
 */

(function($) {
    'use strict';

    // Localized strings
    var i18n = (window.fdAdminUI && fdAdminUI.i18n) ? fdAdminUI.i18n : {};

    // ============================================
    // Tab Switching
    // ============================================
    function initTabs() {
        // Prefer URL parameter 'tab', fallback to hash
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');

        if (tabParam) {
            const $tab = $(`.fd-tab[data-tab="${tabParam}"]`);
            if ($tab.length) {
                switchTab($tab, false);
            }
        } else if (window.location.hash) {
            const hash = window.location.hash;
            const $tab = $(`.fd-tab[href="${hash}"], .fd-tab[data-tab="${hash.slice(1)}"]`);
            if ($tab.length) {
                switchTab($tab, false);
            }
        }

        // Click to switch
        $('.fd-tab').on('click', function(e) {
            e.preventDefault();
            switchTab($(this), true);
        });

        function switchTab($tab, updateUrl) {
            const target = $tab.data('tab') || $tab.attr('href').slice(1);

            // Update tab active state
            $tab.addClass('fd-tab-active')
                .siblings('.fd-tab')
                .removeClass('fd-tab-active');

            // Update content area active state
            $(`.fd-tab-content[data-tab="${target}"]`)
                .addClass('fd-tab-active')
                .siblings('.fd-tab-content')
                .removeClass('fd-tab-active');

            // Update URL parameters (use tab instead of hash)
            if (updateUrl && history.pushState) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', target);
                url.hash = '';
                history.pushState(null, null, url.toString());
            }

            // Update _wp_http_referer in all forms to ensure returning to current tab after submit
            updateFormReferers(target);
        }

        // Update _wp_http_referer field in forms
        function updateFormReferers(tab) {
            $('input[name="_wp_http_referer"]').each(function() {
                const $input = $(this);
                let referer = $input.val();

                // Parse URL
                try {
                    const url = new URL(referer, window.location.origin);
                    url.searchParams.set('tab', tab);
                    url.hash = '';
                    $input.val(url.pathname + url.search);
                } catch (e) {
                    // If parsing fails, build a new referer
                    const baseUrl = referer.split('?')[0].split('#')[0];
                    const params = new URLSearchParams(referer.split('?')[1] || '');
                    params.set('tab', tab);
                    $input.val(baseUrl + '?' + params.toString());
                }
            });
        }

        // Also update referer once on page load
        const currentTab = urlParams.get('tab') || $('.fd-tab-active').data('tab');
        if (currentTab) {
            updateFormReferers(currentTab);
        }
    }

    // ============================================
    // Switch Components
    // ============================================
    function initSwitches() {
        $('.fd-switch input').on('change', function() {
            const $switch = $(this);
            const $target = $($switch.data('target'));

            if ($switch.is(':checked')) {
                $target.show();
                $switch.trigger('fd-switch:on');
            } else {
                $target.hide();
                $switch.trigger('fd-switch:off');
            }
        });

        // Initialize state
        $('.fd-switch input').each(function() {
            const $switch = $(this);
            const $target = $($switch.data('target'));

            if ($target.length) {
                if ($switch.is(':checked')) {
                    $target.show();
                } else {
                    $target.hide();
                }
            }
        });
    }

    // ============================================
    // Confirm Dialog
    // ============================================
    function initConfirm() {
        $('[data-confirm]').on('click', function(e) {
            const message = $(this).data('confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    }

    // ============================================
    // Copy to Clipboard
    // ============================================
    function initCopyToClipboard() {
        $('.fd-copy-btn').on('click', function() {
            const $btn = $(this);
            const text = $btn.data('copy') || $btn.prev('input, textarea').val();

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess($btn);
                });
            } else {
                // Fallback
                const $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                showCopySuccess($btn);
            }
        });

        function showCopySuccess($btn) {
            const originalText = $btn.html();
            $btn.html('<span class="dashicons dashicons-yes"></span> ' + (i18n.copied || 'Copied'));
            setTimeout(function() {
                $btn.html(originalText);
            }, 2000);
        }
    }

    // ============================================
    // Collapsible Panels
    // ============================================
    function initCollapse() {
        $('.fd-collapse-trigger').on('click', function() {
            const $trigger = $(this);
            const $target = $($trigger.data('target'));

            $trigger.toggleClass('fd-collapsed');
            $target.slideToggle(300);
        });
    }

    // ============================================
    // Search Filter
    // ============================================
    function initSearch() {
        $('.fd-search-filter').on('input', function() {
            const query = $(this).val().toLowerCase();
            const target = $(this).data('target');

            $(target).each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(query) > -1);
            });
        });
    }

    // ============================================
    // Form Auto-save
    // ============================================
    function initAutoSave() {
        let saveTimeout;

        $('.fd-auto-save input, .fd-auto-save select, .fd-auto-save textarea').on('change input', function() {
            clearTimeout(saveTimeout);

            const $form = $(this).closest('.fd-auto-save');
            const $indicator = $form.find('.fd-save-indicator');

            $indicator.text(i18n.unsaved || 'Unsaved...').css('color', '#f59e0b');

            saveTimeout = setTimeout(function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: $form.serialize(),
                    success: function() {
                        $indicator.text(i18n.saved || 'Saved').css('color', '#10b981');
                    },
                    error: function() {
                        $indicator.text(i18n.saveFailed || 'Save failed').css('color', '#ef4444');
                    }
                });
            }, 1000);
        });
    }

    // ============================================
    // Tooltips
    // ============================================
    function initTooltips() {
        $('[data-tooltip]').each(function() {
            const $el = $(this);
            const text = $el.data('tooltip');

            $el.attr('title', text);
        });
    }

    // ============================================
    // Notification Close
    // ============================================
    function initNotifications() {
        $('.fd-alert .fd-close-btn, .notice .notice-dismiss').on('click', function() {
            $(this).closest('.fd-alert, .notice').fadeOut(300, function() {
                $(this).remove();
            });
        });
    }

    // ============================================
    // Color Picker Enhancement
    // ============================================
    function initColorPickers() {
        if (typeof jQuery.fn.wpColorPicker !== 'undefined') {
            $('.fd-color-picker').wpColorPicker();
        }
    }

    // ============================================
    // File Upload Preview
    // ============================================
    function initFilePreview() {
        $('.fd-file-input').on('change', function() {
            const file = this.files[0];
            const $preview = $($(this).data('preview'));

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ============================================
    // Sortable Lists
    // ============================================
    function initSortable() {
        if (typeof jQuery.fn.sortable !== 'undefined') {
            $('.fd-sortable').sortable({
                handle: '.fd-sort-handle',
                axis: 'y',
                update: function(event, ui) {
                    $(this).trigger('fd-sortable:update', [ui]);
                }
            });
        }
    }

    // ============================================
    // AJAX Form Submit
    // ============================================
    function initAjaxForms() {
        $('.fd-ajax-form').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $button = $form.find('button[type="submit"]');
            const buttonText = $button.html();

            $button.prop('disabled', true).html(i18n.processing || 'Processing...');

            $.ajax({
                url: $form.attr('action') || ajaxurl,
                type: $form.attr('method') || 'POST',
                data: $form.serialize(),
                success: function(response) {
                    $form.trigger('fd-ajax-form:success', [response]);
                    showNotification('success', response.message || i18n.success || 'Operation successful');
                },
                error: function(xhr) {
                    $form.trigger('fd-ajax-form:error', [xhr]);
                    showNotification('error', xhr.responseJSON?.message || i18n.error || 'Operation failed');
                },
                complete: function() {
                    $button.prop('disabled', false).html(buttonText);
                }
            });
        });
    }

    // ============================================
    // Notification
    // ============================================
    function showNotification(type, message) {
        const icons = {
            success: 'dashicons-yes-alt',
            error: 'dashicons-dismiss',
            warning: 'dashicons-warning',
            info: 'dashicons-info'
        };

        const $notification = $(`
            <div class="fd-alert fd-alert-${type}" style="position: fixed; top: 32px; right: 20px; z-index: 999999; min-width: 300px; box-shadow: var(--fd-shadow-xl); animation: slideInRight 0.3s ease;">
                <div class="fd-alert-icon">
                    <span class="dashicons ${icons[type]}"></span>
                </div>
                <div class="fd-alert-content">
                    <div class="fd-alert-message">${message}</div>
                </div>
                <button type="button" class="fd-close-btn" style="margin-left: auto; border: none; background: none; cursor: pointer; color: currentColor; opacity: 0.7;">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
        `);

        $('body').append($notification);

        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);

        $notification.find('.fd-close-btn').on('click', function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        });
    }

    // ============================================
    // Menu Style Toggle
    // ============================================
    function initMenuStyleToggle() {
        // Check menu style related fields
        const $styleRadios = $('input[name="fd_admin_ui_options[menu_style]"]');

        if ($styleRadios.length === 0) {
            return;
        }

        function toggleModernSection() {
            const isModern = $('input[name="fd_admin_ui_options[menu_style]"]:checked').val() === 'modern';

            // Toggle modern style exclusive accordion show/hide
            const $modernSection = $('.fd-menu-modern-only');

            if (isModern) {
                $modernSection.slideDown(300);
            } else {
                $modernSection.slideUp(300);
            }
        }

        // Initialize state
        toggleModernSection();

        // Listen for changes
        $styleRadios.on('change', toggleModernSection);
    }

    // ============================================
    // Initialize All Features
    // ============================================
    $(document).ready(function() {
        initTabs();
        initSwitches();
        initConfirm();
        initCopyToClipboard();
        initCollapse();
        initSearch();
        initAutoSave();
        initTooltips();
        initNotifications();
        initColorPickers();
        initFilePreview();
        initSortable();
        initAjaxForms();
        initMenuStyleToggle();
    });

    // Expose to global scope
    window.FDAdminUI = {
        showNotification: showNotification
    };

})(jQuery);
