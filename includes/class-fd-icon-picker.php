<?php
/**
 * FD Icon Picker
 * Reusable Dashicons icon picker component
 */

defined('ABSPATH') || exit;

class FD_Icon_Picker {

    /**
     * Whether assets (CSS/JS) have already been enqueued
     */
    private static $assets_enqueued = false;

    /**
     * Get all Dashicons list
     */
    public static function get_dashicons() {
        return array(
            '' => __('No Icon', 'fd-admin-ui'),
            // Admin Icons
            'dashicons-admin-appearance' => __('Appearance', 'fd-admin-ui'),
            'dashicons-admin-collapse' => __('Collapse', 'fd-admin-ui'),
            'dashicons-admin-comments' => __('Comments', 'fd-admin-ui'),
            'dashicons-admin-customizer' => __('Customizer', 'fd-admin-ui'),
            'dashicons-admin-generic' => __('Generic', 'fd-admin-ui'),
            'dashicons-admin-home' => __('Home', 'fd-admin-ui'),
            'dashicons-admin-links' => __('Links', 'fd-admin-ui'),
            'dashicons-admin-media' => __('Media', 'fd-admin-ui'),
            'dashicons-admin-multisite' => __('Multisite', 'fd-admin-ui'),
            'dashicons-admin-network' => __('Network', 'fd-admin-ui'),
            'dashicons-admin-page' => __('Page', 'fd-admin-ui'),
            'dashicons-admin-plugins' => __('Plugins', 'fd-admin-ui'),
            'dashicons-admin-post' => __('Post', 'fd-admin-ui'),
            'dashicons-admin-settings' => __('Settings', 'fd-admin-ui'),
            'dashicons-admin-site' => __('Site', 'fd-admin-ui'),
            'dashicons-admin-site-alt' => __('Site 2', 'fd-admin-ui'),
            'dashicons-admin-site-alt2' => __('Site 3', 'fd-admin-ui'),
            'dashicons-admin-site-alt3' => __('Site 4', 'fd-admin-ui'),
            'dashicons-admin-tools' => __('Tools', 'fd-admin-ui'),
            'dashicons-admin-users' => __('Users', 'fd-admin-ui'),
            // General Icons
            'dashicons-airplane' => __('Airplane', 'fd-admin-ui'),
            'dashicons-album' => __('Album', 'fd-admin-ui'),
            'dashicons-align-center' => __('Align Center', 'fd-admin-ui'),
            'dashicons-align-full-width' => __('Align Full Width', 'fd-admin-ui'),
            'dashicons-align-left' => __('Align Left', 'fd-admin-ui'),
            'dashicons-align-none' => __('Align None', 'fd-admin-ui'),
            'dashicons-align-pull-left' => __('Pull Left', 'fd-admin-ui'),
            'dashicons-align-pull-right' => __('Pull Right', 'fd-admin-ui'),
            'dashicons-align-right' => __('Align Right', 'fd-admin-ui'),
            'dashicons-align-wide' => __('Align Wide', 'fd-admin-ui'),
            'dashicons-amazon' => 'Amazon',
            'dashicons-analytics' => __('Analytics', 'fd-admin-ui'),
            'dashicons-archive' => __('Archive', 'fd-admin-ui'),
            'dashicons-arrow-down' => __('Arrow Down', 'fd-admin-ui'),
            'dashicons-arrow-down-alt' => __('Arrow Down 2', 'fd-admin-ui'),
            'dashicons-arrow-down-alt2' => __('Arrow Down 3', 'fd-admin-ui'),
            'dashicons-arrow-left' => __('Arrow Left', 'fd-admin-ui'),
            'dashicons-arrow-left-alt' => __('Arrow Left 2', 'fd-admin-ui'),
            'dashicons-arrow-left-alt2' => __('Arrow Left 3', 'fd-admin-ui'),
            'dashicons-arrow-right' => __('Arrow Right', 'fd-admin-ui'),
            'dashicons-arrow-right-alt' => __('Arrow Right 2', 'fd-admin-ui'),
            'dashicons-arrow-right-alt2' => __('Arrow Right 3', 'fd-admin-ui'),
            'dashicons-arrow-up' => __('Arrow Up', 'fd-admin-ui'),
            'dashicons-arrow-up-alt' => __('Arrow Up 2', 'fd-admin-ui'),
            'dashicons-arrow-up-alt2' => __('Arrow Up 3', 'fd-admin-ui'),
            'dashicons-art' => __('Art', 'fd-admin-ui'),
            'dashicons-awards' => __('Awards', 'fd-admin-ui'),
            'dashicons-backup' => __('Backup', 'fd-admin-ui'),
            'dashicons-bank' => __('Bank', 'fd-admin-ui'),
            'dashicons-beer' => __('Beer', 'fd-admin-ui'),
            'dashicons-bell' => __('Bell', 'fd-admin-ui'),
            'dashicons-block-default' => __('Block', 'fd-admin-ui'),
            'dashicons-book' => __('Book', 'fd-admin-ui'),
            'dashicons-book-alt' => __('Book 2', 'fd-admin-ui'),
            'dashicons-buddicons-activity' => __('Activity', 'fd-admin-ui'),
            'dashicons-buddicons-bbpress-logo' => 'bbPress',
            'dashicons-buddicons-buddypress-logo' => 'BuddyPress',
            'dashicons-buddicons-community' => __('Community', 'fd-admin-ui'),
            'dashicons-buddicons-forums' => __('Forums', 'fd-admin-ui'),
            'dashicons-buddicons-friends' => __('Friends', 'fd-admin-ui'),
            'dashicons-buddicons-groups' => __('Groups', 'fd-admin-ui'),
            'dashicons-buddicons-pm' => __('Private Message', 'fd-admin-ui'),
            'dashicons-buddicons-replies' => __('Replies', 'fd-admin-ui'),
            'dashicons-buddicons-topics' => __('Topics', 'fd-admin-ui'),
            'dashicons-buddicons-tracking' => __('Tracking', 'fd-admin-ui'),
            'dashicons-building' => __('Building', 'fd-admin-ui'),
            'dashicons-businessman' => __('Businessman', 'fd-admin-ui'),
            'dashicons-businessperson' => __('Businessperson', 'fd-admin-ui'),
            'dashicons-businesswoman' => __('Businesswoman', 'fd-admin-ui'),
            'dashicons-button' => __('Button', 'fd-admin-ui'),
            'dashicons-calculator' => __('Calculator', 'fd-admin-ui'),
            'dashicons-calendar' => __('Calendar', 'fd-admin-ui'),
            'dashicons-calendar-alt' => __('Calendar 2', 'fd-admin-ui'),
            'dashicons-camera' => __('Camera', 'fd-admin-ui'),
            'dashicons-camera-alt' => __('Camera 2', 'fd-admin-ui'),
            'dashicons-car' => __('Car', 'fd-admin-ui'),
            'dashicons-carrot' => __('Carrot', 'fd-admin-ui'),
            'dashicons-cart' => __('Cart', 'fd-admin-ui'),
            'dashicons-category' => __('Category', 'fd-admin-ui'),
            'dashicons-chart-area' => __('Area Chart', 'fd-admin-ui'),
            'dashicons-chart-bar' => __('Bar Chart', 'fd-admin-ui'),
            'dashicons-chart-line' => __('Line Chart', 'fd-admin-ui'),
            'dashicons-chart-pie' => __('Pie Chart', 'fd-admin-ui'),
            'dashicons-clipboard' => __('Clipboard', 'fd-admin-ui'),
            'dashicons-clock' => __('Clock', 'fd-admin-ui'),
            'dashicons-cloud' => __('Cloud', 'fd-admin-ui'),
            'dashicons-cloud-saved' => __('Cloud Saved', 'fd-admin-ui'),
            'dashicons-cloud-upload' => __('Cloud Upload', 'fd-admin-ui'),
            'dashicons-code-standards' => __('Code Standards', 'fd-admin-ui'),
            'dashicons-coffee' => __('Coffee', 'fd-admin-ui'),
            'dashicons-color-picker' => __('Color Picker', 'fd-admin-ui'),
            'dashicons-columns' => __('Columns', 'fd-admin-ui'),
            'dashicons-controls-back' => __('Back', 'fd-admin-ui'),
            'dashicons-controls-forward' => __('Forward', 'fd-admin-ui'),
            'dashicons-controls-pause' => __('Pause', 'fd-admin-ui'),
            'dashicons-controls-play' => __('Play', 'fd-admin-ui'),
            'dashicons-controls-repeat' => __('Repeat', 'fd-admin-ui'),
            'dashicons-controls-skipback' => __('Skip Back', 'fd-admin-ui'),
            'dashicons-controls-skipforward' => __('Skip Forward', 'fd-admin-ui'),
            'dashicons-controls-volumeoff' => __('Mute', 'fd-admin-ui'),
            'dashicons-controls-volumeon' => __('Volume', 'fd-admin-ui'),
            'dashicons-cover-image' => __('Cover Image', 'fd-admin-ui'),
            'dashicons-dashboard' => __('Dashboard', 'fd-admin-ui'),
            'dashicons-database' => __('Database', 'fd-admin-ui'),
            'dashicons-database-add' => __('Database Add', 'fd-admin-ui'),
            'dashicons-database-export' => __('Database Export', 'fd-admin-ui'),
            'dashicons-database-import' => __('Database Import', 'fd-admin-ui'),
            'dashicons-database-remove' => __('Database Remove', 'fd-admin-ui'),
            'dashicons-database-view' => __('Database View', 'fd-admin-ui'),
            'dashicons-desktop' => __('Desktop', 'fd-admin-ui'),
            'dashicons-dismiss' => __('Dismiss', 'fd-admin-ui'),
            'dashicons-download' => __('Download', 'fd-admin-ui'),
            'dashicons-drumstick' => __('Drumstick', 'fd-admin-ui'),
            'dashicons-edit' => __('Edit', 'fd-admin-ui'),
            'dashicons-edit-large' => __('Edit (Large)', 'fd-admin-ui'),
            'dashicons-edit-page' => __('Edit Page', 'fd-admin-ui'),
            'dashicons-editor-aligncenter' => __('Editor Align Center', 'fd-admin-ui'),
            'dashicons-editor-alignleft' => __('Editor Align Left', 'fd-admin-ui'),
            'dashicons-editor-alignright' => __('Editor Align Right', 'fd-admin-ui'),
            'dashicons-editor-bold' => __('Bold', 'fd-admin-ui'),
            'dashicons-editor-break' => __('Line Break', 'fd-admin-ui'),
            'dashicons-editor-code' => __('Code', 'fd-admin-ui'),
            'dashicons-editor-contract' => __('Contract', 'fd-admin-ui'),
            'dashicons-editor-customchar' => __('Special Character', 'fd-admin-ui'),
            'dashicons-editor-expand' => __('Expand', 'fd-admin-ui'),
            'dashicons-editor-help' => __('Help', 'fd-admin-ui'),
            'dashicons-editor-indent' => __('Indent', 'fd-admin-ui'),
            'dashicons-editor-insertmore' => __('Insert More', 'fd-admin-ui'),
            'dashicons-editor-italic' => __('Italic', 'fd-admin-ui'),
            'dashicons-editor-justify' => __('Justify', 'fd-admin-ui'),
            'dashicons-editor-kitchensink' => __('More Tools', 'fd-admin-ui'),
            'dashicons-editor-ltr' => __('Left to Right', 'fd-admin-ui'),
            'dashicons-editor-ol' => __('Ordered List', 'fd-admin-ui'),
            'dashicons-editor-ol-rtl' => __('Ordered List RTL', 'fd-admin-ui'),
            'dashicons-editor-outdent' => __('Outdent', 'fd-admin-ui'),
            'dashicons-editor-paragraph' => __('Paragraph', 'fd-admin-ui'),
            'dashicons-editor-paste-text' => __('Paste Text', 'fd-admin-ui'),
            'dashicons-editor-paste-word' => __('Paste Word', 'fd-admin-ui'),
            'dashicons-editor-quote' => __('Quote', 'fd-admin-ui'),
            'dashicons-editor-removeformatting' => __('Remove Formatting', 'fd-admin-ui'),
            'dashicons-editor-rtl' => __('Right to Left', 'fd-admin-ui'),
            'dashicons-editor-spellcheck' => __('Spellcheck', 'fd-admin-ui'),
            'dashicons-editor-strikethrough' => __('Strikethrough', 'fd-admin-ui'),
            'dashicons-editor-table' => __('Table', 'fd-admin-ui'),
            'dashicons-editor-textcolor' => __('Text Color', 'fd-admin-ui'),
            'dashicons-editor-ul' => __('Unordered List', 'fd-admin-ui'),
            'dashicons-editor-underline' => __('Underline', 'fd-admin-ui'),
            'dashicons-editor-unlink' => __('Unlink', 'fd-admin-ui'),
            'dashicons-editor-video' => __('Video', 'fd-admin-ui'),
            'dashicons-ellipsis' => __('Ellipsis', 'fd-admin-ui'),
            'dashicons-email' => __('Email', 'fd-admin-ui'),
            'dashicons-email-alt' => __('Email 2', 'fd-admin-ui'),
            'dashicons-email-alt2' => __('Email 3', 'fd-admin-ui'),
            'dashicons-embed-audio' => __('Embed Audio', 'fd-admin-ui'),
            'dashicons-embed-generic' => __('Embed Generic', 'fd-admin-ui'),
            'dashicons-embed-photo' => __('Embed Photo', 'fd-admin-ui'),
            'dashicons-embed-post' => __('Embed Post', 'fd-admin-ui'),
            'dashicons-embed-video' => __('Embed Video', 'fd-admin-ui'),
            'dashicons-excerpt-view' => __('Excerpt View', 'fd-admin-ui'),
            'dashicons-exit' => __('Exit', 'fd-admin-ui'),
            'dashicons-external' => __('External Link', 'fd-admin-ui'),
            'dashicons-facebook' => 'Facebook',
            'dashicons-facebook-alt' => 'Facebook 2',
            'dashicons-feedback' => __('Feedback', 'fd-admin-ui'),
            'dashicons-filter' => __('Filter', 'fd-admin-ui'),
            'dashicons-flag' => __('Flag', 'fd-admin-ui'),
            'dashicons-food' => __('Food', 'fd-admin-ui'),
            'dashicons-format-aside' => __('Aside', 'fd-admin-ui'),
            'dashicons-format-audio' => __('Audio', 'fd-admin-ui'),
            'dashicons-format-chat' => __('Chat', 'fd-admin-ui'),
            'dashicons-format-gallery' => __('Gallery', 'fd-admin-ui'),
            'dashicons-format-image' => __('Image', 'fd-admin-ui'),
            'dashicons-format-quote' => __('Quote', 'fd-admin-ui'),
            'dashicons-format-status' => __('Status', 'fd-admin-ui'),
            'dashicons-format-video' => __('Video', 'fd-admin-ui'),
            'dashicons-forms' => __('Forms', 'fd-admin-ui'),
            'dashicons-fullscreen-alt' => __('Fullscreen', 'fd-admin-ui'),
            'dashicons-fullscreen-exit-alt' => __('Exit Fullscreen', 'fd-admin-ui'),
            'dashicons-games' => __('Games', 'fd-admin-ui'),
            'dashicons-google' => 'Google',
            'dashicons-grid-view' => __('Grid View', 'fd-admin-ui'),
            'dashicons-groups' => __('Groups', 'fd-admin-ui'),
            'dashicons-hammer' => __('Hammer', 'fd-admin-ui'),
            'dashicons-heading' => __('Heading', 'fd-admin-ui'),
            'dashicons-heart' => __('Heart', 'fd-admin-ui'),
            'dashicons-hidden' => __('Hidden', 'fd-admin-ui'),
            'dashicons-hourglass' => __('Hourglass', 'fd-admin-ui'),
            'dashicons-html' => 'HTML',
            'dashicons-id' => __('ID Card', 'fd-admin-ui'),
            'dashicons-id-alt' => __('ID Card 2', 'fd-admin-ui'),
            'dashicons-image-crop' => __('Crop', 'fd-admin-ui'),
            'dashicons-image-filter' => __('Filter', 'fd-admin-ui'),
            'dashicons-image-flip-horizontal' => __('Flip Horizontal', 'fd-admin-ui'),
            'dashicons-image-flip-vertical' => __('Flip Vertical', 'fd-admin-ui'),
            'dashicons-image-rotate' => __('Rotate', 'fd-admin-ui'),
            'dashicons-image-rotate-left' => __('Rotate Left', 'fd-admin-ui'),
            'dashicons-image-rotate-right' => __('Rotate Right', 'fd-admin-ui'),
            'dashicons-images-alt' => __('Images', 'fd-admin-ui'),
            'dashicons-images-alt2' => __('Images 2', 'fd-admin-ui'),
            'dashicons-index-card' => __('Index Card', 'fd-admin-ui'),
            'dashicons-info' => __('Info', 'fd-admin-ui'),
            'dashicons-info-outline' => __('Info Outline', 'fd-admin-ui'),
            'dashicons-insert' => __('Insert', 'fd-admin-ui'),
            'dashicons-insert-after' => __('Insert After', 'fd-admin-ui'),
            'dashicons-insert-before' => __('Insert Before', 'fd-admin-ui'),
            'dashicons-instagram' => 'Instagram',
            'dashicons-laptop' => __('Laptop', 'fd-admin-ui'),
            'dashicons-layout' => __('Layout', 'fd-admin-ui'),
            'dashicons-leftright' => __('Left Right', 'fd-admin-ui'),
            'dashicons-lightbulb' => __('Lightbulb', 'fd-admin-ui'),
            'dashicons-linkedin' => 'LinkedIn',
            'dashicons-list-view' => __('List View', 'fd-admin-ui'),
            'dashicons-location' => __('Location', 'fd-admin-ui'),
            'dashicons-location-alt' => __('Location 2', 'fd-admin-ui'),
            'dashicons-lock' => __('Lock', 'fd-admin-ui'),
            'dashicons-marker' => __('Marker', 'fd-admin-ui'),
            'dashicons-media-archive' => __('Media Archive', 'fd-admin-ui'),
            'dashicons-media-audio' => __('Media Audio', 'fd-admin-ui'),
            'dashicons-media-code' => __('Media Code', 'fd-admin-ui'),
            'dashicons-media-default' => __('Media Default', 'fd-admin-ui'),
            'dashicons-media-document' => __('Media Document', 'fd-admin-ui'),
            'dashicons-media-interactive' => __('Media Interactive', 'fd-admin-ui'),
            'dashicons-media-spreadsheet' => __('Media Spreadsheet', 'fd-admin-ui'),
            'dashicons-media-text' => __('Media Text', 'fd-admin-ui'),
            'dashicons-media-video' => __('Media Video', 'fd-admin-ui'),
            'dashicons-megaphone' => __('Megaphone', 'fd-admin-ui'),
            'dashicons-menu' => __('Menu', 'fd-admin-ui'),
            'dashicons-menu-alt' => __('Menu 2', 'fd-admin-ui'),
            'dashicons-menu-alt2' => __('Menu 3', 'fd-admin-ui'),
            'dashicons-menu-alt3' => __('Menu 4', 'fd-admin-ui'),
            'dashicons-microphone' => __('Microphone', 'fd-admin-ui'),
            'dashicons-migrate' => __('Migrate', 'fd-admin-ui'),
            'dashicons-minus' => __('Minus', 'fd-admin-ui'),
            'dashicons-money' => __('Money', 'fd-admin-ui'),
            'dashicons-money-alt' => __('Money 2', 'fd-admin-ui'),
            'dashicons-move' => __('Move', 'fd-admin-ui'),
            'dashicons-nametag' => __('Nametag', 'fd-admin-ui'),
            'dashicons-networking' => __('Networking', 'fd-admin-ui'),
            'dashicons-no' => __('No', 'fd-admin-ui'),
            'dashicons-no-alt' => __('No 2', 'fd-admin-ui'),
            'dashicons-open-folder' => __('Open Folder', 'fd-admin-ui'),
            'dashicons-palmtree' => __('Palm Tree', 'fd-admin-ui'),
            'dashicons-paperclip' => __('Paperclip', 'fd-admin-ui'),
            'dashicons-pdf' => 'PDF',
            'dashicons-performance' => __('Performance', 'fd-admin-ui'),
            'dashicons-pets' => __('Pets', 'fd-admin-ui'),
            'dashicons-phone' => __('Phone', 'fd-admin-ui'),
            'dashicons-pinterest' => 'Pinterest',
            'dashicons-playlist-audio' => __('Audio Playlist', 'fd-admin-ui'),
            'dashicons-playlist-video' => __('Video Playlist', 'fd-admin-ui'),
            'dashicons-plugins-checked' => __('Plugins Checked', 'fd-admin-ui'),
            'dashicons-plus' => __('Plus', 'fd-admin-ui'),
            'dashicons-plus-alt' => __('Plus 2', 'fd-admin-ui'),
            'dashicons-plus-alt2' => __('Plus 3', 'fd-admin-ui'),
            'dashicons-podio' => 'Podio',
            'dashicons-portfolio' => __('Portfolio', 'fd-admin-ui'),
            'dashicons-post-status' => __('Post Status', 'fd-admin-ui'),
            'dashicons-pressthis' => 'PressThis',
            'dashicons-printer' => __('Printer', 'fd-admin-ui'),
            'dashicons-privacy' => __('Privacy', 'fd-admin-ui'),
            'dashicons-products' => __('Products', 'fd-admin-ui'),
            'dashicons-randomize' => __('Randomize', 'fd-admin-ui'),
            'dashicons-reddit' => 'Reddit',
            'dashicons-redo' => __('Redo', 'fd-admin-ui'),
            'dashicons-remove' => __('Remove', 'fd-admin-ui'),
            'dashicons-rest-api' => 'REST API',
            'dashicons-rss' => 'RSS',
            'dashicons-saved' => __('Saved', 'fd-admin-ui'),
            'dashicons-schedule' => __('Schedule', 'fd-admin-ui'),
            'dashicons-screenoptions' => __('Screen Options', 'fd-admin-ui'),
            'dashicons-search' => __('Search', 'fd-admin-ui'),
            'dashicons-share' => __('Share', 'fd-admin-ui'),
            'dashicons-share-alt' => __('Share 2', 'fd-admin-ui'),
            'dashicons-share-alt2' => __('Share 3', 'fd-admin-ui'),
            'dashicons-shield' => __('Shield', 'fd-admin-ui'),
            'dashicons-shield-alt' => __('Shield 2', 'fd-admin-ui'),
            'dashicons-shortcode' => __('Shortcode', 'fd-admin-ui'),
            'dashicons-slides' => __('Slides', 'fd-admin-ui'),
            'dashicons-smartphone' => __('Smartphone', 'fd-admin-ui'),
            'dashicons-smiley' => __('Smiley', 'fd-admin-ui'),
            'dashicons-sort' => __('Sort', 'fd-admin-ui'),
            'dashicons-sos' => 'SOS',
            'dashicons-spotify' => 'Spotify',
            'dashicons-star-empty' => __('Empty Star', 'fd-admin-ui'),
            'dashicons-star-filled' => __('Filled Star', 'fd-admin-ui'),
            'dashicons-star-half' => __('Half Star', 'fd-admin-ui'),
            'dashicons-sticky' => __('Sticky', 'fd-admin-ui'),
            'dashicons-store' => __('Store', 'fd-admin-ui'),
            'dashicons-superhero' => __('Superhero', 'fd-admin-ui'),
            'dashicons-superhero-alt' => __('Superhero 2', 'fd-admin-ui'),
            'dashicons-table-col-after' => __('Insert Column After', 'fd-admin-ui'),
            'dashicons-table-col-before' => __('Insert Column Before', 'fd-admin-ui'),
            'dashicons-table-col-delete' => __('Delete Column', 'fd-admin-ui'),
            'dashicons-table-row-after' => __('Insert Row After', 'fd-admin-ui'),
            'dashicons-table-row-before' => __('Insert Row Before', 'fd-admin-ui'),
            'dashicons-table-row-delete' => __('Delete Row', 'fd-admin-ui'),
            'dashicons-tablet' => __('Tablet', 'fd-admin-ui'),
            'dashicons-tag' => __('Tag', 'fd-admin-ui'),
            'dashicons-tagcloud' => __('Tag Cloud', 'fd-admin-ui'),
            'dashicons-testimonial' => __('Testimonial', 'fd-admin-ui'),
            'dashicons-text' => __('Text', 'fd-admin-ui'),
            'dashicons-text-page' => __('Text Page', 'fd-admin-ui'),
            'dashicons-thumbs-down' => __('Thumbs Down', 'fd-admin-ui'),
            'dashicons-thumbs-up' => __('Thumbs Up', 'fd-admin-ui'),
            'dashicons-tickets' => __('Tickets', 'fd-admin-ui'),
            'dashicons-tickets-alt' => __('Tickets 2', 'fd-admin-ui'),
            'dashicons-tide' => 'Tide',
            'dashicons-translation' => __('Translation', 'fd-admin-ui'),
            'dashicons-trash' => __('Trash', 'fd-admin-ui'),
            'dashicons-twitch' => 'Twitch',
            'dashicons-twitter' => 'Twitter',
            'dashicons-twitter-alt' => 'Twitter 2',
            'dashicons-undo' => __('Undo', 'fd-admin-ui'),
            'dashicons-universal-access' => __('Accessibility', 'fd-admin-ui'),
            'dashicons-universal-access-alt' => __('Accessibility 2', 'fd-admin-ui'),
            'dashicons-unlock' => __('Unlock', 'fd-admin-ui'),
            'dashicons-update' => __('Update', 'fd-admin-ui'),
            'dashicons-update-alt' => __('Update 2', 'fd-admin-ui'),
            'dashicons-upload' => __('Upload', 'fd-admin-ui'),
            'dashicons-vault' => __('Vault', 'fd-admin-ui'),
            'dashicons-video-alt' => __('Video 2', 'fd-admin-ui'),
            'dashicons-video-alt2' => __('Video 3', 'fd-admin-ui'),
            'dashicons-video-alt3' => __('Video 4', 'fd-admin-ui'),
            'dashicons-visibility' => __('Visible', 'fd-admin-ui'),
            'dashicons-warning' => __('Warning', 'fd-admin-ui'),
            'dashicons-welcome-add-page' => __('Add Page', 'fd-admin-ui'),
            'dashicons-welcome-comments' => __('Welcome Comments', 'fd-admin-ui'),
            'dashicons-welcome-learn-more' => __('Learn More', 'fd-admin-ui'),
            'dashicons-welcome-view-site' => __('View Site', 'fd-admin-ui'),
            'dashicons-welcome-widgets-menus' => __('Widgets Menus', 'fd-admin-ui'),
            'dashicons-welcome-write-blog' => __('Write Blog', 'fd-admin-ui'),
            'dashicons-whatsapp' => 'WhatsApp',
            'dashicons-wordpress' => 'WordPress',
            'dashicons-wordpress-alt' => 'WordPress 2',
            'dashicons-xing' => 'Xing',
            'dashicons-yes' => __('Yes', 'fd-admin-ui'),
            'dashicons-yes-alt' => __('Yes 2', 'fd-admin-ui'),
            'dashicons-youtube' => 'YouTube',
        );
    }

