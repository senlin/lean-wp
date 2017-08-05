=== Lean WP ===
Contributors: senlin
Donate link: https://so-wp.com/plugins/donations
Tags: lean, wp, hide, bloat, remove, adminbar, customizer, embed, rest api, json, emojis, sidebar, dashboard widget, tools, howdy
Requires at least: 4.7.5
Tested up to: 4.8
Stable tag: 1.0.0
License: GPL-3.0+
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Using WP for a company site? Then you should install Lean WP to make the WordPress front- and back-end more suitable for companies.
Lean WP: WordPress for companies, without the bloat!

== Description ==

The WordPress software is getting bloated. And that is especially true if you're using WP to build a lean company website.

Customizer, Distraction Free Writing, Emojis, Embeds, the list keeps getting larger with more nonsense and more bloat. There have been rumors about forking WordPress to have a more business oriented version, but such things are merely talk and will never really get off the ground.

The better way in our opinion is to use the WP infrastructure and to simply remove all the unwanted things by using actions and filters.

The result is Lean WP.

<strong>What does the Lean WP do exactly?</strong>
<em>Backend related</em>
* remove redundant items of the adminbar
* remove howdy
* globally remove welcome panel
* remove dashboard widgets and removes certain admin sidebar (sub)menus
* remove a number of default widgets
* disable XML-RPC and remove call for XML-RPC from head
* globally disable scrollfree editor
* reorder Pages, Posts and Media
* prevent plugins from injecting themselves as top level menus
* move Gravity Forms menu down if plugin is active
* set Reading Settings "Front page displays" to Page
* disable file editors
* set different defaults for certain options
* remove contextual help
* remove admin footer text and WP version
* add Lean WP Dashboard Widget

<em>Frontend related</em>
* clean up the head
* remove redundant items of the adminbar
* remove WP version from RSS
* remove all meta generator tags
* change default category name
* block user-enumeration
* disable author archives


We have adapted a handful of existing plugins and added to Lean WP, these are:

- [WP Comment Humility](https://wordpress.org/plugins/wp-comment-humility/) by John James Jacoby
- [Disable Embeds](https://wordpress.org/plugins/disable-embeds/) by Pascal Birchler
- [Disable Emojis](https://wordpress.org/plugins/disable-emojis/) by Ryan Heller
- [Disable REST API](https://wordpress.org/plugins/disable-json-api/) by Dave McHale
- [mypace Remove Comments Feed Link](https://wordpress.org/plugins/mypace-remove-comments-feed-link/) by Kei Nomura

There are also two plugins that are auto-installed upon activation of the Lean WP plugin, these are:

- [Customizer Remove All Parts](https://wordpress.org/plugins/customizer-remove-all-parts/) by Andy Wilkerson and Jesse Petersen
- [Move Site Icon To Settings](https://wordpress.org/plugins/move-site-icon-to-settings/) by Greg Reindel

The reason we chose to auto activate these two and integrate the other plugins is because these two are more complex and it is very likely that they will receive updates in the future. The plugins we integrated are less likely to receive updates and/or have not been updated for many years already. Instead of recommending our users to install expired plugins, we think it is better that we thoroughly test the code and only use the part necessary to do the job we want it to do.

Lastly Lean WP adds a dashboard widget to the now very clean Dashboard, which shows the WP version, the current theme and the user's IP address.

== Installation ==

Visit the Plugin's [Documentation](https://so-wp.com/lean-wp-docs/welcome/installation) for detailed instructions.

== Screenshots ==

1. Description of first screenshot named screenshot-1
2. Description of second screenshot named screenshot-2
3. Description of third screenshot named screenshot-3

== Frequently Asked Questions ==

Visit the Plugin's [Documentation](https://so-wp.com/lean-wp-docs/faq) to see all the FAQs.


== Changelog ==

= 1.0.0 =

* 2017-5-22
* Initial release
