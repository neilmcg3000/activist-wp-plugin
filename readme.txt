=== Activist ===
Contributors: willscott
Tags: access, caching, block, censorship
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 4.4
License: BSD
License URI: https://opensource.org/licenses/BSD-3-Clause


Activate your site with client-side caching to maintain access to users even when your server is unavailable.

== Description ==
Activist adds fallback application-cache caching to your site, so that visitors who have accessed your site in the past are able to maintain access even when your server is unavailable. Full details at [activistjs.com](https://activistjs.com)

== Installation ==

1. Upload The `activist` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure behavior in the settings menu.

== Frequently Asked Questions ==

= How does it work? =

Activist uses both the application cache and the service worker APIs to make parts
of your site continue to work offline. It then uses a set of Javascript heuristics
to determine when a visitor is reaching the offline content because of censorship
from their ISP, and allows configuration of a custom message in those cases.

== Screenshots ==

1. Admin Configuration Menu.

== Changelog ==

= 0.1.1 =
* Update to wordpress 4.4
* Add assets
* Bug fixes
= 0.1.0 =
* Initial Listing