    /**
     * Get the CSS content code for a Dashicon
     */
    public static function get_dashicon_content($class_name) {
        $icons = array(
            // Admin Icons
            'dashicons-admin-appearance' => '\f100',
            'dashicons-admin-collapse' => '\f148',
            'dashicons-admin-comments' => '\f101',
            'dashicons-admin-customizer' => '\f540',
            'dashicons-admin-generic' => '\f111',
            'dashicons-admin-home' => '\f102',
            'dashicons-admin-links' => '\f103',
            'dashicons-admin-media' => '\f104',
            'dashicons-admin-multisite' => '\f541',
            'dashicons-admin-network' => '\f112',
            'dashicons-admin-page' => '\f105',
            'dashicons-admin-plugins' => '\f106',
            'dashicons-admin-post' => '\f109',
            'dashicons-admin-settings' => '\f108',
            'dashicons-admin-site' => '\f319',
            'dashicons-admin-site-alt' => '\f11d',
            'dashicons-admin-site-alt2' => '\f11e',
            'dashicons-admin-site-alt3' => '\f11f',
            'dashicons-admin-tools' => '\f107',
            'dashicons-admin-users' => '\f110',
            // Welcome Icons
            'dashicons-welcome-add-page' => '\f133',
            'dashicons-welcome-comments' => '\f117',
            'dashicons-welcome-learn-more' => '\f118',
            'dashicons-welcome-view-site' => '\f115',
            'dashicons-welcome-widgets-menus' => '\f116',
            'dashicons-welcome-write-blog' => '\f119',
            // Post Formats
            'dashicons-format-aside' => '\f123',
            'dashicons-format-audio' => '\f127',
            'dashicons-format-chat' => '\f125',
            'dashicons-format-gallery' => '\f161',
            'dashicons-format-image' => '\f128',
            'dashicons-format-quote' => '\f122',
            'dashicons-format-status' => '\f130',
            'dashicons-format-video' => '\f126',
            // Media
            'dashicons-camera' => '\f306',
            'dashicons-camera-alt' => '\f129',
            'dashicons-images-alt' => '\f232',
            'dashicons-images-alt2' => '\f233',
            'dashicons-video-alt' => '\f234',
            'dashicons-video-alt2' => '\f235',
            'dashicons-video-alt3' => '\f236',
            'dashicons-media-archive' => '\f501',
            'dashicons-media-audio' => '\f500',
            'dashicons-media-code' => '\f499',
            'dashicons-media-default' => '\f498',
            'dashicons-media-document' => '\f497',
            'dashicons-media-interactive' => '\f496',
            'dashicons-media-spreadsheet' => '\f495',
            'dashicons-media-text' => '\f491',
            'dashicons-media-video' => '\f490',
            'dashicons-playlist-audio' => '\f492',
            'dashicons-playlist-video' => '\f493',
            'dashicons-controls-play' => '\f522',
            'dashicons-controls-pause' => '\f523',
            'dashicons-controls-forward' => '\f519',
            'dashicons-controls-skipforward' => '\f517',
            'dashicons-controls-back' => '\f518',
            'dashicons-controls-skipback' => '\f516',
            'dashicons-controls-repeat' => '\f515',
            'dashicons-controls-volumeon' => '\f521',
            'dashicons-controls-volumeoff' => '\f520',
            // Image Editing
            'dashicons-image-crop' => '\f165',
            'dashicons-image-filter' => '\f533',
            'dashicons-image-flip-horizontal' => '\f169',
            'dashicons-image-flip-vertical' => '\f168',
            'dashicons-image-rotate' => '\f167',
            'dashicons-image-rotate-left' => '\f166',
            'dashicons-image-rotate-right' => '\f167',
            'dashicons-undo' => '\f171',
            'dashicons-redo' => '\f172',
            // Databases
            'dashicons-database' => '\f170',
            'dashicons-database-add' => '\f170',
            'dashicons-database-export' => '\f170',
            'dashicons-database-import' => '\f170',
            'dashicons-database-remove' => '\f170',
            'dashicons-database-view' => '\f170',
            // TinyMCE
            'dashicons-editor-aligncenter' => '\f207',
            'dashicons-editor-alignleft' => '\f206',
            'dashicons-editor-alignright' => '\f208',
            'dashicons-editor-bold' => '\f200',
            'dashicons-editor-break' => '\f474',
            'dashicons-editor-code' => '\f475',
            'dashicons-editor-contract' => '\f506',
            'dashicons-editor-customchar' => '\f220',
            'dashicons-editor-expand' => '\f211',
            'dashicons-editor-help' => '\f223',
            'dashicons-editor-indent' => '\f222',
            'dashicons-editor-insertmore' => '\f209',
            'dashicons-editor-italic' => '\f201',
            'dashicons-editor-justify' => '\f214',
            'dashicons-editor-kitchensink' => '\f212',
            'dashicons-editor-ltr' => '\f320',
            'dashicons-editor-ol' => '\f204',
            'dashicons-editor-ol-rtl' => '\f12c',
            'dashicons-editor-outdent' => '\f221',
            'dashicons-editor-paragraph' => '\f476',
            'dashicons-editor-paste-text' => '\f217',
            'dashicons-editor-paste-word' => '\f216',
            'dashicons-editor-quote' => '\f205',
            'dashicons-editor-removeformatting' => '\f218',
            'dashicons-editor-rtl' => '\f320',
            'dashicons-editor-spellcheck' => '\f210',
            'dashicons-editor-strikethrough' => '\f224',
            'dashicons-editor-table' => '\f535',
            'dashicons-editor-textcolor' => '\f215',
            'dashicons-editor-ul' => '\f203',
            'dashicons-editor-underline' => '\f213',
            'dashicons-editor-unlink' => '\f225',
            'dashicons-editor-video' => '\f219',
            // Posts
            'dashicons-align-center' => '\f134',
            'dashicons-align-full-width' => '\f11b',
            'dashicons-align-left' => '\f135',
            'dashicons-align-none' => '\f138',
            'dashicons-align-pull-left' => '\f136',
            'dashicons-align-pull-right' => '\f137',
            'dashicons-align-right' => '\f136',
            'dashicons-align-wide' => '\f11c',
            'dashicons-block-default' => '\f12b',
            'dashicons-button' => '\f11a',
            'dashicons-cloud' => '\f176',
            'dashicons-cloud-saved' => '\f137',
            'dashicons-cloud-upload' => '\f13b',
            'dashicons-columns' => '\f13c',
            'dashicons-cover-image' => '\f13d',
            'dashicons-ellipsis' => '\f11c',
            'dashicons-embed-audio' => '\f13e',
            'dashicons-embed-generic' => '\f13f',
            'dashicons-embed-photo' => '\f144',
            'dashicons-embed-post' => '\f146',
            'dashicons-embed-video' => '\f149',
            'dashicons-exit' => '\f14a',
            'dashicons-heading' => '\f10e',
            'dashicons-html' => '\f14b',
            'dashicons-info-outline' => '\f14c',
            'dashicons-insert' => '\f10f',
            'dashicons-insert-after' => '\f14d',
            'dashicons-insert-before' => '\f14e',
            'dashicons-remove' => '\f14f',
            'dashicons-saved' => '\f15e',
            'dashicons-shortcode' => '\f150',
            'dashicons-table-col-after' => '\f151',
            'dashicons-table-col-before' => '\f152',
            'dashicons-table-col-delete' => '\f15a',
            'dashicons-table-row-after' => '\f15b',
            'dashicons-table-row-before' => '\f15c',
            'dashicons-table-row-delete' => '\f15d',
            // Sorting
            'dashicons-excerpt-view' => '\f164',
            'dashicons-grid-view' => '\f509',
            'dashicons-list-view' => '\f163',
            'dashicons-move' => '\f545',
            'dashicons-screenoptions' => '\f180',
            // Social
            'dashicons-amazon' => '\f162',
            'dashicons-facebook' => '\f304',
            'dashicons-facebook-alt' => '\f305',
            'dashicons-google' => '\f18a',
            'dashicons-instagram' => '\f12d',
            'dashicons-linkedin' => '\f18d',
            'dashicons-pinterest' => '\f192',
            'dashicons-podio' => '\f19c',
            'dashicons-reddit' => '\f195',
            'dashicons-rss' => '\f303',
            'dashicons-share' => '\f237',
            'dashicons-share-alt' => '\f240',
            'dashicons-share-alt2' => '\f242',
            'dashicons-spotify' => '\f196',
            'dashicons-twitch' => '\f199',
            'dashicons-twitter' => '\f301',
            'dashicons-twitter-alt' => '\f302',
            'dashicons-whatsapp' => '\f19a',
            'dashicons-xing' => '\f19d',
            'dashicons-youtube' => '\f19b',
            // WordPress.org
            'dashicons-hammer' => '\f308',
            'dashicons-art' => '\f309',
            'dashicons-migrate' => '\f310',
            'dashicons-performance' => '\f311',
            'dashicons-universal-access' => '\f483',
            'dashicons-universal-access-alt' => '\f507',
            'dashicons-tickets' => '\f486',
            'dashicons-tickets-alt' => '\f190',
            'dashicons-nametag' => '\f484',
            'dashicons-clipboard' => '\f481',
            'dashicons-heart' => '\f487',
            'dashicons-megaphone' => '\f488',
            'dashicons-schedule' => '\f489',
            // Products
            'dashicons-wordpress' => '\f120',
            'dashicons-wordpress-alt' => '\f324',
            'dashicons-pressthis' => '\f157',
            'dashicons-update' => '\f463',
            'dashicons-update-alt' => '\f113',
            'dashicons-buddicons-activity' => '\f452',
            'dashicons-buddicons-bbpress-logo' => '\f477',
            'dashicons-buddicons-buddypress-logo' => '\f448',
            'dashicons-buddicons-community' => '\f453',
            'dashicons-buddicons-forums' => '\f449',
            'dashicons-buddicons-friends' => '\f454',
            'dashicons-buddicons-groups' => '\f456',
            'dashicons-buddicons-pm' => '\f457',
            'dashicons-buddicons-replies' => '\f451',
            'dashicons-buddicons-topics' => '\f450',
            'dashicons-buddicons-tracking' => '\f455',
            // Taxonomies
            'dashicons-category' => '\f318',
            'dashicons-tag' => '\f323',
            // Widgets
            'dashicons-archive' => '\f480',
            'dashicons-tagcloud' => '\f479',
            'dashicons-text' => '\f478',
            // Notifications
            'dashicons-bell' => '\f16d',
            'dashicons-yes' => '\f147',
            'dashicons-yes-alt' => '\f12a',
            'dashicons-no' => '\f158',
            'dashicons-no-alt' => '\f335',
            'dashicons-plus' => '\f132',
            'dashicons-plus-alt' => '\f502',
            'dashicons-plus-alt2' => '\f543',
            'dashicons-minus' => '\f460',
            'dashicons-dismiss' => '\f153',
            'dashicons-marker' => '\f159',
            'dashicons-star-filled' => '\f155',
            'dashicons-star-half' => '\f459',
            'dashicons-star-empty' => '\f154',
            'dashicons-flag' => '\f227',
            'dashicons-warning' => '\f534',
            'dashicons-info' => '\f348',
            // Misc
            'dashicons-airplane' => '\f15f',
            'dashicons-album' => '\f514',
            'dashicons-analytics' => '\f183',
            'dashicons-awards' => '\f313',
            'dashicons-backup' => '\f321',
            'dashicons-bank' => '\f16a',
            'dashicons-beer' => '\f16c',
            'dashicons-book' => '\f330',
            'dashicons-book-alt' => '\f331',
            'dashicons-building' => '\f512',
            'dashicons-businessman' => '\f338',
            'dashicons-businessperson' => '\f12f',
            'dashicons-businesswoman' => '\f12e',
            'dashicons-calculator' => '\f16e',
            'dashicons-calendar' => '\f145',
            'dashicons-calendar-alt' => '\f508',
            'dashicons-car' => '\f16b',
            'dashicons-carrot' => '\f511',
            'dashicons-cart' => '\f174',
            'dashicons-chart-area' => '\f239',
            'dashicons-chart-bar' => '\f185',
            'dashicons-chart-line' => '\f238',
            'dashicons-chart-pie' => '\f184',
            'dashicons-clock' => '\f469',
            'dashicons-code-standards' => '\f13a',
            'dashicons-coffee' => '\f16f',
            'dashicons-color-picker' => '\f131',
            'dashicons-dashboard' => '\f226',
            'dashicons-desktop' => '\f472',
            'dashicons-download' => '\f316',
            'dashicons-drumstick' => '\f17f',
            'dashicons-edit' => '\f464',
            'dashicons-edit-large' => '\f327',
            'dashicons-edit-page' => '\f186',
            'dashicons-email' => '\f465',
            'dashicons-email-alt' => '\f466',
            'dashicons-email-alt2' => '\f467',
            'dashicons-external' => '\f504',
            'dashicons-feedback' => '\f175',
            'dashicons-filter' => '\f536',
            'dashicons-food' => '\f187',
            'dashicons-forms' => '\f314',
            'dashicons-fullscreen-alt' => '\f188',
            'dashicons-fullscreen-exit-alt' => '\f189',
            'dashicons-games' => '\f18c',
            'dashicons-groups' => '\f307',
            'dashicons-hourglass' => '\f18e',
            'dashicons-id' => '\f336',
            'dashicons-id-alt' => '\f337',
            'dashicons-index-card' => '\f510',
            'dashicons-laptop' => '\f547',
            'dashicons-layout' => '\f538',
            'dashicons-leftright' => '\f229',
            'dashicons-lightbulb' => '\f339',
            'dashicons-location' => '\f230',
            'dashicons-location-alt' => '\f231',
            'dashicons-lock' => '\f160',
            'dashicons-menu' => '\f333',
            'dashicons-menu-alt' => '\f228',
            'dashicons-menu-alt2' => '\f329',
            'dashicons-menu-alt3' => '\f349',
            'dashicons-microphone' => '\f482',
            'dashicons-money' => '\f526',
            'dashicons-money-alt' => '\f18f',
            'dashicons-networking' => '\f325',
            'dashicons-open-folder' => '\f537',
            'dashicons-palmtree' => '\f527',
            'dashicons-paperclip' => '\f546',
            'dashicons-pdf' => '\f190',
            'dashicons-pets' => '\f191',
            'dashicons-phone' => '\f525',
            'dashicons-portfolio' => '\f322',
            'dashicons-post-status' => '\f173',
            'dashicons-printer' => '\f193',
            'dashicons-privacy' => '\f194',
            'dashicons-products' => '\f312',
            'dashicons-randomize' => '\f503',
            'dashicons-rest-api' => '\f124',
            'dashicons-shield' => '\f332',
            'dashicons-shield-alt' => '\f334',
            'dashicons-slides' => '\f181',
            'dashicons-smartphone' => '\f470',
            'dashicons-smiley' => '\f328',
            'dashicons-sort' => '\f156',
            'dashicons-sos' => '\f468',
            'dashicons-sticky' => '\f537',
            'dashicons-store' => '\f513',
            'dashicons-superhero' => '\f197',
            'dashicons-superhero-alt' => '\f198',
            'dashicons-tablet' => '\f471',
            'dashicons-testimonial' => '\f473',
            'dashicons-text-page' => '\f121',
            'dashicons-thumbs-down' => '\f542',
            'dashicons-thumbs-up' => '\f529',
            'dashicons-tide' => '\f10d',
            'dashicons-translation' => '\f326',
            'dashicons-trash' => '\f182',
            'dashicons-unlock' => '\f528',
            'dashicons-upload' => '\f317',
            'dashicons-vault' => '\f178',
            'dashicons-hidden' => '\f530',
            'dashicons-visibility' => '\f177',
            // Arrows
            'dashicons-arrow-down' => '\f140',
            'dashicons-arrow-down-alt' => '\f346',
            'dashicons-arrow-down-alt2' => '\f347',
            'dashicons-arrow-left' => '\f141',
            'dashicons-arrow-left-alt' => '\f340',
            'dashicons-arrow-left-alt2' => '\f341',
            'dashicons-arrow-right' => '\f139',
            'dashicons-arrow-right-alt' => '\f344',
            'dashicons-arrow-right-alt2' => '\f345',
            'dashicons-arrow-up' => '\f142',
            'dashicons-arrow-up-alt' => '\f342',
            'dashicons-arrow-up-alt2' => '\f343',
            // Search
            'dashicons-search' => '\f179',
            'dashicons-plugins-checked' => '\f485',
        );

        return isset($icons[$class_name]) ? $icons[$class_name] : '\f111';
    }

