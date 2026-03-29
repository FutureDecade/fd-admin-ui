=== FD Admin UI ===
Contributors: futuredecade
Tags: admin theme, admin ui, custom admin, admin customization, dashboard
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, lightweight admin UI framework for WordPress. Customize colors, menus, login page, global search, and more — all from one settings panel.

== Description ==

FD Admin UI modernizes your WordPress admin interface with a unified design system, customizable color themes, and powerful productivity features — without bloat.

**Key Features:**

* **Visual Identity System** — Set primary, secondary, accent, and semantic colors. Automatically generates consistent color scales across the entire admin.
* **Admin Bar Customization** — Choose classic or modern style, add your brand logo, toggle visibility of specific items.
* **Sidebar Menu Management** — Drag-and-drop reordering, custom colors, adjustable width, hover effects, and auto-fold on post editor.
* **Global Search (Cmd+K)** — Instantly search posts, pages, media, users, plugins, and menu items from anywhere in the admin.
* **Login Page Customizer** — Custom background, logo, colors, and welcome message for the login page.
* **Editor Enhancements** — Unified featured image and excerpt meta box, automatic table of contents, and merged TinyMCE toolbars.
* **Modern List Tables** — Horizontal scrolling for wide tables, author avatars, and responsive styling.
* **Unified Taxonomy Box** — Manage categories, tags, and custom taxonomies from a single, compact meta box.
* **Dashboard & Footer** — Customize dashboard widgets and admin footer text.
* **Design Tokens** — A shared design token library for consistent styling across themes and plugins.

All features are modular and can be individually enabled or disabled.

== Installation ==

1. Upload the `fd-admin-ui` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings > Admin UI** to configure.

== Frequently Asked Questions ==

= Does this plugin affect the frontend of my site? =

No. FD Admin UI only modifies the WordPress admin dashboard. It has zero impact on your site's frontend performance or appearance.

= Is it compatible with other admin plugins? =

Yes. FD Admin UI is designed to be non-invasive and works alongside most admin plugins. If you encounter a conflict, please report it in the support forum.

= Does it work with Multisite? =

Yes. The plugin can be activated on individual sites within a Multisite network.

= Can I use it with my custom theme? =

Absolutely. If you use the FD Theme, visual identity settings will automatically sync between theme and plugin.

== Screenshots ==

1. Settings panel — Adminbar customization with color schemes
2. Sidebar menu — Custom colors, logo, and drag-and-drop reordering
3. Global search — Cmd+K search across posts, pages, media, and more
4. Visual identity — Brand colors and design tokens configuration
5. Login page — Custom branded login screen
6. List tables — Modern styling with author avatars
7. Editor — Unified featured image, excerpt, and taxonomy meta box

== Changelog ==

= 1.3.1 =
* Added full internationalization (i18n) support with English as default language
* Added Chinese (zh_CN) translation
* Code cleanup for WordPress.org submission

= 1.3.0 =
* Added unified taxonomy box for managing categories, tags, and custom taxonomies
* Added post meta box enhancements (featured image + excerpt in one panel)
* Introduced FD Design Tokens shared library for consistent color system
* Added accent color to visual identity settings

= 1.2.0 =
* Added editor enhancements (merged TinyMCE toolbars, slash commands)
* Added post table of contents with auto-generation and navigation
* Added modern list table styling with horizontal scroll and author avatars
* Added footer customization (custom text, hide version)
* Added content area background color settings

= 1.1.0 =
* Added global search with Cmd+K / Ctrl+K keyboard shortcut
* Added login page customizer (background, logo, colors)
* Added dashboard widget management
* Added search settings panel
* Added icon picker component for menu customization

= 1.0.0 =
* Initial release
* Core admin UI framework with modern styling
* Admin bar customization (classic and modern modes)
* Sidebar menu management with drag-and-drop sorting
* Visual identity system with primary, secondary, and semantic colors
* Global border radius and font size settings
* Settings page with tabbed interface

== Upgrade Notice ==

= 1.3.1 =
Added internationalization support. The plugin now defaults to English with Chinese translation available.
