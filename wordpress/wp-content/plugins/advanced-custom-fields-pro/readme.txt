=== Advanced Custom Fields Pro ===
Contributors: elliotcondon
Tags: acf, advanced, custom, field, fields, form, repeater, content
Requires at least: 3.6.0
Tested up to: 4.9.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Customise WordPress with powerful, professional and intuitive fields.

== Description ==

Use the Advanced Custom Fields plugin to take full control of your WordPress edit screens & custom field data.

**Add fields on demand.** Our field builder allows you to quickly and easily add fields to WP edit screens with only the click of a few buttons!

**Add them anywhere.** Fields can be added all over WP including posts, users, taxonomy terms, media, comments and even custom options pages!

**Show them everywhere.** Load and display your custom field values in any theme template file with our hassle free developer friendly functions!

= Features =
* Simple & Intuitive
* Powerful Functions
* Over 30 Field Types
* Extensive Documentation
* Millions of Users

= Links =
* [Website](https://www.advancedcustomfields.com)
* [Documentation](https://www.advancedcustomfields.com/resources/)
* [Support](https://support.advancedcustomfields.com)
* [ACF PRO](https://www.advancedcustomfields.com/pro/)

= PRO =
The Advanced Custom Fields plugin is also available in a professional version which includes more fields, more functionality, and more flexibility! [Learn more](https://www.advancedcustomfields.com/pro/)


== Installation ==

From your WordPress dashboard

1. **Visit** Plugins > Add New
2. **Search** for "Advanced Custom Fields"
3. **Activate** Advanced Custom Fields from your Plugins page
4. **Click** on the new menu item "Custom Fields" and create your first Custom Field Group!
5. **Read** the documentation to [get started](https://www.advancedcustomfields.com/resources/getting-started-with-acf/)


== Frequently Asked Questions ==

= What kind of support do you provide? =

**Help Desk.** Support is currently provided via our email help desk. Questions are generally answered within 24 hours, with the exception of weekends and holidays. We answer questions related to ACF, it’s usage and provide minor customization guidance. We cannot guarantee support for questions which include custom theme code, or 3rd party plugin conflicts & compatibility. [Open a Support Ticket](http://support.advancedcustomfields.com/new-ticket/)

**Support Forums.** Our Community Forums provide a great resource for searching and finding previously answered and asked support questions. You may create a new thread on these forums, however, it is not guaranteed that you will receive an answer from our support team. This is more of an area for developers to talk to one another, post ideas, plugins and provide basic help. [View the Support Forum](http://support.advancedcustomfields.com)


== Screenshots ==

1. Simple & Intuitive

2. Made for developers

3. All about fields


== Changelog ==

= 5.7.0 =
* Core: Major JavaScript updates
* Core: Improved conditional logic with new types and more supported fields
* Core: Improved localization and internationalization
* Repeater field: Improved logic that remembers collapsed row states
* Repeater field: Added support to collapse multiple rows (hold shift)
* API: Improved lookup to find fields without a reference value
* Language: Added Croatian translation - Thanks to Vlado Bosnjak
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Updated Romanian translation - thanks to Ionut Staicu
* Language: Updated German translation - thanks to Ralf Koller
* Language: Updated Arabic translation - thanks to Karim Ramadan
* Language: Updated Portuguese translation - thanks to Pedro Mendonça

= 5.6.10 =
* Core: Minor fixes and improvements

= 5.6.9 =
* User field: Added new 'Return Format' setting (Array, Object, ID)
* Core: Added basic compatibility with Gutenberg - values now save
* Core: Fixed bug affecting the loading of fields on new Menu Items
* Core: Removed private ('show_ui' => false) post types from the 'Post Type' location rule choices
* Core: Minor fixes and improvements
* Language: Updated French translation - thanks to Maxime Bernard-Jacquet

= 5.6.8 =
* API: Fixed bug causing have_rows() to fail with PHP 7.2
* Core: Fixed bug causing "Add new term" form to hide after submit
* Core: Minor fixes and improvements
* Language: Updated German translation - thanks to Ralf Koller
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Arabic translation - thanks to Karim Ramadan
* Language: Updated Spanish translation - thanks to Luis Rull Muñoz
* Language: Updated Persian translation - thanks to Majix

= 5.6.7 =
* Fixed an assortment of bugs found in 5.6.6

= 5.6.6 =
* Accordion field: Added new field type
* Tab field: Added logic to remember active tabs
* WYSIWYG field: Fixed JS error in quicktags initialization
* Core: Fixed issue preventing conditional logic for menu item fields
* Core: Fixed issue preventing JS initialization for newly added menu items.
* Core: Allow whitespace in input value (previously trimmed)
* Core: Minor fixes and improvements
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Updated Brazilian Portuguese translation - thanks to Rafael Ribeiro
* Language: Updated Dutch translation - thanks to Derk Oosterveld
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Persian translation - thanks to Kamel Kimiaei
* Language: Updated Swiss German translation - thanks to Raphael Hüni
* Language: Updated Arabic translation - thanks to Karim Ramadan

= 5.6.5 =
* API: Added new 'kses' setting to the `acf_form()` function
* Core: Added new 'Admin Tools' framework (includes design refresh)
* Core: Minor fixes and improvements
* Language: Update Ukrainian translation - thanks to Jurko Chervony
* Language: Update Russian translation - thanks to Andriy Toniyevych
* Language: Update Hebrew translation - thanks to Itamar Megged

= 5.6.4 =
* Google Map field: Fixed bug causing invalid url to JavaScript library
* WYSIWYG field: Fixed minor z-index and drag/drop bugs
* Group field: Fixed bug causing incorrect export settings
* Core: Fixed bug in 'Post Taxonomy' location rule ignoring selected terms during AJAX callback
* Core: Fixed bug preventing a draft to validate with required fields
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Update Turkish translation - thanks to Emre Erkan
* Language: Updated Chinese translation - thanks to Wang Hao
* Language: Update Hebrew translation - thanks to Itamar Megged

= 5.6.3 =
* Button Group field: Added new field type
* Range field: Added missing 'step' attribute to number input
* Range field: Added width to number input based on max setting
* Basic fields: Added missing 'required' attribute to inputs
* Basic fields: Removed empty attributes from inputs
* API: Fixed `get_fields()` bug ignoring fields starting with an underscore
* Core: Minor fixes and improvements
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated French translation - thanks to Maxime Bernard-Jacquet
* Language: Updated Finnish translation - thanks to Sauli Rajala
* Language: Updated German translation - thanks to Ralf Koller

= 5.6.2 =
* Range field: Added new field type
* Clone field: Fixed bug causing value update issues for 'seamless' + widgets / nave menu items
* Location: Added parent theme's post templates to 'post template' location rule
* Location: Fixed bug causing 'nav menu' location rule to fail during AJAX (add new item)
* Core: Fixed PHP errors in customizer when editing non ACF panels
* Core: Fixed bug casing backslash character to break fields / field groups
* Core: Many minor bug fixes
* Language: Updated Romanian translation - thanks to Ionut Staicu
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Update Turkish translation - thanks to Emre Erkan
* Language: Updated Russian translation - Thanks to Алекс Яровиков
* Language: Updated French translation - Thanks to Julie Arrigoni

= 5.6.1 =
* Fixed an assortment of bugs found in 5.6.0

= 5.6.0 =
* Link field: Added new field type
* Group field: Added new field type
* API: Improved `have_rows()` function to work with clone and group field values
* Core: Added new location for Menus
* Core: Added new location for Menu Items
* Core: Added types to Attachment location rule - thanks to Jan Thomas
* Core: Added "Confirm Remove" tooltips
* Core: Updated Select2 JS library to v4
* Core: Minor fixes and improvements

= 5.5.14 =
* Core: Minor bug fixes

= 5.5.13 =
* Clone field: Improved 'Fields' setting to show all fields within a matching field group search
* Flexible Content field: Fixed bug causing 'layout_title' filter to fail when field is cloned
* Flexible Content field: Added missing 'translate_field' function
* WYSIWYG field: Fixed JS error when using CKEditor plugin
* Date Picker field: Improved 'Display Format' and 'Return Format' settings UI
* Time Picker field: Same as above
* Datetime Picker field: Same as above
* Core: Added new 'remove_wp_meta_box' setting
* Core: Added constants ACF, ACF_PRO, ACF_VERSION and ACF_PATH
* Core: Improved compatibility with Select2 v4 including sortable functionality
* Language: Updated Portuguese translation - thanks to Pedro Mendonça

= 5.5.12 =
* Tab field: Allowed HTML within field label to show in tab
* Core: Improved plugin update class
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Brazilian Portuguese translation - thanks to Rafael Ribeiro

= 5.5.11 =
* Google Map field: Added new 'google_map_init' JS action
* Core: Minor fixes and improvements
* Language: Updated Swiss German translation - thanks to Raphael Hüni
* Language: Updated French translation - thanks to Maxime Bernard-Jacquet

= 5.5.10 =
* API: Added new functionality to the `acf_form()` function:
* - added new 'html_updated_message' setting
* - added new 'html_submit_button' setting
* - added new 'html_submit_spinner' setting
* - added new 'acf/pre_submit_form' filter run when form is successfully submit (before saving $_POST)
* - added new 'acf/submit_form' action run when form is successfully submit (after saving $_POST)
* - added new '%post_id%' replace string to the 'return' setting
* - added new encryption logic to prevent $_POST exploits
* - added new `acf_register_form()` function
* Core: Fixed bug preventing values being loaded on a new post/page preview
* Core: Fixed missing 'Bulk Actions' dropdown on sync screen when no field groups exist
* Core: Fixed bug ignoring PHP field groups if exists in JSON
* Core: Minor fixes and improvements

= 5.5.9 =
* Core: Fixed bug causing ACF4 PHP field groups to be ignored if missing ‘key’ setting

= 5.5.8 =
* Flexible Content: Added logic to better 'clean up' data when re-ordering layouts
* oEmbed field: Fixed bug causing incorrect width and height settings in embed HTML
* Core: Fixed bug causing incorrect Select2 CSS version loading for WooCommerce 2.7
* Core: Fixed bug preventing 'min-height' style being applied to floating width fields
* Core: Added new JS 'init' actions for wysiwyg, date, datetime, time and select2 fields
* Core: Minor fixes and improvements

= 5.5.7 =
* Core: Fixed bug causing `get_field()` to return incorrect data for sub fields registered via PHP code.

= 5.5.6 =
* Core: Fixed bug causing license key to be ignored after changing url from http to https
* Core: Fixed Select2 (v4) bug where 'allow null' setting would not correctly save empty value
* Core: Added new 'acf/validate_field' filter
* Core: Added new 'acf/validate_field_group' filter
* Core: Added new 'acf/validate_post_id' filter
* Core: Added new 'row_index_offset' setting
* Core: Fixed bug causing value loading issues for a taxonomy term in WP < 4.4
* Core: Minor fixes and improvements

= 5.5.5 =
* File field: Fixed bug creating draft post when saving an empty value
* Image field: Fixed bug mentioned above

= 5.5.4 =
* File field: Added logic to 'connect' selected attachment to post (only if attachment is not 'connected')
* File field: Removed `filesize()` call causing performance issues with externally hosted attachments
* File field: Added AJAX validation to 'basic' uploader
* Image field: Added 'connect' logic mentioned above
* Image field: Added AJAX validation mentioned above
* True false field: Improved usability by allowing 'tab' key to focus element (use space or arrow keys to toggle)
* Gallery field: Fixed bug causing unsaved changes in sidebar to be lost when selecting another attachment
* API: Fixed `add_row()` and `add_sub_row()` return values (from true to new row index)
* Core: Improved `get_posts()` query speeds by setting 'update_cache' settings to false
* Core: Allowed 'instruction_placement' setting on 'widget' forms (previously set always to 'below fields')
* Core: Removed 'ACF PRO invalid license nag' and will include fix for 'protocol change' in next release
* Language: Updated French translation - thanks to Martial Parfait

= 5.5.3 =
* Options page: Fixed bug when using WPML in multiple tabs causing incorrect 'lang' to be used during save.
* Core: Added support with new `get_user_locale()` setting in WP 4.7
* Core: Improved efficiency of termmeta DB upgrade logic
* Core: Minor fixes and improvements

= 5.5.2 =
* Tab field: Fixed bug causing value loading issues for field's with the same name
* Repeater field: Fixed bug in 'collapsed' setting where field key was shown instead of field label

= 5.5.1 =
* Select field: Fixed bug preventing some field settings from being selected
* Date picker field: Improved compatibility with customised values
* Core: Added new 'enqueue_datepicker' setting which can be used to prevent the library from being enqueued
* Core: Added new 'enqueue_datetimepicker' setting which can be used to prevent the library from being enqueued
* Core: Minor fixes and improvements

= 5.5.0 =
* True False field: Added new 'ui' setting which renders as a toggle switch
* WYSIWYG field: Added new 'delay' setting which delays tinymce initialization until the field is clicked
* WYSIWYG field: Added compatibility for WP 4.7 toolbar buttons order
* Checkbox field: Added new 'allow_custom' and 'save_custom' settings allowing you to add custom choices
* Select field: Fixed bug where Select2 fields did not correctly use the ‘allow null’ setting
* Clone field: Fixed bug causing save/load issues found when 2 sub fields clone in the same field/group.
* Flexible Content field: Improved popup style and validation messages
* Google Map field: Prevent scroll zoom
* Date picker field: Added better compatibility logic for custom 'date_format' setting found in version < 5.0.0
* API: acf_form() 'id' setting is now used as 'id' attribute in <form> element
* Options page: Fixed incorrect redirect URL from a sub options page
* Field group: Added new 'post_template' location rule (requires WP 4.7)
* Core: Added support for the wp_termmeta table (includes DB upgrade)
* Core: Added new 'select_2_version' setting which can be changed between 3 and 4
* Core: Added new 'enqueue_select2' setting which can be used to prevent the library from being enqueued
* Core: Added new 'enqueue_google_maps' setting which can be used to prevent the library from being enqueued
* Core: Minor fixes and improvements
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Norwegian translation - thanks to Havard Grimelid
* Language: Updated Swedish translation - thanks to Jonathan de Jong
* Language: Updated German translation - thanks to Ralf Koller
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Updated Swiss German translation - thanks to Raphael Hüni

= 5.4.8 =
* Flexible Content field: Fixed bug in 'layout_title' filter preventing values being loaded correctly

= 5.4.7 =
* Time Picker field: Fixed bug preventing default time from being selected
* Date Picker field: Improved compatibility with unix timestamp values
* File field: Fixed validation bugs when used as a sub field (multiple selection)
* Select field: Fixed bug incorrectly allowing a disabled field (hidden by conditional logic) to save values
* API: Added new `add_sub_row()` function
* API: Added new `update_sub_row()` function
* API: Added new `delete_sub_row()` function
* Core: Fixed bug causing 'sync' issues with sub clone fields
* Core: Minor fixes and improvements

= 5.4.6 =
* Gallery field: Fixed bug where open sidebar fields were saved to post
* Flexible Content field: Fixed bug causing Google map render issue within collapsed layout
* Flexible Content field: Fixed bug during 'duplicate layout' where radio input values were lost
* API: Fixed bug causing `get_row(true)` to return incorrect values
* Core: Fixed bug where preview values did not load for a draft post
* Core: Added notice when PRO license fails to validate URL
* Core: Fixed bug where conditional logic would incorrectly enable select elements
* Core: Minor fixes and improvements

= 5.4.5 =
* API: Fixed bug in `acf_form()` where AJAX validation ignored 'post_title'
* API: Improved `update_field()` when saving a new value (when reference value does not yet exist)
* Core: Added search input & toggle to admin field groups list
* Core: Fixed bug where preview values did not load for a draft post

= 5.4.4 =
* WYSIWYG field: Fixed JS error when 'Disable the visual editor when writing' is checked

= 5.4.3 =
* WYSIWYG field: Fixed JS bug (since WP 4.6) causing conflicts with editor plugins
* Google Maps field: Fixed JS error conflict with Divi theme
* Radio field: Fixed bug (Chrome only) ignoring default values in cloned sub fields
* Core: Fixed `wp_get_sites()` deprecated error (since WP 4.6) shown in network admin

= 5.4.2 =
* API: Fixed bug preventing post_title and post_content values saving in `acf_form()`

= 5.4.1 =
* API: Fixed bug causing `get_fields('options')` to return false
* Core: Fixed bug causing `get_current_screen()` to throw PHP error
* Core: Fixed bug causing 'Preview Post' to load empty field values

= 5.4.0 =
* Clone field: Added new field type (https://www.advancedcustomfields.com/resources/clone/)
* Gallery field: Removed 'Preview Size' setting and improved UI
* Taxonomy field: Added compatibility to save/load terms to user object
* Select field: Added new 'Return Format' setting
* Radio field: Added new 'Return Format' setting
* Checkbox field: Added new 'Return Format' setting
* Page link field: Added new 'Allow Archives URLs' setting
* Core: Fixed plugin update bug delaying updates
* Core: Fixed bug when editing field settings in Chrome causing required setting to self toggle
* Core: Improved speed and fixed bugs when creating and restoring revisions
* Core: Minor fixes and improvements
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Brazilian Portuguese translation - thanks to Augusto Simão
* Language: Updated Dutch translation - thanks to Derk Oosterveld
* Language: Updated Persian translation - thanks to Kamel
* Language: Updated German translation - thanks to Ralf Koller
* Language: Updated Swiss German translation - thanks to Raphael Hüni

View full changelog: https://www.advancedcustomfields.com/changelog/

== Upgrade Notice ==

= 5.2.7 =
* Field class names have changed slightly in v5.2.7 from `field_type-{$type}` to `acf-field-{$type}`. This change was introduced to better optimise JS performance. The previous class names can be added back in with the following filter: https://www.advancedcustomfields.com/resources/acfcompatibility/

= 3.0.0 =
* Editor is broken in WordPress 3.3

= 2.1.4 =
* Adds post_id column back into acf_values