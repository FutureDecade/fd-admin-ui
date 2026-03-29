/**
 * FD Global Search
 * Global Search JavaScript
 */

(function($) {
    'use strict';

    const FDSearch = {
        modal: null,
        input: null,
        results: null,
        backdrop: null,
        closeBtn: null,
        currentIndex: -1,
        searchTimeout: null,
        currentRequest: null,
        searchHistory: [],
        maxHistory: 10,

        /**
         * Initialize
         */
        init: function() {
            this.modal = $('#fd-search-modal');
            this.input = $('#fd-search-input');
            this.results = $('#fd-search-results');
            this.backdrop = this.modal.find('.fd-search-backdrop');
            this.closeBtn = this.modal.find('.fd-search-close');

            // Load search history
            this.loadHistory();

            // Bind events
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;

            // Keyboard shortcut trigger
            $(document).on('keydown', function(e) {
                const key = fdSearchConfig.shortcutKey || 'k';

                // Cmd/Ctrl + K or P
                if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === key) {
                    e.preventDefault();
                    self.open();
                }

                // Esc to close
                if (e.key === 'Escape' && self.modal.is(':visible')) {
                    e.preventDefault();
                    self.close();
                }
            });

            // Input search
            this.input.on('input', function() {
                self.handleInput();
            });

            // Keyboard navigation
            this.input.on('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    self.navigateDown();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    self.navigateUp();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    self.selectCurrent();
                }
            });

            // Click result item
            this.results.on('click', '.fd-search-item', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                if (url) {
                    self.saveToHistory(self.input.val());
                    window.location.href = url;
                }
            });

            // Click backdrop to close
            this.backdrop.on('click', function() {
                self.close();
            });

            // Click close button
            this.closeBtn.on('click', function() {
                self.close();
            });

            // Mouse hover highlight
            this.results.on('mouseenter', '.fd-search-item', function() {
                self.results.find('.fd-search-item').removeClass('active');
                $(this).addClass('active');
                self.currentIndex = $(this).index('.fd-search-item');
            });
        },

        /**
         * Open search modal
         */
        open: function() {
            this.modal.css('display', 'flex').hide().fadeIn(150);
            this.input.focus();

            // If history enabled and input is empty, show history
            if (fdSearchConfig.showHistory && this.input.val() === '' && this.searchHistory.length > 0) {
                this.showHistory();
            } else if (this.input.val() === '') {
                // Show search hint
                this.showSearchHint();
            }
        },

        /**
         * Close search modal
         */
        close: function() {
            this.modal.fadeOut(150);
            this.input.val('');
            this.results.empty();
            this.currentIndex = -1;

            // Cancel unfinished requests
            if (this.currentRequest) {
                this.currentRequest.abort();
            }
        },

        /**
         * Handle input
         */
        handleInput: function() {
            const self = this;
            const keyword = this.input.val().trim();

            // Clear previous timer
            clearTimeout(this.searchTimeout);

            // If input is empty
            if (keyword === '') {
                if (fdSearchConfig.showHistory && this.searchHistory.length > 0) {
                    this.showHistory();
                } else {
                    this.showSearchHint();
                }
                return;
            }

            // Show loading state
            this.showLoading();

            // Debounce: search after 300ms
            this.searchTimeout = setTimeout(function() {
                self.search(keyword);
            }, 300);
        },

        /**
         * Execute search
         */
        search: function(keyword) {
            const self = this;

            // Cancel previous request
            if (this.currentRequest) {
                this.currentRequest.abort();
            }

            // Send AJAX request
            this.currentRequest = $.ajax({
                url: fdSearchConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fd_global_search',
                    nonce: fdSearchConfig.nonce,
                    keyword: keyword
                },
                success: function(response) {
                    if (response.success) {
                        self.renderResults(response.data);
                    } else {
                        self.showError(response.data.message || fdSearchConfig.i18n.noResults);
                    }
                },
                error: function(xhr) {
                    if (xhr.statusText !== 'abort') {
                        self.showError(fdSearchConfig.i18n.searchFailed || 'Search failed, please try again');
                    }
                },
                complete: function() {
                    self.currentRequest = null;
                }
            });
        },

        /**
         * Render search results
         */
        renderResults: function(data) {
            this.results.empty();
            this.currentIndex = -1;

            let hasResults = false;

            // Render results by category
            const categories = [
                { key: 'posts', label: fdSearchConfig.i18n.posts },
                { key: 'pages', label: fdSearchConfig.i18n.pages },
                { key: 'media', label: fdSearchConfig.i18n.media },
                { key: 'menu_items', label: fdSearchConfig.i18n.menuItems },
                { key: 'users', label: fdSearchConfig.i18n.users },
                { key: 'plugins', label: fdSearchConfig.i18n.plugins }
            ];

            categories.forEach(function(category) {
                if (data[category.key] && data[category.key].length > 0) {
                    hasResults = true;
                    this.renderCategory(category.label, data[category.key]);
                }
            }.bind(this));

            if (!hasResults) {
                this.showNoResults();
            }
        },

        /**
         * Render category
         */
        renderCategory: function(label, items) {
            const categoryHtml = $('<div class="fd-search-category"></div>');
            categoryHtml.append('<div class="fd-search-category-title">' + label + '</div>');

            items.forEach(function(item) {
                const itemHtml = $('<a href="#" class="fd-search-item" data-url="' + item.url + '"></a>');

                // Icon container
                const icon = item.icon || 'dashicons-admin-generic';
                const iconWrapper = $('<div class="fd-search-item-icon"></div>');
                iconWrapper.append('<span class="dashicons ' + icon + '"></span>');
                itemHtml.append(iconWrapper);

                // Content
                const content = $('<div class="fd-search-item-content"></div>');
                content.append('<div class="fd-search-item-title">' + this.escapeHtml(item.title) + '</div>');

                if (item.excerpt) {
                    content.append('<div class="fd-search-item-excerpt">' + this.escapeHtml(item.excerpt) + '</div>');
                }

                itemHtml.append(content);

                // Meta info
                if (item.status || item.date) {
                    const meta = $('<div class="fd-search-item-meta"></div>');
                    if (item.status) {
                        const statusEl = $('<span class="fd-search-item-status"></span>');
                        statusEl.attr('data-status', item.status);
                        statusEl.text(item.status);
                        meta.append(statusEl);
                    }
                    if (item.date) {
                        meta.append('<span class="fd-search-item-date">' + item.date + '</span>');
                    }
                    itemHtml.append(meta);
                }

                categoryHtml.append(itemHtml);
            }.bind(this));

            this.results.append(categoryHtml);
        },

        /**
         * Show search history
         */
        showHistory: function() {
            this.results.empty();

            const historyHtml = $('<div class="fd-search-category"></div>');
            historyHtml.append('<div class="fd-search-category-title">' + fdSearchConfig.i18n.recentSearches + '</div>');

            this.searchHistory.forEach(function(keyword) {
                const itemHtml = $('<div class="fd-search-history-item"></div>');
                itemHtml.append('<span class="dashicons dashicons-clock"></span>');
                itemHtml.append('<span>' + this.escapeHtml(keyword) + '</span>');

                itemHtml.on('click', function() {
                    this.input.val(keyword);
                    this.handleInput();
                }.bind(this));

                historyHtml.append(itemHtml);
            }.bind(this));

            this.results.append(historyHtml);
        },

        /**
         * Show search hint
         */
        showSearchHint: function() {
            this.results.html(
                '<div class="fd-search-hint">' +
                '<div class="fd-search-hint-icon dashicons dashicons-search"></div>' +
                '<div class="fd-search-hint-text">' + (fdSearchConfig.i18n.searchHintText || 'Type a keyword to start searching') + '</div>' +
                '<div class="fd-search-hint-subtext">' + (fdSearchConfig.i18n.searchHintSubtext || 'Search posts, pages, menus, users, and more') + '</div>' +
                '</div>'
            );
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            this.results.html('<div class="fd-search-loading">' + fdSearchConfig.i18n.searching + '</div>');
        },

        /**
         * Show no results
         */
        showNoResults: function() {
            this.results.html(
                '<div class="fd-search-no-results">' +
                fdSearchConfig.i18n.noResults +
                '</div>'
            );
        },

        /**
         * Show error
         */
        showError: function(message) {
            this.results.html(
                '<div class="fd-search-error">' +
                message +
                '</div>'
            );
        },

        /**
         * Navigate down
         */
        navigateDown: function() {
            const items = this.results.find('.fd-search-item');
            if (items.length === 0) return;

            this.currentIndex++;
            if (this.currentIndex >= items.length) {
                this.currentIndex = 0;
            }

            this.highlightItem(items, this.currentIndex);
        },

        /**
         * Navigate up
         */
        navigateUp: function() {
            const items = this.results.find('.fd-search-item');
            if (items.length === 0) return;

            this.currentIndex--;
            if (this.currentIndex < 0) {
                this.currentIndex = items.length - 1;
            }

            this.highlightItem(items, this.currentIndex);
        },

        /**
         * Highlight item
         */
        highlightItem: function(items, index) {
            items.removeClass('active');
            const item = items.eq(index);
            item.addClass('active');

            // Scroll into visible area
            const container = this.results;
            const itemTop = item.position().top;
            const itemBottom = itemTop + item.outerHeight();
            const containerHeight = container.height();

            if (itemBottom > containerHeight) {
                container.scrollTop(container.scrollTop() + itemBottom - containerHeight);
            } else if (itemTop < 0) {
                container.scrollTop(container.scrollTop() + itemTop);
            }
        },

        /**
         * Select current item
         */
        selectCurrent: function() {
            const items = this.results.find('.fd-search-item');
            if (this.currentIndex >= 0 && this.currentIndex < items.length) {
                const url = items.eq(this.currentIndex).data('url');
                if (url) {
                    this.saveToHistory(this.input.val());
                    window.location.href = url;
                }
            }
        },

        /**
         * Save to history
         */
        saveToHistory: function(keyword) {
            if (!keyword || !fdSearchConfig.showHistory) return;

            // Remove duplicates
            this.searchHistory = this.searchHistory.filter(function(item) {
                return item !== keyword;
            });

            // Add to beginning
            this.searchHistory.unshift(keyword);

            // Limit count
            if (this.searchHistory.length > this.maxHistory) {
                this.searchHistory = this.searchHistory.slice(0, this.maxHistory);
            }

            // Save to localStorage
            try {
                localStorage.setItem('fd_search_history', JSON.stringify(this.searchHistory));
            } catch (e) {
                // Ignore errors
            }
        },

        /**
         * Load history
         */
        loadHistory: function() {
            if (!fdSearchConfig.showHistory) return;

            try {
                const history = localStorage.getItem('fd_search_history');
                if (history) {
                    this.searchHistory = JSON.parse(history);
                }
            } catch (e) {
                this.searchHistory = [];
            }
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        FDSearch.init();
    });

    // Expose to global scope for menu button usage
    window.FDSearch = FDSearch;

})(jQuery);
