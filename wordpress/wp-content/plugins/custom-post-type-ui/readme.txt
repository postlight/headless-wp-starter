=== Custom Post Type UI ===
Contributors: webdevstudios, pluginize, tw2113, vegasgeek, modemlooper, williamsba1
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3084056
Tags: custom post types, CPT, CMS, post, types, post type, taxonomy, tax, custom, content types, post types
Requires at least: 4.6
Tested up to: 4.9.4
Stable tag: 1.5.7
License: GPL-2.0+
Requires PHP: 5.2

Admin UI for creating custom post types and custom taxonomies for WordPress

== Description ==

Custom Post Type UI provides an easy to use interface for registering and managing custom post types and taxonomies for your website.

While CPTUI helps solve the problem of creating custom post types, displaying the data gleaned from them can be a whole new challenge. That’s why we created [Custom Post Type UI Extended](https://pluginize.com/product/custom-post-type-ui-extended/?utm_source=cptui-desription&utm_medium=text&utm_campaign=wporg). [View our Layouts page](https://pluginize.com/cpt-ui-extended-features/?utm_source=cptui-description-examples&utm_medium=text&utm_campaign=wporg) to see some examples that are available with Custom Post Type UI Extended.

Official development of Custom Post Type UI is on GitHub, with official stable releases published on WordPress.org. The GitHub repo can be found at [https://github.com/WebDevStudios/custom-post-type-ui](https://github.com/WebDevStudios/custom-post-type-ui). Please use the Support tab for potential bugs, issues, or enhancement ideas.

[Pluginize](https://pluginize.com/?utm_source=cptui&utm_medium=text&utm_campaign=wporg) was launched in 2016 by [WebDevStudios](https://webdevstudios.com/) to promote, support, and house all of their [WordPress products](https://pluginize.com/shop/?utm_source=cptui-&utm_medium=text&utm_campaign=wporg). Pluginize is not only [creating new products for WordPress all the time, like CPTUI Extended](https://pluginize.com/product/custom-post-type-ui-extended/?utm_source=cptui&utm_medium=text&utm_campaign=wporg), but also provides ongoing support and development for WordPress community favorites like [CMB2](https://wordpress.org/plugins/cmb2/) and more.

== Screenshots ==

1. Add new post type screen and tab.
2. Add new taxonomy screen and tab.
3. Registered post types and taxonomies from CPTUI
4. Import/Export Post Types screen.
5. Get Code screen.
6. Help/support screen.

== Changelog ==

= 1.5.7 - 2018-03-07 =
* Added: "output" added to blacklisted taxonomy slug list.
* Fixed: Prevent potential fatal error with customized links in plugin list page.
* Updated: Text at top of help area and readme description to promote available layouts in CPTUI-Extended.
* Updated: Things have been tested on the latest WordPress. You're in good hands.

= 1.5.6 - 2017-11-09 =
* Added: Added "custom_css", "customize_changeset", "author", and "post_type" as reserved post_types.
* Fixed: The "Invalid JSON" error message was receiving the wrong color indicator for for the admin notice.

= 1.5.5 - 2017-07-27 =
* Fixed: Prevent possible conflicts with .required css selector by prefixing ours.
* Fixed: Better accommodate possible labels with apostrophes, in relation to "Get code" functionality.

= 1.5.4 - 2017-06-22 =
* Fixed: Resolved saving issue around post types tha matched existing page slugs.
* Fixed: Layout issues on about page.

= 1.5.3 - 2017-03-29 =
* Fixed: Removed ability to rename post type and taxonomy slugs to reserved slugs after initial saving.
* Updated: Updated existing and added new, fancier side graphics.

= 1.5.2 - 2017-2-1 =
* Fixed: Chrome conflicts around the js used to sanitize post type and taxonomy slugs and cursors moving to end of input.
* Fixed: Further hardened undefined index notices and instance checks in our cptui_not_new_install() callback.
* Updated: Help text for post type and taxonomy slugs around the use of dashes. See http://docs.pluginize.com/article/135-dashes-in-post-type-taxonomy-slugs-for-url-seo
* Added: Clarification text regarding what the "Get code" section is useful for.

= 1.5.1 - 2017-1-17 =
* Fixed: Undefined index notice during update process for themes or plugins.
* Fixed: Blacklisted the word "include" from allowed taxonomy slugs. Causes menus to not show in WP Admin.
* Fixed: Blacklisted the word "fields" from allowed post type slugs. Causes pages to not show in WP Admin.
* Updated: Replaced hardcoded "manage_options" reference in our menu setup with variable holding filtered capability.

= 1.5.0 - 2017-1-10 =
* Added: Helper functions to grab individual post types or taxonomies from CPTUI options, function to check for support for custom saved values.
* Added: Helper functions to mark and check if a new CPTUI install.
* Added: FAQ clarifying why post type/taxonomy slugs are forced to underscores. We mean well, I assure you.
* Added: Conversion from Cyrillic characters to latin equivalents.
* Fixed: Parameter handling for get_terms() to match WordPress 4.5.
* Fixed: Added "action" as a reserved taxonomy name.
* Fixed: PHP Notices for rewrite array index, present since version 1.0.6
* Fixed: Prevent triggering post type/taxonomy slug convert when navigating screen via tab key.
* Fixed: Provide empty quote indicator in Registered Post Types and Taxonomies screen for empty values.
* Fixed: Post types and taxonomies no longer need extra page refresh to be registered after an import.
* Updated: Further evolved Registered Post Types and Taxonomies screen to better match list table styles.
* Updated: Bumped minimum required WordPress version to 4.6.
* Updated: Clarified what checking a checkbox does in regards to "Supports" area of post type settings.
* Updated: Changed appropriate help/support links to docs.pluginize.com.
* Updated: Added filter to tab collection for the tools section. You can now add your own tabs.

= 1.4.3 - 2016-10-17 =
* Fixed: issue with post types and taxonomies trying to be converted before registration. Prevented full success of process.
* Fixed: Prevent trying to convert taxonomy terms if no terms exist. Taxonomy will still be deleted from CPTUI list.
* Fixed: Prevent trying to redirect on activation if being network-activated.

= 1.4.2 - 2016-10-03 =
* Fixed: Responsiveness of sections and "ad" space when creating post types or taxonomies on smaller screens. Props @thecxguy

= 1.4.1 - 2016-8-25 =
* Fixed: issue with default values for new parameters regarding menu/nav menu display for taxonomies.
* Fixed: typo in support area.

= 1.4.0 - 2016-8-22 =
* Added: "Export" tab on editor screens for quick access to post type or taxonomy export pages.
* Added: CPTUI notices are now dismissable via a button on the right side.
* Added: "Get code" link to registered post types and registered taxonomies listings.
* Added: More amending of incorrect characters in post type and taxonomy slugs. Latin standard alphabet only. Sorry.
* Added: New post type template stack reference from recent WordPress versions.
* Added: Side warning notification if post type or taxonomy slug has been edited.
* Added: Display About page upon activation of plugin.
* Added: Link below ads regarding getting them removed via purchase of CPTUI Extended.
* Added: No need to refresh page after initial save to see post types and taxonomies in menu.
* Added: Taxonomy support for show_in_menu and show_in_nav_menus.
* Fixed: Further improved labels for information text on inputs.
* Fixed: Hide "choose icon" button for non-js users.
* Fixed: Issue with misused "parent" label key that should be parent_item_colon.
* Fixed: Missed show_in_menu_string parameter for "get code" area.
* Fixed: Make sure taxonomies have required post type associated.
* Fixed: "Edit" links in listings area now account for network-admin when needed, with CPTUI Extended.
* Updated: Switch to dedicated dashicon for color consistency between applied admin color schemes.
* Updated: Updated about page.
* Updated: Further UI refinements to better match WordPress admin. Adapted styles found from metaboxes, including collapse/expand toggles.

== Upgrade Notice ==

= 1.5.7 - 2018-03-07 =
* Added: "output" added to blacklisted taxonomy slug list.
* Fixed: Prevent potential fatal error with customized links in plugin list page.
* Updated: Text at top of help area and readme description to promote available layouts in CPTUI-Extended.
* Updated: Things have been tested on the latest WordPress. You're in good hands.

= 1.5.6 - 2017-11-09 =
* Added: Added "custom_css", "customize_changeset", "author", and "post_type" as reserved post_types.
* Fixed: The "Invalid JSON" error message was receiving the wrong color indicator for for the admin notice.

= 1.5.5 - 2017-07-27 =
* Fixed: Prevent possible conflicts with .required css selector by prefixing ours.
* Fixed: Better accommodate possible labels with apostrophes, in relation to "Get code" functionality.

= 1.5.4 - 2017-06-22 =
* Fixed: Resolved saving issue around post types tha matched existing page slugs.
* Fixed: Layout issues on about page.

= 1.5.3 - 2017-03-29 =
* Fixed: Removed ability to rename post type and taxonomy slugs to reserved slugs after initial saving.
* Updated: Updated existing and added new, fancier side graphics.

= 1.5.2 - 2017-2-1 =
* Fixed: Chrome conflicts around the js used to sanitize post type and taxonomy slugs and cursors moving to end of input.
* Fixed: Further hardened undefined index notices and instance checks in our cptui_not_new_install() callback.
* Updated: Help text for post type and taxonomy slugs around the use of dashes. See http://docs.pluginize.com/article/135-dashes-in-post-type-taxonomy-slugs-for-url-seo
* Added: Clarification text regarding what the "Get code" section is useful for.

= 1.5.1 - 2017-1-17 =
* Fixed: Undefined index notice during update process for themes or plugins.
* Fixed: Blacklisted the word "include" from allowed taxonomy slugs. Causes menus to not show in WP Admin.
* Fixed: Blacklisted the word "fields" from allowed post type slugs. Causes pages to not show in WP Admin.
* Updated: Replaced hardcoded "manage_options" reference in our menu setup with variable holding filtered capability.

= 1.5.0 - 2017-1-10 =
* Added: Helper functions to grab individual post types or taxonomies from CPTUI options, function to check for support for custom saved values.
* Added: Helper functions to mark and check if a new CPTUI install.
* Added: FAQ clarifying why post type/taxonomy slugs are forced to underscores. We mean well, I assure you.
* Fixed: Parameter handling for get_terms() to match WordPress 4.5.
* Fixed: Added "action" as a reserved taxonomy name.
* Fixed: PHP Notices for rewrite array index, present since version 1.0.6
* Fixed: Prevent triggering post type/taxonomy slug convert when navigating screen via tab key.
* Fixed: Provide empty quote indicator in Registered Post Types and Taxonomies screen for empty values.
* Fixed: Post types and taxonomies no longer need extra page refresh to be registered after an import.
* Updated: Further evolved Registered Post Types and Taxonomies screen to better match list table styles.
* Updated: Bumped minimum required WordPress version to 4.6.
* Updated: Clarified what checking a checkbox does in regards to "Supports" area of post type settings.
* Updated: Changed appropriate help/support links to docs.pluginize.com.
* Updated: Added filter to tab collection for the tools section. You can now add your own tabs.

= 1.4.3 - 2016-10-17 =
* Fixed: issue with post types and taxonomies trying to be converted before registration. Prevented full success of process.
* Fixed: Prevent trying to convert taxonomy terms if no terms exist. Taxonomy will still be deleted from CPTUI list.
* Fixed: Prevent trying to redirect on activation if being network-activated.

= 1.4.2 - 2016-10-03 =
* Fixed: Responsiveness of sections and "ad" space when creating post types or taxonomies on smaller screens. Props @thecxguy

= 1.4.1 - 2016-8-25 =
* Fixed: issue with default values for new parameters regarding menu/nav menu display for taxonomies.
* Fixed: typo in support area.

= 1.4.0 - 2016-8-22 =
* Added: "Export" tab on editor screens for quick access to post type or taxonomy export pages.
* Added: CPTUI notices are now dismissable via a button on the right side.
* Added: "Get code" link to registered post types and registered taxonomies listings.
* Added: More amending of incorrect characters in post type and taxonomy slugs. Latin standard alphabet only. Sorry.
* Added: New post type template stack reference from recent WordPress versions.
* Added: Side warning notification if post type or taxonomy slug has been edited.
* Added: Display About page upon activation of plugin.
* Added: Link below ads regarding getting them removed via purchase of CPTUI Extended.
* Added: No need to refresh page after initial save to see post types and taxonomies in menu.
* Added: Taxonomy support for show_in_menu and show_in_nav_menus.
* Fixed: Further improved labels for information text on inputs.
* Fixed: Hide "choose icon" button for non-js users.
* Fixed: Issue with misused "parent" label key that should be parent_item_colon.
* Fixed: Missed show_in_menu_string parameter for "get code" area.
* Fixed: Make sure taxonomies have required post type associated.
* Fixed: "Edit" links in listings area now account for network-admin when needed, with CPTUI Extended.
* Updated: Switch to dedicated dashicon for color consistency between applied admin color schemes.
* Updated: Updated about page.
* Updated: Further UI refinements to better match WordPress admin. Adapted styles found from metaboxes, including collapse/expand toggles.

== Installation ==

= Admin Installer via search =
1. Visit the Add New plugin screen and search for "custom post type ui".
2. Click the "Install Now" button.
3. Activate the plugin.
4. Navigate to the "CPTUI" Menu.

= Admin Installer via zip =
1. Visit the Add New plugin screen and click the "Upload Plugin" button.
2. Click the "Browse..." button and select zip file from your computer.
3. Click "Install Now" button.
4. Once done uploading, activate Custom Post Type UI.

= Manual =
1. Upload the Custom Post Type UI folder to the plugins directory in your WordPress installation.
2. Activate the plugin.
3. Navigate to the "CPTUI" Menu.

That's it! Now you can easily start creating custom post types and taxonomies in WordPress.

== Frequently Asked Questions ==

#### User documentation
Please see http://docs.pluginize.com/category/126-custom-post-type-ui

#### Code/API documentation
Please see http://codex.pluginize.com/cptui/
