/**
 * FD Post Meta Box - Post Meta Box Enhancement JavaScript
 * Handles featured image upload, excerpt character count, and author input
 */
(function ($) {
    'use strict';

    var mediaFrame;

    // Localized strings
    var i18n = (window.fdPostMetaBox && fdPostMetaBox.i18n) ? fdPostMetaBox.i18n : {};

    $(document).ready(function () {
        initFeaturedImage();
        initExcerptCounter();
        initAuthorInput();
        initEditorPlaceholder();
        initBottomBar();
    });

    // ============================================================
    // Author Input Handling
    // ============================================================
    function initAuthorInput() {
        var $input = $('#fd-author-input');
        var $suggestions = $('.fd-author-suggestions');
        var $wpAuthorId = $('#fd-wp-author-id');
        var $customAuthorName = $('#fd-custom-author-name');
        var debounceTimer = null;
        var selectedAuthorId = null;

        if ($input.length === 0) {
            return;
        }

        // Initialize: if custom author exists, clear WordPress author ID
        if ($customAuthorName.val().trim()) {
            selectedAuthorId = null;
        } else {
            selectedAuthorId = parseInt($wpAuthorId.val()) || null;
        }

        // Listen for input
        $input.on('input', function () {
            var value = $(this).val().trim();

            // Clear previous timer
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }

            // If input is empty, hide suggestions
            if (!value) {
                $suggestions.removeClass('show').empty();
                // Clear both hidden fields
                $wpAuthorId.val('');
                $customAuthorName.val('');
                selectedAuthorId = null;
                return;
            }

            // Debounce: search after 300ms
            debounceTimer = setTimeout(function () {
                fetchAuthorSuggestions(value);
            }, 300);
        });

        // Keyboard navigation
        $input.on('keydown', function (e) {
            var $items = $suggestions.find('.fd-author-suggestion-item');
            var $active = $items.filter('.active');

            // Arrow Down
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if ($active.length === 0) {
                    $items.first().addClass('active');
                } else {
                    var $next = $active.next('.fd-author-suggestion-item');
                    if ($next.length > 0) {
                        $active.removeClass('active');
                        $next.addClass('active');
                    }
                }
            }
            // Arrow Up
            else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if ($active.length > 0) {
                    var $prev = $active.prev('.fd-author-suggestion-item');
                    if ($prev.length > 0) {
                        $active.removeClass('active');
                        $prev.addClass('active');
                    }
                }
            }
            // Enter key
            else if (e.key === 'Enter') {
                e.preventDefault();
                if ($active.length > 0) {
                    selectAuthor($active);
                } else {
                    // No suggestion selected, treat as custom author
                    setCustomAuthor($input.val().trim());
                    $suggestions.removeClass('show').empty();
                }
            }
            // ESC key
            else if (e.key === 'Escape') {
                $suggestions.removeClass('show').empty();
            }
        });

        // Click suggestion item
        $(document).on('click', '.fd-author-suggestion-item', function (e) {
            e.preventDefault();
            selectAuthor($(this));
        });

        // Handle blur
        $input.on('blur', function () {
            // Delay hiding suggestions to allow clicking suggestion items
            setTimeout(function () {
                var value = $input.val().trim();

                // If input has value but no author selected, treat as custom author
                if (value && selectedAuthorId === null && !$customAuthorName.val()) {
                    setCustomAuthor(value);
                }

                $suggestions.removeClass('show').empty();
            }, 200);
        });

        // Close suggestions when clicking elsewhere
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.fd-author-input-container').length) {
                $suggestions.removeClass('show').empty();
            }
        });

        // Fetch author suggestions
        function fetchAuthorSuggestions(search) {
            $.ajax({
                url: fdPostMetaBox.ajaxUrl,
                type: 'GET',
                data: {
                    action: 'fd_get_author_suggestions',
                    search: search,
                    nonce: fdPostMetaBox.nonce
                },
                success: function (response) {
                    if (response.success && response.data.length > 0) {
                        renderSuggestions(response.data);
                    } else {
                        $suggestions.removeClass('show').empty();
                    }
                },
                error: function () {
                    $suggestions.removeClass('show').empty();
                }
            });
        }

        // Render suggestion list
        function renderSuggestions(suggestions) {
            $suggestions.empty();

            suggestions.forEach(function (item, index) {
                var $item = $('<div class="fd-author-suggestion-item">')
                    .attr('data-id', item.id)
                    .attr('data-name', item.display_name)
                    .attr('data-index', index)
                    .html('<span class="fd-author-suggestion-name">' + item.display_name + '</span>' +
                          '<span class="fd-author-suggestion-login">@' + item.user_login + '</span>');

                $suggestions.append($item);
            });

            $suggestions.addClass('show');

            // Select first item by default
            $suggestions.find('.fd-author-suggestion-item').first().addClass('active');
        }

        // Select author
        function selectAuthor($item) {
            var authorId = parseInt($item.data('id'));
            var authorName = $item.data('name');

            // Set as WordPress author
            $input.val(authorName);
            $wpAuthorId.val(authorId);
            $customAuthorName.val(''); // Clear custom author
            selectedAuthorId = authorId;

            $suggestions.removeClass('show').empty();
        }

        // Set custom author
        function setCustomAuthor(name) {
            $wpAuthorId.val(''); // Clear WordPress author ID
            $customAuthorName.val(name);
            selectedAuthorId = null;
        }
    }

    // ============================================================
    // Editor Placeholder
    // ============================================================
    function initEditorPlaceholder() {
        var placeholderText = i18n.editorPlaceholder || 'Start writing...';
        var initialized = false;

        function addPlaceholder(editor) {
            if (initialized) {
                return;
            }
            initialized = true;


            // Inject placeholder styles into editor iframe
            // Place placeholder on first <p>'s ::before to align with cursor position
            var iframeDoc = editor.getDoc();
            if (iframeDoc) {
                var style = iframeDoc.createElement('style');
                style.textContent = [
                    'body[data-mce-placeholder]:not(.mce-visualblocks) > p:first-child::before {',
                    '    content: var(--fd-placeholder) !important;',
                    '    color: #8c8f94 !important;',
                    '    pointer-events: none !important;',
                    '    font-size: 14px !important;',
                    '    line-height: 1.5 !important;',
                    '}'
                ].join('\n');
                iframeDoc.head.appendChild(style);
            }

            // Show/hide placeholder
            function showPlaceholder(body) {
                body.setAttribute('data-mce-placeholder', '1');
                body.style.setProperty('--fd-placeholder', '"' + placeholderText + '"');
            }

            function hidePlaceholder(body) {
                body.removeAttribute('data-mce-placeholder');
                body.style.removeProperty('--fd-placeholder');
            }

            // Check and show placeholder
            function checkPlaceholder() {
                var body = editor.getBody();
                if (!body) { return; }

                var content = editor.getContent({ format: 'text' }).trim();
                var hasContent = content.length > 0;
                var hasFocus = editor.hasFocus();

                if (!hasContent && !hasFocus) {
                    showPlaceholder(body);
                } else {
                    hidePlaceholder(body);
                }
            }

            // Initial check (delayed to ensure editor is fully loaded)
            setTimeout(function() {
                checkPlaceholder();
            }, 100);

            // Listen for content changes
            editor.on('input change setcontent keyup', function () {
                checkPlaceholder();
            });

            // Listen for focus events - hide when focused
            editor.on('focus click mousedown', function () {
                var body = editor.getBody();
                if (body) { hidePlaceholder(body); }
            });

            // Recheck when losing focus
            editor.on('blur', function () {
                setTimeout(function() {
                    checkPlaceholder();
                }, 50);
            });
        }

        // Method 1: Listen for tinymce-editor-init event
        $(document).on('tinymce-editor-init', function (event, editor) {
            if (editor.id === 'content') {
                addPlaceholder(editor);
            }
        });

        // Method 2: Poll to check if TinyMCE is initialized
        var checkInterval = setInterval(function() {
            if (typeof tinymce !== 'undefined') {
                var editor = tinymce.get('content');
                if (editor && editor.getBody()) {
                    clearInterval(checkInterval);
                    addPlaceholder(editor);
                }
            }
        }, 100);

        // Stop polling after 10 seconds
        setTimeout(function() {
            clearInterval(checkInterval);
        }, 10000);
    }

    // ============================================================
    // Author Field Toggle (deprecated, kept for compatibility)
    // ============================================================
    function initAuthorToggle() {
        // This function has been replaced by initAuthorInput
    }

    // ============================================================
    // Featured Image Handling
    // ============================================================
    function initFeaturedImage() {
        var $preview = $('#fd-featured-image-preview');
        var $setButton = $('#fd-set-featured-image');
        var $removeButton = $('#fd-remove-featured-image');
        var $hiddenInput = $('#fd-featured-image-id');

        // Click preview area or set button to open media library
        $preview.on('click', function (e) {
            // If clicking remove button, don't open media library
            if ($(e.target).closest('.fd-remove-featured-image').length) {
                return;
            }
            e.preventDefault();
            openMediaFrame();
        });

        // Remove cover image
        $(document).on('click', '#fd-remove-featured-image', function (e) {
            e.preventDefault();
            e.stopPropagation();
            removeFeaturedImage();
        });

        // Open media library
        function openMediaFrame() {
            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: i18n.selectCoverImage || 'Select Cover Image',
                button: { text: i18n.setAsCover || 'Set as Cover' },
                multiple: false,
                library: { type: 'image' }
            });

            mediaFrame.on('select', function () {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                setFeaturedImage(attachment);
            });

            mediaFrame.open();
        }

        // Set featured image
        function setFeaturedImage(attachment) {
            var imageUrl = attachment.sizes && attachment.sizes.medium
                ? attachment.sizes.medium.url
                : attachment.url;

            // Update preview
            $preview.html(
                '<img id="fd-featured-image-img" src="' + imageUrl + '" alt="">' +
                '<div class="fd-featured-image-overlay">' +
                '<button type="button" class="fd-image-action" id="fd-set-featured-image" title="' + (i18n.changeCover || 'Change Cover') + '">' +
                '<span class="dashicons dashicons-edit"></span>' +
                '</button>' +
                '<button type="button" class="fd-image-action fd-remove-featured-image" id="fd-remove-featured-image" title="' + (i18n.removeCover || 'Remove Cover') + '">' +
                '<span class="dashicons dashicons-no-alt"></span>' +
                '</button>' +
                '</div>'
            );

            // Update hidden field
            $hiddenInput.val(attachment.id);
        }

        // Remove featured image
        function removeFeaturedImage() {
            // Restore placeholder
            $preview.html(
                '<div class="fd-featured-image-placeholder" id="fd-set-featured-image">' +
                '<span class="dashicons dashicons-format-image"></span>' +
                '<span>' + (i18n.coverImage || 'Cover Image') + '</span>' +
                '</div>'
            );

            // Clear hidden field
            $hiddenInput.val('');
        }
    }

    // ============================================================
    // Excerpt Character Count
    // ============================================================
    function initExcerptCounter() {
        var $textarea = $('#fd-post-excerpt');
        var $counter = $('.fd-excerpt-count');
        var maxLength = 120;

        if ($textarea.length === 0) {
            return;
        }

        // Update character count
        function updateCounter() {
            var length = $textarea.val().length;
            $counter.text(length);

            // Add different styles based on character count
            $counter.removeClass('fd-warning fd-error');
            if (length >= maxLength) {
                $counter.addClass('fd-error');
            } else if (length >= maxLength * 0.9) {
                $counter.addClass('fd-warning');
            }
        }

        // Listen for input
        $textarea.on('input', updateCounter);

        // Initialize
        updateCounter();
    }

    // ============================================================
    // Bottom Fixed Status Bar
    // ============================================================
    function initBottomBar() {
        var $bar = $('#fd-editor-bottom-bar');
        if ($bar.length === 0) { return; }

        var $wordCount = $('#fd-word-count');
        var $savedTime = $('#fd-bar-saved-time');
        var $savedAt   = $('#fd-bar-saved-at');

        // Position: match #post-body-content width
        function updateBarPosition() {
            var $content = $('#post-body-content');
            if ($content.length === 0) { return; }
            var rect = $content[0].getBoundingClientRect();
            $bar.css({
                left:  Math.round(rect.left) + 'px',
                width: Math.round(rect.width) + 'px',
                right: 'auto'
            });
        }

        updateBarPosition();
        setTimeout(updateBarPosition, 200); // Wait for layout to complete
        $(window).on('resize.fdBar', function () { updateBarPosition(); });
        // Reposition after sidebar collapse/expand animation
        $('#collapse-button').on('click.fdBar', function () {
            setTimeout(updateBarPosition, 300);
        });

        // Word count (consistent with TinyMCE status bar)
        function updateWordCount(editor) {
            var count;
            if (editor.plugins && editor.plugins.wordcount) {
                // Use TinyMCE wordcount plugin
                count = editor.plugins.wordcount.getCount();
            } else {
                // Fallback: character count (excluding spaces), consistent with Word
                var text = editor.getContent({ format: 'text' });

                // Remove all whitespace (spaces, newlines, tabs, etc.)
                text = text.replace(/\s+/g, '');

                // Count remaining characters
                count = text.length;
            }
            $wordCount.text(count);
        }

        // Save time
        function showSavedTime(timeStr) {
            if (!timeStr) { return; }
            $savedAt.text(timeStr);
            $savedTime.addClass('fd-bar-visible');
        }

        // Initial value (post_modified time from PHP)
        if (fdPostMetaBox.savedTime) {
            showSavedTime(fdPostMetaBox.savedTime);
        }

        // Update time after auto-save
        $(document).on('heartbeat-tick', function (e, data) {
            if (data['wp-autosave'] && data['wp-autosave'].success) {
                var now = new Date();
                var h = String(now.getHours()).padStart(2, '0');
                var m = String(now.getMinutes()).padStart(2, '0');
                showSavedTime(h + ':' + m);
            }
        });

        // Bind editor events
        function bindEditor(editor) {
            updateWordCount(editor);
            editor.on('input change setcontent keyup', function () {
                updateWordCount(editor);
            });
        }

        // Wait for TinyMCE ready
        $(document).on('tinymce-editor-init', function (event, editor) {
            if (editor.id === 'content') { bindEditor(editor); }
        });
        if (typeof tinymce !== 'undefined') {
            var ed = tinymce.get('content');
            if (ed && ed.getBody()) { bindEditor(ed); }
        }

        // Buttons
        $('#fd-bar-save-draft').on('click', function () {
            $('#save-post').trigger('click');
        });

        $('#fd-bar-preview').on('click', function () {
            $('#post-preview').trigger('click');
        });

        $('#fd-bar-publish').on('click', function () {
            $('#publish').trigger('click');
        });
    }

}(jQuery));
