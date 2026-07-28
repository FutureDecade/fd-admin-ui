/**
 * Lingcoo Admin UI - JavaScript Utility Functions
 * Provides optional utility functions without modifying WordPress native behavior
 * Version: 2.0.0
 */

(function ($) {
    'use strict';

    // Localized strings
    var i18n = (window.fdAdminUI && fdAdminUI.i18n) ? fdAdminUI.i18n : {};

    /**
     * Main initialization function
     */
    function init() {
        // Add duplicate submission prevention for fd-* component forms only
        initFdFormProtection();

    }

    /**
     * FD form duplicate submission prevention
     * Only applies to forms with .fd-form, .fd-settings-form class
     */
    function initFdFormProtection() {
        $('.fd-form, .fd-settings-form').on('submit', function () {
            var $submitBtn = $(this).find('button[type="submit"], input[type="submit"]');
            if ($submitBtn.data('submitting')) {
                return false;
            }
            $submitBtn.data('submitting', true);

            // Disable button and show loading state
            $submitBtn.prop('disabled', true);
            var originalText = $submitBtn.text();
            $submitBtn.html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span>' + originalText);

            // Restore button state after 10 seconds (prevent getting stuck)
            setTimeout(function () {
                $submitBtn.prop('disabled', false).text(originalText);
                $submitBtn.data('submitting', false);
            }, 10000);
        });
    }

    /**
     * Utility: Show loading overlay
     * Requires .fd-loading-overlay CSS styles
     */
    window.fdShowLoading = function (message) {
        message = message || i18n.loading || 'Loading...';
        var $overlay = $('<div class="fd-loading-overlay"><div class="fd-loading-spinner"></div><div class="fd-loading-message">' + message + '</div></div>');
        $('body').append($overlay);
        setTimeout(function () {
            $overlay.addClass('fd-active');
        }, 10);
    };

    /**
     * Utility: Hide loading overlay
     */
    window.fdHideLoading = function () {
        $('.fd-loading-overlay').removeClass('fd-active');
        setTimeout(function () {
            $('.fd-loading-overlay').remove();
        }, 300);
    };

    /**
     * Utility: Show toast notification
     * Requires .fd-toast CSS styles
     */
    window.fdShowToast = function (message, type) {
        type = type || 'info';
        var icons = {
            'info': 'info',
            'success': 'yes-alt',
            'warning': 'warning',
            'error': 'dismiss'
        };

        var $toast = $('<div class="fd-toast fd-toast-' + type + '"><span class="dashicons dashicons-' + icons[type] + '"></span><span>' + message + '</span></div>');

        $('body').append($toast);

        setTimeout(function () {
            $toast.addClass('fd-active');
        }, 10);

        setTimeout(function () {
            $toast.removeClass('fd-active');
            setTimeout(function () {
                $toast.remove();
            }, 300);
        }, 3000);
    };

    /**
     * Utility: Confirm dialog
     */
    window.fdConfirm = function (message, callback) {
        if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
            return true;
        }
        return false;
    };

    // Initialize when page is ready
    $(document).ready(init);

})(jQuery);
