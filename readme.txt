=== WPMU Contextual Related Posts ===
Contributors: alexbilbie
Tags: related, wpmu, posts, blogs
Requires at least: Wordpress MU 2.8
Tested up to: Wordpress MU 2.8.6

Creates relationships between Wordpress MU blog posts based on user defined tags, relevant Open Calais tags and extracted terms.

== Description ==

Creates relationships between Wordpress MU blog posts based on user defined tags, relevant Open Calais tags and extracted terms.

It requires an Open Calais (http://www.opencalais.com/) API key and a Yahoo Term Extraction API key (http://developer.yahoo.com/search/content/V1/termExtraction.html)

This plugin was created for the JISC (http://jisc.ac.uk) funded JISCPress project (http://jiscpress.blogs.lincoln.ac.uk)

== Installation ==

1. Place this plugin in your /wp-content/plugins/ folder.
2. Activate the plugin sitewide
3. In the Site Admin panel, click "WPMU Related Posts" to configure.
4. Activate widgets on the blogs you wish to display related blogs/posts

== Frequently Asked Questions ==

= Can I exclude certain blogs from being tagged? =

Yes.

= Do I have to use Open Calais / Yahoo Term Extraction =

No this plugin will work fine with user submitted tags.

== Screenshots ==

== Changelog ==

= 1.0 =
* Initial release

= 1.0.1 =
* Bug fix

= 1.0.2 =
* Added missing uninstall function
* Code tidy up + documentation

= 1.0.3 =
* Fixed installation error
* Minor bug fixes
* Speed improvements

= 1.0.4 =
* Fixed installation error introduced in 1.0.3

= 1.0.5 =
* Fix for broken widget under certain conditions
* Renamed the widget

= 1.0.6 =
* Fix the widget layout to better fit in with other widgets for easier styling

= 1.0.7 =
* Widget bug fix

= 1.0.8 =
* Fixed error introduced in 1.0.7

= 1.0.9 =
* Rewrote some core functions for increased efficiency and error checking

= 1.1 =
* Re-wrote the cron jobs to better catch errors