    /**
     * Enqueue component CSS and JS assets (only once)
     */
    public static function enqueue_assets() {
        if (self::$assets_enqueued) {
            return;
        }
        self::$assets_enqueued = true;
        ?>
        <style id="fd-icon-picker-styles">
            /* Icon picker container */
            .fd-icon-picker {
                position: relative;
                width: 100%;
                max-width: 350px;
            }

            /* Trigger button */
            .fd-icon-picker-trigger {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px 12px;
                background: #fff;
                border: 1px solid #8c8f94;
                border-radius: 4px;
                cursor: pointer;
                min-height: 36px;
                transition: border-color 0.2s;
            }
            .fd-icon-picker-trigger:hover {
                border-color: #2271b1;
            }
            .fd-icon-picker-trigger .dashicons {
                flex-shrink: 0;
                font-size: 20px;
                width: 20px;
                height: 20px;
                color: #2c3338;
            }
            .fd-icon-picker-trigger .fd-icon-picker-label {
                flex: 1;
                color: #2c3338;
                font-size: 13px;
            }
            .fd-icon-picker-trigger .fd-icon-picker-arrow {
                color: #8c8f94;
                transition: transform 0.2s;
                font-size: 16px !important;
                width: 16px !important;
                height: 16px !important;
            }
            .fd-icon-picker.open .fd-icon-picker-trigger .fd-icon-picker-arrow {
                transform: rotate(180deg);
            }

            /* Dropdown panel */
            .fd-icon-picker-dropdown {
                display: none;
                position: fixed;
                background: #fff;
                border: 1px solid #8c8f94;
                border-radius: 4px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 999999;
                max-height: 320px;
                overflow: hidden;
                width: 480px;
            }
            .fd-icon-picker.open .fd-icon-picker-dropdown {
                display: block;
            }

            /* Search box */
            .fd-icon-picker-search {
                padding: 8px;
                border-bottom: 1px solid #ddd;
            }
            .fd-icon-picker-search input {
                width: 100%;
                padding: 6px 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 13px;
            }
            .fd-icon-picker-search input:focus {
                outline: none;
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
            }

            /* Icon list container */
            .fd-icon-picker-list {
                max-height: 260px;
                overflow-y: auto;
                padding: 8px;
            }

            /* Icon grid */
            .fd-icon-picker-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 4px;
            }

