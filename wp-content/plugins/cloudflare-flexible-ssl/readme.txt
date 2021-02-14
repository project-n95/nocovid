=== Flexible SSL for CloudFlare ===
Contributors: onedollarplugin, paultgoodchild
Donate link: https://icwp.io/cloudflaresslpluginauthor
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: CloudFlare, SSL, Flexible SSL, Universal SSL, redirect loop, HTTPS, HTTP_X_FORWARDED_PROTO
Requires at least: 3.2.0
Requires PHP: 5.2
Recommended PHP: 7.0
Tested up to: 5.6
Stable tag: 1.3.1

Fix For Redirect Loops on WordPress with CloudFlare's Flexible/Universal SSL.

== Description ==

[Click For Full Implementation Guide](https://icwp.io/6z).

Using CloudFlare® Flexible SSL on WordPress isn't as simple as just turning it on.

This plugin forms an **integral part** to enabling Flexible SSL on WordPress and prevents infinite redirect loops when loading WordPress sites under Cloudflare's Flexible SSL system.

*Cloudflare is a registered trademark of Cloudflare, Inc.*

One Dollar Plugin is not affiliated in any way with Cloudflare, Inc. This plugin provided separately and completely independently.

Remember: This plugin is just part of the installation process for Flexible SSL. [Please follow the full guide](https://icwp.io/6z)

== Frequently Asked Questions ==

= Does this plugin affect non-SSL traffic? =

No. It only comes into play when Cloudflare is serving HTTPS traffic from your site.

= Should I change my WordPress Site URL to HTTPS? =

No - there is no need.  Use Cloudflare's pages rules to redirect your visitors.  You can then safely turn off SSL whenever you want from within Cloudflare and your WordPress site will still load on HTTP.

= What happens if I disable this plugin AFTER enabling Flexible SSL on Cloudflare? =

Your WordPress site will not load on HTTPS/SSL. You will create an infinite loop loading problem and the only way to solve this is to turn off SSL redirects within Cloudflare.

= Does I need this plugin if I use Cloudflare FULL or Strict SSL? =

No. It is designed only to assist with Flexible SSL.

== Screenshots ==

n/a

== Installation ==

For full installation instructions, please review the following article: [Installation Instructions](https://icwp.io/6z).

= 1.3.1

UPDATED:	Adjusting plugin naming for Trademark woes.

= 1.3.0 =

UPDATED:	Update supported WordPress version to 5.1.
CHANGED:	Code adjustments to only process Flexible SSL in the case where it's not already SSL.

= 1.2.2 =

UPDATED:	Supported WordPress Version to v4.5
== Changelog ==

= 1.2.1 =

FIXED:		Checking to ensure certain data types

= 1.2.0 =

ADDED:		The plugin will try to set itself to load first, before all other plugins.

= 1.1.0 =

UPDATED:	Supported WordPress Version
UPDATED:	Also works for any standard SSL Proxy scenario that uses HTTP_X_FORWARDED_PROTO - it doesn't have to Cloudflare

= 1.0.0 =

First Release

== Upgrade Notice ==

= 1.0.0 =

First Release

