=== Scrape and Debug ===
Contributors: MissionMike
Tags: debug, facebook, google, open graph, w3c, validator, structured data, html, pagespeed, amp
Donate link: https://www.missionmike.dev/scrape-and-debug-wordpress-plugin/
Requires at least: 2.8
Tested up to: 5.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simplify page debugging via Facebook Sharing Debugger, LinkedIn Post Inspector, Google's Structured Data Testing Tool and Rich Results Test, PageSpeed Insights, W3C Validation, and Google AMP Test.

== Description ==
#About Scrape && Debug

Preview your posts and pages on social media. Validate your pages' HTML with W3C's Validator, or check PageSpeed with Google's PageSpeed Insights. Access these tools quickly from within wp-admin, without needing to copy/paste multiple URLs each time to see your results. This utility can seriously speed up your publishing process, especially if you're concerned with how your new posts appear on social media.

This plugin was created to speed up SEO and Social Media testing via public tools provided by Google, W3C, Facebook, and LinkedIn. Found in page/post sidebar metabox. Some services (like Facebook's Sharing Debugger) require an account with the respective service to use (i.e., a free Facebook account).

Scrape and Debug does **not** allow you to change any meta titles, keywords, open graph data, etc. It is only providing quick-links to debug the existing data and values set for those fields by other 3rd-party SEO plugins, or to test your general page speed and HTML validity.

Links to quickly debug and check posts are found in the sidebar when editing posts, as well as in a column (Quicklinks) when listing posts or pages in admin.

In Settings, debuggers can be shown or hidden, or omitted from particular post types.


== Installation ==
Download zip, install, activate!

== Frequently Asked Questions ==
N/A

=v0.5.3=

* Force loading of new admin styles in settings page

=v0.5.2=

* Improved admin UI
* Added notification bar on activation directing user to settings page if no saved settings are found

=v0.5.1=

* Added support for LinkedIn Post Inspector

=v0.5.0=

* Add new Google Rich Results Test support
* Deprecate Google Structured Data Testing tool
* Style updates
* Code cleanup

== Changelog ==

=v0.4.3=

* Code cleanup

=v0.3=

* Added Google AMP debugger. If page/post has valid <link rel="amphtml" src="..."> tag, Google's AMP Test will request to validate it. Check Scrape and Debug settings to show the AMP Test option.

=v0.2.2=

* Set WP page and post types to "display" in settings by default (user still needs to save settings upon activation, but they're auto-checked for quick saving)

=v0.2.1=

* Removed recurring 'check settings' notice

=v0.2=

* Reworded confusing settings page instructions and changed toggle settings to show/hide
* Fixed undefined variable warnings and PHP notices in wp-admin

=v0.1.1=

* Fixed CSS version parameter to force stylesheet updates.

=v0.1=

* Added quicklinks to posts/pages and custom post type columns for faster debug checking of multiple posts

=v0.0.4=

* Fixed "Notice: Trying to get property of non-object in dts-debugger.php on line 224" notice (thrown when WP_DEBUG is set to true in wp-config.php)
* Fixed "Notice: Undefined variable: post in dts-debugger.php on line 224" notice (thrown when WP_DEBUG is set to true in wp-config.php)

=v0.0.3=

* Removed immediate redirect upon activation
* Set default values on activation (show all debuggers, show on all public post types)
* Added settings to hide particular debuggers
* Improved settings page (add icons)
* Misc. bugfixes

=v0.0.2=

* Unescape apostrophes in readme.txt

=v0.0.1=

* Added dts-debugger.php, styles.css
* Added site icons





