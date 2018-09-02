=== Authors By Latest Post ===

Contributors: mayukojpn
Tags: author, archive,
Requires at least: 4.6
Tested up to: 4.9.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display writer list of your blog in order of their last published date and time.

== Description ==

Display writer list of your blog in order of their last published date and time.

Author list will be called by using shortcode `authors_by_latest_post`.

This plugin will modify and get [`users`](https://developer.wordpress.org/rest-api/reference/users/) end point of WP REST API v2. Make sure your WP API is enabled on your site.

Author card's UI supports only Japanese for now.

== Shortcode Usage ==

shortcode `authors_by_latest_post` supports these options:

* `per_page`: Accepts numbers. Author display number for one page or loading.
* `max_column`: Accepts 2 or 3. Maximum column number for wide screen.
* `infinite`: Default is `0`. If you set `1`, the rest of author card will be loaded when user scholl down to the end of author list.

== Screenshots ==

1. Shortcode example.
