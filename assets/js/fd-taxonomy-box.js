/**
 * FD Taxonomy Box - Taxonomy Meta Box Interaction
 */

(function($) {
    'use strict';

    // Localized strings
    var i18n = (window.fdTaxonomyBox && fdTaxonomyBox.i18n) ? fdTaxonomyBox.i18n : {};

    const FDTaxonomyBox = {
        // Debounce timer
        debounceTimer: null,

        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;

            // Category item click toggle
            $(document).on('click', '.fd-category-item', function(e) {
                e.preventDefault();
                self.toggleCategory($(this));
            });

            // Tag remove
            $(document).on('click', '.fd-tag-remove', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.removeTag($(this));
            });

            // Show suggestions on tag input
            $(document).on('input', '.fd-tag-input', function(e) {
                self.handleTagInput($(this));
            });

            // Tag input Enter or comma to add
            $(document).on('keydown', '.fd-tag-input', function(e) {
                // Enter key or comma
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    self.addTagFromInput($(this));
                }
                // Arrow down: select suggestion
                else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    self.selectNextSuggestion($(this));
                }
                // Arrow up: select suggestion
                else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    self.selectPrevSuggestion($(this));
                }
                // ESC: close suggestions
                else if (e.key === 'Escape') {
                    self.hideSuggestions($(this));
                }
            });

            // Click suggestion item
            $(document).on('click', '.fd-tag-suggestion-item', function(e) {
                e.preventDefault();
                const $input = $(this).closest('.fd-tag-input-wrapper').find('.fd-tag-input');
                const tagName = $(this).data('name');
                self.addSingleTag($input, tagName);
            });

            // Update on tag input blur
            $(document).on('blur', '.fd-tag-input', function() {
                const self = FDTaxonomyBox;
                // Delay hiding suggestions to allow clicking suggestion items
                setTimeout(function() {
                    self.hideSuggestions($(this));
                }.bind(this), 200);
            });

            // Close suggestions when clicking elsewhere
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.fd-tag-input-wrapper').length) {
                    $('.fd-tag-suggestions').removeClass('show');
                }
            });
        },

        /**
         * Handle tag input (show suggestions)
         */
        handleTagInput: function($input) {
            const self = this;
            const value = $input.val().trim();
            const $suggestions = $input.siblings('.fd-tag-suggestions');
            const taxonomy = $input.data('taxonomy');

            // Clear previous timer
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }

            // If input is empty, hide suggestions
            if (!value) {
                $suggestions.removeClass('show').empty();
                return;
            }

            // Get the last tag from current input (comma separated)
            const tags = value.split(',');
            const currentTag = tags[tags.length - 1].trim();

            // If current tag is empty, hide suggestions
            if (!currentTag) {
                $suggestions.removeClass('show').empty();
                return;
            }

            // Debounce: search after 300ms
            this.debounceTimer = setTimeout(function() {
                self.fetchSuggestions(taxonomy, currentTag, $suggestions);
            }, 300);
        },

        /**
         * Fetch tag suggestions
         */
        fetchSuggestions: function(taxonomy, search, $suggestions) {
            $.ajax({
                url: fdTaxonomyBox.ajaxUrl,
                type: 'GET',
                data: {
                    action: 'fd_get_tag_suggestions',
                    taxonomy: taxonomy,
                    search: search,
                    nonce: fdTaxonomyBox.nonce
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        FDTaxonomyBox.renderSuggestions(response.data, $suggestions);
                    } else {
                        $suggestions.removeClass('show').empty();
                    }
                },
                error: function() {
                    $suggestions.removeClass('show').empty();
                }
            });
        },

        /**
         * Render suggestion list
         */
        renderSuggestions: function(suggestions, $suggestions) {
            $suggestions.empty();

            suggestions.forEach(function(item, index) {
                const $item = $('<div class="fd-tag-suggestion-item">')
                    .attr('data-name', item.name)
                    .attr('data-index', index)
                    .html('<span class="fd-suggestion-name">' + item.name + '</span>' +
                          '<span class="fd-suggestion-count">(' + item.count + ')</span>');

                $suggestions.append($item);
            });

            $suggestions.addClass('show');

            // Select first item by default
            $suggestions.find('.fd-tag-suggestion-item').first().addClass('active');
        },

        /**
         * Hide suggestions
         */
        hideSuggestions: function($input) {
            $input.siblings('.fd-tag-suggestions').removeClass('show').empty();
        },

        /**
         * Select next suggestion
         */
        selectNextSuggestion: function($input) {
            const $suggestions = $input.siblings('.fd-tag-suggestions');
            const $items = $suggestions.find('.fd-tag-suggestion-item');
            const $active = $items.filter('.active');

            if ($active.length === 0) {
                $items.first().addClass('active');
            } else {
                const $next = $active.next('.fd-tag-suggestion-item');
                if ($next.length > 0) {
                    $active.removeClass('active');
                    $next.addClass('active');
                }
            }
        },

        /**
         * Select previous suggestion
         */
        selectPrevSuggestion: function($input) {
            const $suggestions = $input.siblings('.fd-tag-suggestions');
            const $items = $suggestions.find('.fd-tag-suggestion-item');
            const $active = $items.filter('.active');

            if ($active.length > 0) {
                const $prev = $active.prev('.fd-tag-suggestion-item');
                if ($prev.length > 0) {
                    $active.removeClass('active');
                    $prev.addClass('active');
                }
            }
        },

        /**
         * Toggle category selection
         */
        toggleCategory: function($item) {
            const $checkbox = $item.find('input[type="checkbox"]');
            const isSelected = $item.hasClass('selected');

            if (isSelected) {
                $item.removeClass('selected');
                $checkbox.prop('checked', false);
            } else {
                $item.addClass('selected');
                $checkbox.prop('checked', true);
            }

            // Update count
            this.updateCount($item.closest('.fd-taxonomy-section'));
        },

        /**
         * Remove tag
         */
        removeTag: function($removeBtn) {
            const $tagItem = $removeBtn.closest('.fd-tag-item');
            const $section = $tagItem.closest('.fd-taxonomy-section');

            // Remove tag element
            $tagItem.fadeOut(200, function() {
                $(this).remove();

                // Update input value
                FDTaxonomyBox.syncInputFromTags($section);

                // Update count
                FDTaxonomyBox.updateCount($section);
            });
        },

        /**
         * Add tag from input
         */
        addTagFromInput: function($input) {
            const value = $input.val().trim();
            if (!value) {
                return;
            }

            const $section = $input.closest('.fd-taxonomy-section');
            const $suggestions = $input.siblings('.fd-tag-suggestions');

            // Check if there's a selected suggestion
            const $activeSuggestion = $suggestions.find('.fd-tag-suggestion-item.active');
            if ($activeSuggestion.length > 0) {
                const tagName = $activeSuggestion.data('name');
                this.addSingleTag($input, tagName);
                return;
            }

            // Split multiple tags (comma separated)
            const tags = value.split(',').map(tag => tag.trim()).filter(tag => tag);

            // Add the last tag
            if (tags.length > 0) {
                const lastTag = tags[tags.length - 1];
                this.addSingleTag($input, lastTag);
            }
        },

        /**
         * Add single tag
         */
        addSingleTag: function($input, tagName) {
            const $section = $input.closest('.fd-taxonomy-section');
            const $tagList = $section.find('.fd-tag-list');

            // Check if already exists
            const exists = $section.find('.fd-tag-item').filter(function() {
                return $(this).find('.fd-tag-name').text() === tagName;
            }).length > 0;

            if (!exists) {
                // If tag list doesn't exist, create it
                if ($tagList.length === 0) {
                    $input.closest('.fd-tag-input-wrapper').before('<div class="fd-tag-list"></div>');
                }

                this.addTagElement($section, tagName);
            }

            // Clear input
            $input.val('');

            // Hide suggestions
            this.hideSuggestions($input);

            // Update count
            this.updateCount($section);
        },

        /**
         * Add tag element
         */
        addTagElement: function($section, tagName) {
            const $tagList = $section.find('.fd-tag-list');

            const $tagItem = $('<div class="fd-tag-item">')
                .append($('<span class="fd-tag-name">').text(tagName))
                .append($('<span class="fd-tag-remove" title="' + (i18n.remove || 'Remove') + '">×</span>'));

            $tagList.append($tagItem);

            // Update input value
            this.syncInputFromTags($section);
        },

        /**
         * Sync from tag list to input
         */
        syncInputFromTags: function($section) {
            const $input = $section.find('.fd-tag-input');
            const tags = [];

            $section.find('.fd-tag-item').each(function() {
                tags.push($(this).find('.fd-tag-name').text());
            });

            $input.val(tags.join(','));
        },

        /**
         * Update selected count
         */
        updateCount: function($section) {
            const $count = $section.find('.fd-taxonomy-count');
            let count = 0;

            // Count selected items
            if ($section.find('.fd-category-list').length > 0) {
                // Hierarchical taxonomy
                count = $section.find('.fd-category-item.selected').length;
            } else if ($section.find('.fd-tag-list').length > 0) {
                // Flat taxonomy
                count = $section.find('.fd-tag-item').length;
            }

            var selectedText = i18n.selected || 'Selected';
            $count.text(selectedText + ' ' + count);
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            $('.fd-taxonomy-box').addClass('loading');
        },

        /**
         * Hide loading state
         */
        hideLoading: function() {
            $('.fd-taxonomy-box').removeClass('loading');
        }
    };

    // Initialize
    $(document).ready(function() {
        if ($('.fd-taxonomy-box').length > 0) {
            FDTaxonomyBox.init();
        }
    });

    // Expose to global scope (for debugging)
    window.FDTaxonomyBox = FDTaxonomyBox;

})(jQuery);