            /* Single icon option */
            .fd-icon-picker-option {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 10px;
                cursor: pointer;
                transition: background 0.15s;
                border-radius: 4px;
                font-size: 12px;
            }
            .fd-icon-picker-option:hover {
                background: #f0f0f1;
            }
            .fd-icon-picker-option.selected {
                background: #e7f3ff;
            }
            .fd-icon-picker-option .dashicons {
                flex-shrink: 0;
                color: #2c3338;
                font-size: 20px;
                width: 20px;
                height: 20px;
            }
            .fd-icon-picker-option .fd-option-text {
                color: #2c3338;
                font-size: 12px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .fd-icon-picker-option-none {
                color: #8c8f94 !important;
                font-style: italic;
            }
            .fd-icon-picker-option-full {
                grid-column: 1 / -1;
            }

            /* No results message */
            .fd-icon-picker-no-results {
                padding: 20px;
                text-align: center;
                color: #8c8f94;
                font-size: 13px;
            }

            /* Preview area */
            .fd-icon-picker-preview {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                background: #f0f0f1;
                border-radius: 4px;
            }
            .fd-icon-picker-preview .dashicons {
                font-size: 20px;
                width: 20px;
                height: 20px;
                color: #2c3338;
            }
        </style>

        <script id="fd-icon-picker-script">
        (function($) {
            'use strict';

            // Icon picker class
            window.FDIconPicker = {
                init: function() {
                    this.bindEvents();
                },

                bindEvents: function() {
                    var self = this;

                    // Click trigger
                    $(document).on('click', '.fd-icon-picker-trigger', function(e) {
                        e.stopPropagation();
                        self.toggleDropdown($(this).closest('.fd-icon-picker'));
                    });

                    // Search filter
                    $(document).on('input', '.fd-icon-picker-search input', function() {
                        self.filterIcons($(this));
                    });

                    // Select icon
                    $(document).on('click', '.fd-icon-picker-option', function(e) {
                        e.stopPropagation();
                        self.selectIcon($(this));
                    });

                    // Click outside to close
                    $(document).on('click', function() {
                        self.closeAll();
                    });

                    // Close on scroll
                    $(window).on('scroll', function() {
                        self.closeAll();
                    });
                },

                toggleDropdown: function($picker) {
                    var isOpen = $picker.hasClass('open');

                    // Close all others
                    this.closeAll();

                    if (!isOpen) {
                        this.openDropdown($picker);
                    }
                },

                openDropdown: function($picker) {
                    var $trigger = $picker.find('.fd-icon-picker-trigger');
                    var $dropdown = $picker.find('.fd-icon-picker-dropdown');
                    var triggerRect = $trigger[0].getBoundingClientRect();
                    var viewportHeight = window.innerHeight;
                    var dropdownHeight = 320;

                    // Determine whether to expand upward or downward
                    var spaceBelow = viewportHeight - triggerRect.bottom;

                    if (spaceBelow < dropdownHeight && triggerRect.top > spaceBelow) {
                        $dropdown.css({
                            top: 'auto',
                            bottom: (viewportHeight - triggerRect.top + 2) + 'px',
                            left: triggerRect.left + 'px'
                        });
                    } else {
                        $dropdown.css({
                            top: (triggerRect.bottom + 2) + 'px',
                            bottom: 'auto',
                            left: triggerRect.left + 'px'
                        });
                    }

                    $picker.addClass('open');

                    // Focus search box
                    setTimeout(function() {
                        $picker.find('.fd-icon-picker-search input').focus();
                    }, 100);
                },

                closeAll: function() {
                    $('.fd-icon-picker').removeClass('open');
                },

                filterIcons: function($input) {
                    var keyword = $input.val().toLowerCase();
                    var $picker = $input.closest('.fd-icon-picker');
                    var $options = $picker.find('.fd-icon-picker-option');
                    var hasResults = false;

                    $options.each(function() {
                        var $option = $(this);
                        var text = $option.find('.fd-option-text').text().toLowerCase();
                        var value = $option.data('value') || '';

                        if (text.indexOf(keyword) !== -1 || value.toLowerCase().indexOf(keyword) !== -1) {
                            $option.show();
                            hasResults = true;
                        } else {
                            $option.hide();
                        }
                    });

                    // Show/hide no results message
                    var $noResults = $picker.find('.fd-icon-picker-no-results');
                    if (hasResults) {
                        $noResults.hide();
                    } else {
                        if ($noResults.length === 0) {
                            $picker.find('.fd-icon-picker-grid').after('<div class="fd-icon-picker-no-results"><?php echo esc_js(__('No matching icons found', 'fd-admin-ui')); ?></div>');
                        } else {
                            $noResults.show();
                        }
                    }
                },

                selectIcon: function($option) {
                    var $picker = $option.closest('.fd-icon-picker');
                    var value = $option.data('value');
                    var label = $option.data('label');
                    var $trigger = $picker.find('.fd-icon-picker-trigger');
                    var $input = $picker.find('.fd-icon-picker-value');

                    // Update value
                    $input.val(value).trigger('change');

                    // Update trigger display
                    $trigger.find('.dashicons').not('.fd-icon-picker-arrow').remove();
                    if (value) {
                        $trigger.prepend('<span class="dashicons ' + value + '"></span>');
                    }
                    $trigger.find('.fd-icon-picker-label').text(label);

                    // Update selected state
                    $picker.find('.fd-icon-picker-option').removeClass('selected');
                    $option.addClass('selected');

                    // Update associated preview (if any)
                    var previewId = $picker.data('preview');
                    if (previewId) {
                        var $preview = $('#' + previewId);
                        $preview.empty();
                        if (value) {
                            $preview.append('<span class="dashicons ' + value + '"></span>');
                        }
                    }

                    // Trigger custom event
                    $picker.trigger('fd-icon-selected', [value, label]);

                    // Close dropdown
                    this.closeAll();
                }
            };

            // Initialize when DOM is ready
            $(document).ready(function() {
                FDIconPicker.init();
            });

        })(jQuery);
        </script>
        <?php
    }

