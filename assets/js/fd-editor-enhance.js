/**
 * FD Editor Enhance - Editor Enhancement JavaScript
 * Provides slash commands, floating toolbar, quick link insertion, table enhancement,
 * and paste cleanup for TinyMCE classic editor
 */
(function ($) {
    'use strict';

    var cfg = window.fdEditorEnhance || {};
    var features = cfg.features || {};
    var i18n = cfg.i18n || {};

    // ============================================================
    // Wait for TinyMCE Ready
    // ============================================================
    function waitForTinyMCE() {
        $(document).on('tinymce-editor-init', function (event, editor) {
            if (editor.id !== 'content') { return; }
            onEditorReady(editor);
        });

        if (typeof window.tinymce !== 'undefined') {
            var editor = window.tinymce.get('content');
            if (editor && editor.getBody()) {
                onEditorReady(editor);
                return;
            }
        }
    }

    function onEditorReady(editor) {
        if (features.pasteClean)   { initPasteCleanup(editor); }
        if (features.quickLink)    { initQuickLink(editor); }
        if (features.table)        { initTableEnhance(editor); }
        if (features.floatToolbar) { initFloatingToolbar(editor); }
        if (features.slashCmd)     { initSlashCommands(editor); }
    }

    // ============================================================
    // Utility: Get iframe offset
    // ============================================================
    function getIframeOffset(editor) {
        var $iframe = $(editor.iframeElement);
        var offset = $iframe.offset();
        return {
            top: offset.top,
            left: offset.left,
            scrollTop: $(editor.getDoc().documentElement).scrollTop() || 0
        };
    }

    // ============================================================
    // Utility: Toast notification
    // ============================================================
    function showToast(message, duration) {
        duration = duration || 2000;
        var $toast = $('<div class="fd-editor-toast"></div>').text(message);
        $('body').append($toast);
        setTimeout(function () { $toast.addClass('fd-editor-toast-visible'); }, 10);
        setTimeout(function () {
            $toast.removeClass('fd-editor-toast-visible');
            setTimeout(function () { $toast.remove(); }, 300);
        }, duration);
    }

    // ============================================================
    // Feature 1: Slash Commands
    // ============================================================
    function initSlashCommands(editor) {
        var $menu = null;
        var filterText = '';
        var slashActive = false;
        var activeIndex = 0;
        var filteredItems = [];

        var commands = [
            { key: 'h2',         label: i18n.slash_h2 || 'Heading 2',       icon: 'H2',  alias: 'h2 heading' },
            { key: 'h3',         label: i18n.slash_h3 || 'Heading 3',       icon: 'H3',  alias: 'h3 heading' },
            { key: 'h4',         label: i18n.slash_h4 || 'Heading 4',       icon: 'H4',  alias: 'h4 heading' },
            { key: 'ul',         label: i18n.slash_ul || 'Unordered List',   icon: '•',   alias: 'ul list' },
            { key: 'ol',         label: i18n.slash_ol || 'Ordered List',     icon: '1.',  alias: 'ol list' },
            { key: 'hr',         label: i18n.slash_hr || 'Horizontal Rule',  icon: '—',   alias: 'hr line' },
            { key: 'blockquote', label: i18n.slash_blockquote || 'Blockquote', icon: '"', alias: 'quote blockquote' },
            { key: 'code',       label: i18n.slash_code || 'Code Block',     icon: '<>',  alias: 'code pre' },
            { key: 'table',      label: i18n.slash_table || 'Table',         icon: '⊞',   alias: 'table' },
            { key: 'img',        label: i18n.slash_img || 'Insert Image',    icon: '▣',   alias: 'image img' },
        ];

        // Listen for keyboard input
        editor.on('keydown', function (e) {
            if (slashActive) {
                handleSlashKeydown(e);
                return;
            }

            // Detect "/" key
            if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                var node = editor.selection.getNode();
                var text = (node.textContent || '').trim();

                // Only trigger in empty paragraphs
                if (node.nodeName === 'P' && text === '') {
                    setTimeout(function () { activateSlash(); }, 0);
                }
            }
        });

        function activateSlash() {
            slashActive = true;
            filterText = '';
            activeIndex = 0;
            showSlashMenu();
        }

        function showSlashMenu() {
            dismissSlash();
            slashActive = true;

            // Filter commands
            filteredItems = commands.filter(function (cmd) {
                if (!filterText) { return true; }
                var q = filterText.toLowerCase();
                return cmd.key.indexOf(q) !== -1 ||
                       cmd.label.toLowerCase().indexOf(q) !== -1 ||
                       cmd.alias.toLowerCase().indexOf(q) !== -1;
            });

            if (activeIndex >= filteredItems.length) {
                activeIndex = 0;
            }

            // Build menu DOM
            $menu = $('<div class="fd-slash-menu"></div>');

            if (filteredItems.length === 0) {
                $menu.append('<div class="fd-slash-menu-empty">' + (i18n.no_match || 'No matching commands') + '</div>');
            } else {
                filteredItems.forEach(function (cmd, idx) {
                    var $item = $('<div class="fd-slash-menu-item"></div>')
                        .attr('data-key', cmd.key);

                    if (idx === activeIndex) {
                        $item.addClass('fd-slash-active');
                    }

                    $item.append('<span class="fd-slash-menu-icon">' + cmd.icon + '</span>');
                    $item.append('<span class="fd-slash-menu-label">' + cmd.label + '</span>');
                    $item.append('<span class="fd-slash-menu-key">/' + cmd.key + '</span>');

                    $item.on('mousedown', function (e) {
                        e.preventDefault();
                        executeCommand(cmd.key);
                    });

                    $item.on('mouseenter', function () {
                        $menu.find('.fd-slash-active').removeClass('fd-slash-active');
                        $item.addClass('fd-slash-active');
                        activeIndex = idx;
                    });

                    $menu.append($item);
                });
            }

            // Position menu
            var iframeInfo = getIframeOffset(editor);
            var rng = editor.selection.getRng();
            var rect = rng.getBoundingClientRect();

            var top = iframeInfo.top + rect.bottom + 4;
            var left = iframeInfo.left + rect.left;

            // Ensure it stays within viewport
            var viewportH = $(window).height();
            var menuH = Math.min(filteredItems.length * 44 + 10, 320);
            if (top + menuH > viewportH) {
                top = iframeInfo.top + rect.top - menuH - 4;
            }

            $menu.css({ top: top, left: Math.max(10, left) });
            $('body').append($menu);
        }

        function handleSlashKeydown(e) {
            var key = e.key;

            if (key === 'ArrowDown') {
                e.preventDefault();
                if (filteredItems.length > 0) {
                    activeIndex = (activeIndex + 1) % filteredItems.length;
                    updateActiveItem();
                }
                return;
            }

            if (key === 'ArrowUp') {
                e.preventDefault();
                if (filteredItems.length > 0) {
                    activeIndex = (activeIndex - 1 + filteredItems.length) % filteredItems.length;
                    updateActiveItem();
                }
                return;
            }

            if (key === 'Enter') {
                e.preventDefault();
                if (filteredItems.length > 0 && filteredItems[activeIndex]) {
                    executeCommand(filteredItems[activeIndex].key);
                }
                return;
            }

            if (key === 'Escape') {
                e.preventDefault();
                dismissSlash();
                return;
            }

            if (key === 'Backspace') {
                if (filterText.length > 0) {
                    filterText = filterText.slice(0, -1);
                    // Let TinyMCE handle backspace then re-render menu
                    setTimeout(function () { showSlashMenu(); }, 0);
                } else {
                    // Delete "/" itself, close menu
                    dismissSlash();
                }
                return;
            }

            // Space key selects current item
            if (key === ' ' && filteredItems.length > 0) {
                e.preventDefault();
                executeCommand(filteredItems[activeIndex].key);
                return;
            }

            // Printable character: append to filter text
            if (key.length === 1 && !e.ctrlKey && !e.metaKey) {
                filterText += key;
                setTimeout(function () { showSlashMenu(); }, 0);
            }
        }

        function updateActiveItem() {
            if (!$menu) { return; }
            $menu.find('.fd-slash-active').removeClass('fd-slash-active');
            $menu.find('.fd-slash-menu-item').eq(activeIndex).addClass('fd-slash-active');

            // Ensure visible
            var $active = $menu.find('.fd-slash-active');
            if ($active.length) {
                var menuEl = $menu[0];
                var itemEl = $active[0];
                if (itemEl.offsetTop + itemEl.offsetHeight > menuEl.scrollTop + menuEl.clientHeight) {
                    menuEl.scrollTop = itemEl.offsetTop + itemEl.offsetHeight - menuEl.clientHeight;
                } else if (itemEl.offsetTop < menuEl.scrollTop) {
                    menuEl.scrollTop = itemEl.offsetTop;
                }
            }
        }

        function executeCommand(key) {
            // First clear "/" and filter text
            var node = editor.selection.getNode();
            if (node && node.nodeName === 'P') {
                node.innerHTML = '';
            }

            switch (key) {
                case 'h2':
                case 'h3':
                case 'h4':
                    editor.execCommand('FormatBlock', false, key);
                    break;

                case 'ul':
                    editor.execCommand('InsertUnorderedList');
                    break;

                case 'ol':
                    editor.execCommand('InsertOrderedList');
                    break;

                case 'hr':
                    editor.execCommand('InsertHorizontalRule');
                    // Ensure empty paragraph after hr for continued editing
                    var hrNode = editor.selection.getNode();
                    if (hrNode && hrNode.nodeName === 'HR') {
                        var nextP = hrNode.nextElementSibling;
                        if (!nextP || nextP.nodeName !== 'P') {
                            var p = editor.dom.create('p', {}, '<br>');
                            hrNode.parentNode.insertBefore(p, hrNode.nextSibling);
                        }
                    }
                    break;

                case 'blockquote':
                    editor.execCommand('FormatBlock', false, 'blockquote');
                    break;

                case 'code':
                    editor.insertContent('<pre><code>' + '\n' + '</code></pre><p><br></p>');
                    // Move cursor into code element
                    var codeEl = editor.getBody().querySelector('pre:last-of-type code');
                    if (codeEl) {
                        editor.selection.setCursorLocation(codeEl, 0);
                    }
                    break;

                case 'table':
                    var col1 = i18n.col_1 || 'Column 1';
                    var col2 = i18n.col_2 || 'Column 2';
                    var col3 = i18n.col_3 || 'Column 3';
                    var tableHtml = '<table class="fd-editor-table">' +
                        '<thead><tr><th>' + col1 + '</th><th>' + col2 + '</th><th>' + col3 + '</th></tr></thead>' +
                        '<tbody><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>' +
                        '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></tbody>' +
                        '</table><p><br></p>';
                    editor.insertContent(tableHtml);
                    break;

                case 'img':
                    openMediaUploader(editor);
                    break;
            }

            dismissSlash();
            editor.focus();
        }

        function openMediaUploader(editor) {
            if (typeof wp === 'undefined' || !wp.media) { return; }

            var frame = wp.media({
                title: i18n.slash_img || 'Insert Image',
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                var alt = attachment.alt || attachment.title || '';
                editor.insertContent('<img src="' + attachment.url + '" alt="' + tinymce.DOM.encode(alt) + '" />');
            });

            frame.open();
        }

        function dismissSlash() {
            slashActive = false;
            filterText = '';
            activeIndex = 0;
            filteredItems = [];
            if ($menu) { $menu.remove(); $menu = null; }
        }

        // Close when clicking outside menu
        $(document).on('mousedown', function (e) {
            if (slashActive && $menu && !$(e.target).closest('.fd-slash-menu').length) {
                dismissSlash();
            }
        });

        // Also close on click inside editor iframe
        editor.on('click', function () {
            if (slashActive) { dismissSlash(); }
        });
    }

    // ============================================================
    // Feature 2: Floating Toolbar
    // ============================================================
    function initFloatingToolbar(editor) {
        var $toolbar = null;
        var hideTimer = null;
        var isToolbarAction = false;
        var headingDropdownVisible = false;

        function createToolbar() {
            $toolbar = $('<div class="fd-float-toolbar"></div>');

            var buttons = [
                { cmd: 'Bold',          html: '<strong>B</strong>',       title: i18n.bold || 'Bold' },
                { cmd: 'Italic',        html: '<span class="fd-toolbar-italic">I</span>', title: i18n.italic || 'Italic' },
                { cmd: 'Strikethrough', html: '<span class="fd-toolbar-strike">S</span>', title: i18n.strikethrough || 'Strikethrough' },
                { type: 'separator' },
                { cmd: 'link',          html: '&#128279;',               title: i18n.link || 'Insert Link', custom: true },
                { cmd: 'code',          html: '&lt;&gt;',                title: i18n.code || 'Inline Code', custom: true },
                { type: 'separator' },
                { cmd: 'heading',       html: 'H',                      title: i18n.heading || 'Heading', dropdown: true },
                { type: 'separator' },
                { cmd: 'RemoveFormat',  html: '&#8709;',                title: i18n.clear_format || 'Clear Formatting' },
            ];

            buttons.forEach(function (btn) {
                if (btn.type === 'separator') {
                    $toolbar.append('<div class="fd-float-toolbar-separator"></div>');
                    return;
                }

                var $btn = $('<button type="button" class="fd-float-toolbar-btn"></button>')
                    .html(btn.html)
                    .attr('title', btn.title)
                    .attr('data-cmd', btn.cmd);

                if (btn.dropdown) {
                    // Heading dropdown
                    var $wrap = $('<div style="position:relative;display:inline-flex;"></div>');
                    var $dropdown = $('<div class="fd-float-toolbar-heading-dropdown"></div>');

                    ['H2', 'H3', 'H4'].forEach(function (tag) {
                        var $hBtn = $('<button type="button" class="fd-float-toolbar-btn"></button>')
                            .text(tag)
                            .attr('title', tag)
                            .on('mousedown', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                isToolbarAction = true;
                                editor.execCommand('FormatBlock', false, tag.toLowerCase());
                                $dropdown.removeClass('fd-dropdown-visible');
                                headingDropdownVisible = false;
                                updateToolbarState();
                                setTimeout(function () { isToolbarAction = false; }, 100);
                            });
                        $dropdown.append($hBtn);
                    });

                    $btn.on('mousedown', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        isToolbarAction = true;
                        headingDropdownVisible = !headingDropdownVisible;
                        if (headingDropdownVisible) {
                            $dropdown.addClass('fd-dropdown-visible');
                        } else {
                            $dropdown.removeClass('fd-dropdown-visible');
                        }
                        setTimeout(function () { isToolbarAction = false; }, 100);
                    });

                    $wrap.append($btn).append($dropdown);
                    $toolbar.append($wrap);
                } else {
                    $btn.on('mousedown', function (e) {
                        e.preventDefault();
                        isToolbarAction = true;

                        if (btn.custom) {
                            if (btn.cmd === 'link') {
                                insertLink(editor);
                            } else if (btn.cmd === 'code') {
                                toggleInlineCode(editor);
                            }
                        } else {
                            editor.execCommand(btn.cmd);
                        }

                        updateToolbarState();
                        setTimeout(function () { isToolbarAction = false; }, 100);
                    });

                    $toolbar.append($btn);
                }
            });

            $('body').append($toolbar);
        }

        function insertLink(editor) {
            var sel = editor.selection.getContent({ format: 'text' });
            var url = prompt(i18n.link_prompt || 'Enter the link URL:', 'https://');
            if (url && url !== 'https://') {
                if (sel && sel.trim()) {
                    editor.selection.setContent('<a href="' + tinymce.DOM.encode(url) + '">' + tinymce.DOM.encode(sel) + '</a>');
                } else {
                    editor.insertContent('<a href="' + tinymce.DOM.encode(url) + '">' + tinymce.DOM.encode(url) + '</a>');
                }
            }
        }

        function toggleInlineCode(editor) {
            var node = editor.selection.getNode();
            if (node.nodeName === 'CODE') {
                // Remove code wrapper
                editor.execCommand('RemoveFormat');
            } else {
                var sel = editor.selection.getContent({ format: 'text' });
                if (sel) {
                    editor.selection.setContent('<code>' + tinymce.DOM.encode(sel) + '</code>');
                }
            }
        }

        function updateToolbarState() {
            if (!$toolbar) { return; }
            $toolbar.find('.fd-float-toolbar-btn').each(function () {
                var cmd = $(this).attr('data-cmd');
                if (!cmd) { return; }
                if (cmd === 'Bold' || cmd === 'Italic' || cmd === 'Strikethrough') {
                    if (editor.queryCommandState(cmd)) {
                        $(this).addClass('fd-toolbar-active');
                    } else {
                        $(this).removeClass('fd-toolbar-active');
                    }
                }
                if (cmd === 'code') {
                    var node = editor.selection.getNode();
                    if (node.nodeName === 'CODE') {
                        $(this).addClass('fd-toolbar-active');
                    } else {
                        $(this).removeClass('fd-toolbar-active');
                    }
                }
            });
        }

        // Show toolbar when text is selected
        editor.on('mouseup keyup', function () {
            if (isToolbarAction) { return; }
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () {
                var sel = editor.selection.getContent({ format: 'text' });
                if (sel && sel.trim().length > 0) {
                    positionAndShow();
                } else {
                    hideToolbar();
                }
            }, 200);
        });

        function positionAndShow() {
            if (!$toolbar) { createToolbar(); }

            updateToolbarState();

            var rng = editor.selection.getRng();
            var rect = rng.getBoundingClientRect();

            // Selection may have no valid dimensions
            if (!rect || (rect.width === 0 && rect.height === 0)) {
                hideToolbar();
                return;
            }

            var iframeInfo = getIframeOffset(editor);
            var toolbarH = $toolbar.outerHeight() || 36;
            var toolbarW = $toolbar.outerWidth() || 300;

            var top = iframeInfo.top + rect.top - toolbarH - 10;
            var left = iframeInfo.left + rect.left + (rect.width / 2) - (toolbarW / 2);

            // If no room above, place below
            $toolbar.removeClass('fd-toolbar-below');
            if (top < 5) {
                top = iframeInfo.top + rect.bottom + 10;
                $toolbar.addClass('fd-toolbar-below');
            }

            // Left/right boundaries
            if (left < 10) { left = 10; }
            var maxLeft = $(window).width() - toolbarW - 10;
            if (left > maxLeft) { left = maxLeft; }

            $toolbar.css({ top: top, left: left }).addClass('fd-float-toolbar-visible');
        }

        function hideToolbar() {
            if ($toolbar) {
                $toolbar.removeClass('fd-float-toolbar-visible');
                // Close heading dropdown
                headingDropdownVisible = false;
                $toolbar.find('.fd-float-toolbar-heading-dropdown').removeClass('fd-dropdown-visible');
            }
        }

        // Hide when editor loses focus or clicking outside
        $(document).on('mousedown', function (e) {
            if (isToolbarAction) { return; }
            if ($toolbar && !$(e.target).closest('.fd-float-toolbar').length) {
                hideToolbar();
            }
        });

        editor.on('keydown', function () {
            if (!isToolbarAction) {
                hideToolbar();
            }
        });
    }

    // ============================================================
    // Feature 3: Quick Link Insertion
    // ============================================================
    function initQuickLink(editor) {
        var urlRegex = /^https?:\/\/[^\s<>"{}|\\^`\[\]]+$/i;

        // Bind to iframe's native paste event (before TinyMCE's PastePreProcess)
        var iframeDoc = editor.getDoc();
        if (!iframeDoc) { return; }

        iframeDoc.addEventListener('paste', function (e) {
            var clipboardData = e.clipboardData || (typeof window !== 'undefined' && window.clipboardData);
            if (!clipboardData) { return; }

            var pastedText = '';
            try {
                pastedText = clipboardData.getData('text/plain') || '';
            } catch (err) {
                return;
            }
            pastedText = pastedText.trim();

            if (!urlRegex.test(pastedText)) { return; }

            // Only intercept plain URL paste
            e.preventDefault();
            e.stopPropagation();

            var selectedText = editor.selection.getContent({ format: 'text' });

            if (selectedText && selectedText.trim().length > 0) {
                editor.selection.setContent(
                    '<a href="' + tinymce.DOM.encode(pastedText) + '">' +
                    tinymce.DOM.encode(selectedText) + '</a>'
                );
                showToast(i18n.link_wrapped || 'Selected text converted to link');
            } else {
                editor.insertContent(
                    '<a href="' + tinymce.DOM.encode(pastedText) + '">' +
                    tinymce.DOM.encode(pastedText) + '</a>&nbsp;'
                );
                showToast(i18n.link_created || 'Link created automatically');
            }
        }, true); // Use capture phase to ensure priority over other handlers
    }

    // ============================================================
    // Feature 4: Table Enhancement
    // ============================================================
    function initTableEnhance(editor) {
        var $contextMenu = null;

        // Inject table styles into TinyMCE iframe
        var iframeDoc = editor.getDoc();
        if (iframeDoc) {
            var style = iframeDoc.createElement('style');
            style.textContent = [
                'table.fd-editor-table { border-collapse: collapse; width: 100%; margin: 1em 0; }',
                'table.fd-editor-table th, table.fd-editor-table td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; min-width: 60px; }',
                'table.fd-editor-table th { background: #f5f5f5; font-weight: 600; }',
                'table.fd-editor-table tr:hover td { background: #fafafa; }',
            ].join('\n');
            iframeDoc.head.appendChild(style);
        }

        // Auto-add class to new tables
        editor.on('SetContent', function () {
            var tables = editor.getBody().querySelectorAll('table:not(.fd-editor-table)');
            for (var i = 0; i < tables.length; i++) {
                tables[i].classList.add('fd-editor-table');
            }
        });

        // Context menu
        editor.on('contextmenu', function (e) {
            var cell = findParent(e.target, ['TD', 'TH'], editor);
            if (!cell) { return; }

            e.preventDefault();

            var table = findParent(cell, ['TABLE'], editor);
            if (!table) { return; }

            showTableContextMenu(e, cell, table);
        });

        function showTableContextMenu(e, cell, table) {
            dismissContextMenu();

            var items = [
                { label: i18n.add_row_above || 'Insert Row Above', action: function () { addRow(cell, 'above'); } },
                { label: i18n.add_row_below || 'Insert Row Below', action: function () { addRow(cell, 'below'); } },
                { type: 'separator' },
                { label: i18n.add_col_left || 'Insert Column Left',  action: function () { addCol(cell, table, 'left'); } },
                { label: i18n.add_col_right || 'Insert Column Right', action: function () { addCol(cell, table, 'right'); } },
                { type: 'separator' },
                { label: i18n.delete_row || 'Delete Row',   action: function () { deleteRow(cell, table); }, danger: true },
                { label: i18n.delete_col || 'Delete Column',   action: function () { deleteCol(cell, table); }, danger: true },
                { label: i18n.delete_table || 'Delete Table', action: function () { deleteTable(table); },   danger: true },
            ];

            $contextMenu = $('<div class="fd-table-context-menu"></div>');
            items.forEach(function (item) {
                if (item.type === 'separator') {
                    $contextMenu.append('<div class="fd-table-ctx-separator"></div>');
                    return;
                }
                var $item = $('<div class="fd-table-ctx-item"></div>').text(item.label);
                if (item.danger) { $item.addClass('fd-table-ctx-danger'); }
                $item.on('mousedown', function (ev) {
                    ev.preventDefault();
                    item.action();
                    dismissContextMenu();
                });
                $contextMenu.append($item);
            });

            // Position
            var iframeInfo = getIframeOffset(editor);
            var top = iframeInfo.top + e.clientY;
            var left = iframeInfo.left + e.clientX;

            // Ensure it stays within viewport
            var viewportW = $(window).width();
            var viewportH = $(window).height();
            var menuW = 170;
            var menuH = items.length * 36;
            if (left + menuW > viewportW - 10) { left = viewportW - menuW - 10; }
            if (top + menuH > viewportH - 10) { top = viewportH - menuH - 10; }

            $contextMenu.css({ top: top, left: left });
            $('body').append($contextMenu);
        }

        function addRow(cell, position) {
            var row = cell.parentNode;
            var colCount = row.cells.length;
            var iframeDocument = editor.getDoc();
            var newRow = iframeDocument.createElement('tr');
            for (var i = 0; i < colCount; i++) {
                var td = iframeDocument.createElement('td');
                td.innerHTML = '&nbsp;';
                newRow.appendChild(td);
            }
            if (position === 'above') {
                row.parentNode.insertBefore(newRow, row);
            } else {
                row.parentNode.insertBefore(newRow, row.nextSibling);
            }
            editor.undoManager.add();
        }

        function addCol(cell, table, position) {
            var cellIndex = cell.cellIndex;
            var insertIndex = position === 'left' ? cellIndex : cellIndex + 1;
            var rows = table.rows;
            var iframeDocument = editor.getDoc();
            for (var i = 0; i < rows.length; i++) {
                var isHeader = (i === 0 && rows[i].parentNode.nodeName === 'THEAD');
                var newCell = iframeDocument.createElement(isHeader ? 'th' : 'td');
                newCell.innerHTML = '&nbsp;';
                if (insertIndex >= rows[i].cells.length) {
                    rows[i].appendChild(newCell);
                } else {
                    rows[i].insertBefore(newCell, rows[i].cells[insertIndex]);
                }
            }
            editor.undoManager.add();
        }

        function deleteRow(cell, table) {
            var row = cell.parentNode;
            row.parentNode.removeChild(row);
            // If no rows left, delete entire table
            if (table.rows.length === 0) {
                deleteTable(table);
                return;
            }
            editor.undoManager.add();
        }

        function deleteCol(cell, table) {
            var cellIndex = cell.cellIndex;
            var rows = table.rows;
            // If only one column, delete entire table
            if (rows[0] && rows[0].cells.length <= 1) {
                deleteTable(table);
                return;
            }
            for (var i = rows.length - 1; i >= 0; i--) {
                if (rows[i].cells[cellIndex]) {
                    rows[i].deleteCell(cellIndex);
                }
            }
            editor.undoManager.add();
        }

        function deleteTable(table) {
            var iframeDocument = editor.getDoc();
            var p = iframeDocument.createElement('p');
            p.innerHTML = '<br>';
            table.parentNode.replaceChild(p, table);
            editor.selection.setCursorLocation(p, 0);
            editor.undoManager.add();
        }

        function dismissContextMenu() {
            if ($contextMenu) { $contextMenu.remove(); $contextMenu = null; }
        }

        // Close on outside click
        $(document).on('mousedown', function (e) {
            if ($contextMenu && !$(e.target).closest('.fd-table-context-menu').length) {
                dismissContextMenu();
            }
        });

        editor.on('click', function () {
            dismissContextMenu();
        });
    }

    // ============================================================
    // Feature 5: Paste Cleanup
    // ============================================================
    function initPasteCleanup(editor) {
        editor.on('PastePreProcess', function (e) {
            var html = e.content;
            if (!html || html.trim().length === 0) { return; }

            var original = html;

            // 1. Remove Word conditional comments
            html = html.replace(/<!--\[if[\s\S]*?endif\]-->/gi, '');
            html = html.replace(/<!--[\s\S]*?-->/g, '');

            // 2. Remove Office XML tags
            html = html.replace(/<o:p[\s\S]*?<\/o:p>/gi, '');
            html = html.replace(/<w:[\s\S]*?<\/w:\w+>/gi, '');
            html = html.replace(/<m:[\s\S]*?<\/m:\w+>/gi, '');
            html = html.replace(/<\/?(xml|st\d)[^>]*>/gi, '');

            // 3. Remove Mso-related classes
            html = html.replace(/\s*class\s*=\s*"[^"]*Mso[^"]*"/gi, '');
            html = html.replace(/\s*class\s*=\s*'[^']*Mso[^']*'/gi, '');

            // 4. Remove mso-* style attributes
            html = html.replace(/\s*mso-[^:;]+:[^;"]+;?/gi, '');

            // 5. Remove decorative CSS properties (preserve semantic format like font-weight, text-decoration)
            html = html.replace(/\s*font-family\s*:[^;"]+;?/gi, '');
            html = html.replace(/\s*font-size\s*:[^;"]+;?/gi, '');
            html = html.replace(/\s*color\s*:\s*(?!inherit)[^;"]+;?/gi, '');
            html = html.replace(/\s*background-color\s*:[^;"]+;?/gi, '');
            html = html.replace(/\s*background\s*:[^;"]+;?/gi, '');
            html = html.replace(/\s*line-height\s*:[^;"]+;?/gi, '');
            html = html.replace(/\s*text-indent\s*:[^;"]+;?/gi, '');

            // 6. Remove empty style attributes
            html = html.replace(/\s*style\s*=\s*"\s*"/gi, '');
            html = html.replace(/\s*style\s*=\s*'\s*'/gi, '');

            // 7. Remove empty <span> tags
            html = html.replace(/<span[^>]*>\s*<\/span>/gi, '');

            // 8. Remove empty paragraphs (preserve at least one line break)
            html = html.replace(/<p[^>]*>\s*(&nbsp;)?\s*<\/p>/gi, '');

            // 9. Remove <font> tags but keep content
            html = html.replace(/<\/?font[^>]*>/gi, '');

            // 10. Clean up excessive blank lines
            html = html.replace(/\n{3,}/g, '\n\n');

            e.content = html;

            if (html !== original) {
                showToast(i18n.paste_cleaned || 'Pasted content cleaned');
            }
        });
    }

    // ============================================================
    // Utility: Find parent element
    // ============================================================
    function findParent(el, tagNames, editor) {
        while (el && el !== editor.getBody()) {
            if (tagNames.indexOf(el.nodeName) !== -1) { return el; }
            el = el.parentNode;
        }
        return null;
    }

    // ============================================================
    // Start
    // ============================================================
    $(document).ready(function () {
        waitForTinyMCE();
    });

}(jQuery));
