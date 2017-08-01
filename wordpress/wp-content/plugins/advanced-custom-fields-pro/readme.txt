=== Advanced Custom Fields Pro ===
Contributors: elliotcondon
Tags: acf, advanced, custom, field, fields, custom field, custom fields, simple fields, magic fields, more fields, repeater, edit
Requires at least: 3.6.0
Tested up to: 4.8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Customise WordPress with powerful, professional and intuitive fields

== Description ==

Advanced Custom Fields is the perfect solution for any WordPress website which needs more flexible data like other Content Management Systems. 

* Visually create your Fields
* Select from multiple input types (text, textarea, wysiwyg, image, file, page link, post object, relationship, select, checkbox, radio buttons, date picker, true / false, repeater, flexible content, gallery and more to come!)
* Assign your fields to multiple edit pages (via custom location rules)
* Easily load data through a simple and friendly API
* Uses the native WordPress custom post type for ease of use and fast processing
* Uses the native WordPress metadata for ease of use and fast processing

= Field Types =
* Text (type text, api returns text)
* Text Area (type text, api returns text)
* Number (type number, api returns integer)
* Email (type email, api returns text)
* Password (type password, api returns text)
* WYSIWYG (a WordPress wysiwyg editor, api returns html)
* Image (upload an image, api returns the url)
* File (upload a file, api returns the url)
* Select (drop down list of choices, api returns chosen item)
* Checkbox (tickbox list of choices, api returns array of choices)
* Radio Buttons ( radio button list of choices, api returns chosen item)
* True / False (tick box with message, api returns true or false)
* Page Link (select 1 or more page, post or custom post types, api returns the selected url)
* Post Object (select 1 or more page, post or custom post types, api returns the selected post objects)
* Relationship (search, select and order post objects with a tidy interface, api returns the selected post objects)
* Taxonomy (select taxonomy terms with options to load, display and save, api returns the selected term objects)
* User (select 1 or more WP users, api returns the selected user objects)
* Google Maps (interactive map, api returns lat,lng,address data)
* Date Picker (jquery date picker, options for format, api returns string)
* Color Picker (WP color swatch picker)
* Tab (Group fields into tabs)
* Message (Render custom messages into the fields)
* Repeater (ability to create repeatable blocks of fields!)
* Flexible Content (ability to create flexible blocks of fields!)
* Gallery (Add, edit and order multiple images in 1 simple field)
* [Custom](https://www.advancedcustomfields.com/resources/tutorials/creating-a-new-field-type/) (Create your own field type!)

= Tested on =
* Mac Firefox 	:)
* Mac Safari 	:)
* Mac Chrome	:)
* PC Safari 	:)
* PC Chrome		:)
* PC Firefox	:)
* iPhone Safari :)
* iPad Safari 	:)
* PC ie7		:S

= Website =
https://www.advancedcustomfields.com/

= Documentation =
* [Getting Started](https://www.advancedcustomfields.com/resources/#getting-started)
* [Field Types](https://www.advancedcustomfields.com/resources/#field-types)
* [Functions](https://www.advancedcustomfields.com/resources/#functions)
* [Actions](https://www.advancedcustomfields.com/resources/#actions)
* [Filters](https://www.advancedcustomfields.com/resources/#filters)
* [How to guides](https://www.advancedcustomfields.com/resources/#how-to)
* [Tutorials](https://www.advancedcustomfields.com/resources/#tutorials)

= Bug Submission and Forum Support =
http://support.advancedcustomfields.com/

= Please Vote and Enjoy =
Your votes really make a difference! Thanks.


== Installation ==

1. Upload 'advanced-custom-fields' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on the new menu item "Custom Fields" and create your first Custom Field Group!
4. Your custom field group will now appear on the page / post / template you specified in the field group's location rules!
5. Read the documentation to display your data: 


== Frequently Asked Questions ==

= Q. I have a question =
A. Chances are, someone else has asked it. Check out the support forum at: 
http://support.advancedcustomfields.com/


== Screenshots ==

1. Creating the Advanced Custom Fields

2. Adding the Custom Fields to a page and hiding the default meta boxes

3. The Page edit screen after creating the Advanced Custom Fields

4. Simple and intuitive API. Read the documentation at: https://www.advancedcustomfields.com/resources/


== Changelog ==

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