    /**
     * Render the icon picker
     *
     * @param array $args Arguments array
     *   - name: input name attribute
     *   - value: currently selected value
     *   - id: picker ID (optional)
     *   - preview_id: preview element ID (optional)
     *   - empty_label: label text for empty option (optional, default "No Icon")
     *   - show_search: whether to show search box (optional, default true)
     *   - icons: custom icon list (optional, uses full list by default)
     */
    public static function render($args = array()) {
        $defaults = array(
            'name' => '',
            'value' => '',
            'id' => '',
            'preview_id' => '',
            'empty_label' => __('No Icon', 'fd-admin-ui'),
            'show_search' => true,
            'icons' => null,
        );

        $args = wp_parse_args($args, $defaults);

        // Ensure assets are loaded
        self::enqueue_assets();

        // Get icon list
        $icons = $args['icons'] !== null ? $args['icons'] : self::get_dashicons();

        // If custom empty label is provided, replace the first one
        if ($args['empty_label'] !== __('No Icon', 'fd-admin-ui') && isset($icons[''])) {
            $icons[''] = $args['empty_label'];
        }

        // Get the label for the currently selected icon
        $selected_label = isset($icons[$args['value']]) ? $icons[$args['value']] : $args['empty_label'];

        // Generate unique ID
        $picker_id = $args['id'] ? $args['id'] : 'fd-icon-picker-' . wp_rand();

        ?>
        <div class="fd-icon-picker" id="<?php echo esc_attr($picker_id); ?>" <?php echo $args['preview_id'] ? 'data-preview="' . esc_attr($args['preview_id']) . '"' : ''; ?>>
            <input type="hidden"
                   name="<?php echo esc_attr($args['name']); ?>"
                   value="<?php echo esc_attr($args['value']); ?>"
                   class="fd-icon-picker-value">

            <div class="fd-icon-picker-trigger">
                <?php if ($args['value']): ?>
                    <span class="dashicons <?php echo esc_attr($args['value']); ?>"></span>
                <?php endif; ?>
                <span class="fd-icon-picker-label"><?php echo esc_html($selected_label); ?></span>
                <span class="dashicons dashicons-arrow-down-alt2 fd-icon-picker-arrow"></span>
            </div>

            <div class="fd-icon-picker-dropdown">
                <?php if ($args['show_search']): ?>
                <div class="fd-icon-picker-search">
                    <input type="text" placeholder="<?php esc_attr_e('Search icons...', 'fd-admin-ui'); ?>">
                </div>
                <?php endif; ?>

                <div class="fd-icon-picker-list">
                    <div class="fd-icon-picker-grid">
                        <?php
                        $is_first = true;
                        foreach ($icons as $icon_class => $icon_label):
                        ?>
                            <div class="fd-icon-picker-option <?php echo $args['value'] === $icon_class ? 'selected' : ''; ?> <?php echo $is_first ? 'fd-icon-picker-option-full' : ''; ?>"
                                 data-value="<?php echo esc_attr($icon_class); ?>"
                                 data-label="<?php echo esc_attr($icon_label); ?>">
                                <?php if ($icon_class): ?>
                                    <span class="dashicons <?php echo esc_attr($icon_class); ?>"></span>
                                <?php endif; ?>
                                <span class="fd-option-text <?php echo !$icon_class ? 'fd-icon-picker-option-none' : ''; ?>"><?php echo esc_html($icon_label); ?></span>
                            </div>
                        <?php
                        $is_first = false;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render icon preview
     *
     * @param string $id Preview element ID
     * @param string $value Currently selected icon class
     */
    public static function render_preview($id, $value = '') {
        ?>
        <div class="fd-icon-picker-preview" id="<?php echo esc_attr($id); ?>">
            <?php if ($value): ?>
                <span class="dashicons <?php echo esc_attr($value); ?>"></span>
            <?php endif; ?>
        </div>
        <?php
    }
}
