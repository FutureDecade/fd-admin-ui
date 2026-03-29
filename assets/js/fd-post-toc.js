/**
 * FD Post TOC - Post Editor Table of Contents JavaScript
 * Parses headings in TinyMCE classic editor, generates table of contents with click-to-scroll support
 * Only enabled on desktop (width > 960px), not initialized on small screens
 */
(function ($) {
    'use strict';

    // Configuration from PHP
    var cfg = window.fdPostTOC || {};
    var headingLevels = cfg.headingLevels || ['h2', 'h3', 'h4'];
    var defaultOpen   = cfg.defaultOpen !== false;
    var tocWidth      = cfg.tocWidth    || 220;
    var menuLeft      = cfg.menuLeft    || 53;
    var i18n          = cfg.i18n        || {};

    // Desktop minimum width threshold (matches CSS media query breakpoint)
    var DESKTOP_MIN_WIDTH = 961;

    // Whether initialization is complete
    var initialized = false;

    // Storage key (per-post independent expand/collapse state)
    var storageKey = 'fd_toc_open_' + (window.location.search || '');

    // DOM node references
    var $wrap, $panel, $handle, $collapseBtn, $list, $empty;

    // Currently active heading's toc-id
    var activeId = null;

    // ============================================================
    // Desktop Detection
    // ============================================================
    function isDesktop() {
        return window.innerWidth >= DESKTOP_MIN_WIDTH;
    }

    // ============================================================
    // Initialize
    // ============================================================
    function init() {
        // Don't initialize on small screens, wait for resize
        if (!isDesktop()) {
            return;
        }

        $wrap        = $('#fd-post-toc-wrap');
        $panel       = $('#fd-post-toc-panel');
        $handle      = $('#fd-post-toc-handle');
        $collapseBtn = $('#fd-post-toc-collapse');
        $list        = $('#fd-post-toc-list');
        $empty       = $('#fd-post-toc-empty');

        if (!$wrap.length) {
            return;
        }

        initialized = true;

        // Inject width and left offset variables into :root for body margin rules
        var root = document.documentElement;
        root.style.setProperty('--fd-toc-width', tocWidth + 'px');
        root.style.setProperty('--fd-toc-menu-left', menuLeft + 'px');
        // --fd-toc-adminbar-h is set by PHP wp_add_inline_style, no JS handling needed

        // Bind expand/collapse
        $collapseBtn.on('click', function () { setOpen(false); });
        $handle.on('click', function () { setOpen(true); });

        // Restore previous state (localStorage first, then config default)
        var saved = localStorage.getItem(storageKey);
        var isOpen = (saved !== null) ? (saved === '1') : defaultOpen;
        setOpen(isOpen, true /* skip animation */);

        // Wait for TinyMCE ready
        waitForTinyMCE();
    }

    // ============================================================
    // Expand / Collapse
    // ============================================================
    function setOpen(open, instant) {
        var body = document.body;

        if (open) {
            body.classList.remove('fd-toc-collapsed');
            $handle.attr('title', i18n.collapse || 'Collapse TOC');
            $handle.attr('aria-label', i18n.collapse || 'Collapse TOC');
        } else {
            body.classList.add('fd-toc-collapsed');
            $handle.attr('title', i18n.expand || 'Expand TOC');
            $handle.attr('aria-label', i18n.expand || 'Expand TOC');
        }

        localStorage.setItem(storageKey, open ? '1' : '0');
    }

    // ============================================================
    // Wait for TinyMCE Ready (supports deferred init)
    // ============================================================
    function waitForTinyMCE() {
        // TinyMCE broadcasts init complete via jQuery event
        $(document).on('tinymce-editor-init', function (event, editor) {
            if (editor.id !== 'content') {
                return;
            }
            onEditorReady(editor);
        });

        // If TinyMCE already initialized (after page refresh), handle directly
        if (typeof window.tinymce !== 'undefined') {
            var editor = window.tinymce.get('content');
            if (editor && editor.getBody()) {
                onEditorReady(editor);
                return;
            }
        }
    }

    // ============================================================
    // Operations After Editor is Ready
    // ============================================================
    function onEditorReady(editor) {
        // Initial build
        buildTOC(editor);

        // Debounced update on content changes
        var updateTimer;
        function debouncedUpdate() {
            clearTimeout(updateTimer);
            updateTimer = setTimeout(function () {
                buildTOC(editor);
            }, 600);
        }

        editor.on('input',      debouncedUpdate);
        editor.on('keyup',      debouncedUpdate);
        editor.on('SetContent', debouncedUpdate);
        editor.on('ExecCommand', debouncedUpdate);

        // Highlight current heading on cursor movement
        editor.on('NodeChange', function () {
            highlightCurrent(editor);
        });

        // Re-parse when switching from HTML source mode back
        editor.on('show', function () {
            setTimeout(function () { buildTOC(editor); }, 200);
        });
    }

    // ============================================================
    // Build TOC
    // ============================================================
    function buildTOC(editor) {
        var body = editor.getBody();
        if (!body) { return; }

        // Get all headings matching configured levels
        var selector = headingLevels.join(',');
        if (!selector) { return; }

        var headings = body.querySelectorAll(selector);

        $list.empty();

        if (!headings.length) {
            $empty.show();
            return;
        }

        $empty.hide();

        // Determine top level (for relative indentation)
        var minLevel = 7;
        headings.forEach(function (h) {
            var lvl = parseInt(h.tagName.charAt(1), 10);
            if (lvl < minLevel) { minLevel = lvl; }
        });

        // Generate entries one by one, inject unique IDs
        headings.forEach(function (h, index) {
            var tocId = 'fd-toc-h-' + index;
            h.setAttribute('data-fd-toc-id', tocId);

            var level   = parseInt(h.tagName.charAt(1), 10);
            var text    = h.textContent || h.innerText || '';
            text = text.trim();
            if (!text) { text = i18n.emptyHeading || '(Empty heading)'; }

            var $li = $('<li></li>').attr('data-level', level);
            var $a  = $('<a href="#" role="button"></a>')
                .attr('data-toc-id', tocId)
                .attr('title', text)
                .text(text);

            $a.on('click', function (e) {
                e.preventDefault();
                scrollToHeading(editor, tocId);
            });

            $li.append($a);
            $list.append($li);
        });

        // Highlight current position
        highlightCurrent(editor);
    }

    // ============================================================
    // Scroll to Heading in Editor
    // ============================================================
    function scrollToHeading(editor, tocId) {
        var body = editor.getBody();
        if (!body) { return; }

        var target = body.querySelector('[data-fd-toc-id="' + tocId + '"]');
        if (!target) { return; }

        // Scroll editor iframe to target position
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Place cursor inside heading
        try {
            editor.selection.select(target, true);
            editor.selection.collapse(true);
        } catch (e) {
            // Some TinyMCE versions may throw on select, ignore
        }

        // Highlight this entry
        setActiveItem(tocId);

        // Give editor focus
        editor.focus();
    }

    // ============================================================
    // Highlight TOC Entry Based on Current Cursor Position
    // ============================================================
    function highlightCurrent(editor) {
        var node = editor.selection.getNode();
        if (!node) { return; }

        // Find nearest heading ancestor upward
        var heading = null;
        var el = node;
        while (el && el !== editor.getBody()) {
            if (/^H[1-6]$/i.test(el.tagName)) {
                heading = el;
                break;
            }
            el = el.parentNode;
        }

        var tocId = heading ? heading.getAttribute('data-fd-toc-id') : null;
        setActiveItem(tocId);
    }

    function setActiveItem(tocId) {
        if (tocId === activeId) { return; }
        activeId = tocId;

        $list.find('a').removeClass('fd-toc-active');
        if (tocId) {
            $list.find('a[data-toc-id="' + tocId + '"]').addClass('fd-toc-active');
        }
    }

    // ============================================================
    // Start
    // ============================================================
    $(document).ready(function () {
        init();

        // Listen for window resize: initialize when switching from small screen to desktop
        var resizeTimer;
        $(window).on('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (isDesktop() && !initialized) {
                    init();
                }
            }, 250);
        });
    });

}(jQuery));
