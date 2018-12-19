# Lean WP

[![plugin version](https://img.shields.io/wordpress/plugin/v/lean-wp.svg)](https://wordpress.org/plugins/lean-wp)

###### Last updated on December 18, 2018
###### Development version 1.3.0
###### requires at least WordPress 4.8
###### tested up to WordPress 5.0
###### Author: [Pieter Bos](https://github.com/senlin)
###### [Documentation](https://so-wp.com/lean-wp-docs)

Lean WP does a great job cleaning up the WordPress backend (Dashboard) and frontend!

## Description

The reason we developed the Lean WP plugin is that we think that WordPress in its current shape can be cleaned up and trimmed down.

The Lean WP plugin is mostly targeted at companies that use WordPress for their company websites. At the same time we think that the plugin can also be useful for people who simply want to use the interface they have gotten so used to, without the new "features" that seem to be added with each new release.

In the past new features were added to WordPress using the 80/20-rule, which meant the following:

> Clean, Lean, and Mean

> The core of WordPress will always provide a solid array of basic features. It’s designed to be lean and fast and will always stay that way. [...] The rule of thumb is that the core should provide features that 80% or more of end users will actually appreciate and use. If the next version of WordPress comes with a feature that the majority of users immediately want to turn off, or think they’ll never use, then we’ve blown it. If we stick to the 80% principle then this should never happen.

_[WordPress.org Philosophy page](https://wordpress.org/about/philosophy/)_

Well, WordPress no longer is Clean and certainly not Lean and in terms of those, the future is not looking very bright. Matt Mullenweg even has hinted that the 80/20-rule is no longer.

> It might be time to retire 80/20 from the philosophy page, as it is seldom used as intended.

_Matt Mullenweg · March 31, 2017 at 8:15 PM on WP Tavern in a [discussion](https://wptavern.com/wordpress-plugin-directory-redesign-why-so-many-people-feel-their-feedback-was-ignored/#comment-216989) on the redesign of the Plugins Directory_

Over the past few releases a long list of unwanted things have been added to Core and unfortunately the end is nowhere near (the Gutenberg project is in full swing at the time of writing this).

* Emojis
* Distraction free writer
* Scroll-free editor
* Customizer
* REST API enabled by default
* Embeds (of content) enabled by default

And there is stuff that people have called desperately for to remove for many years already, such as:

* XLM-RPC anyone?

Last but not least there are the usual frustrations that seem impossible to deal with in an easy way:

* removing the comments system completely including the RSS
* the ridiculous Howdy-"greeting" in the admin panel
* disabling author archives
* plugins that add their settings all over the WP Dashboard sidebar
* the useless welcome panel with a strong focus on blogging and making new content
* changing the order of Pages and Posts in the sidebar menu of the WP Dashboard.

There are various plugins that take care of a few or more items on the list above, but to be honest we were getting tired of having to install 5-10 different plugins to bend WordPress into submission. The Lean WP plugin therefore is a collection of functions, hooks and filters to target all of our frustrations.

For more information, please have a look at the [extensive documentation](https://so-wp.com/lean-wp-docs) we have made available for the Lean WP plugin, including features, FAQs and screenshots.

## Changelog

### 1.3.0

* June 12, 2018
* switch [Disable REST API plugin](https://wordpress.org/plugins/disable-json-api/) by Dave McHale for newly released [Disable WP REST API](https://wordpress.org/plugins/disable-wp-rest-api/) by Jeff Star

### 1.2.0

* November 17, 2017
* Makes `show_page_on_front()` function pluggable so it can be overridden in (child-)theme or other plugin; addresses [issue #10](https://github.com/senlin/lean-wp/issues/10)

### 1.1.1

* August 19, 2017
* Fix issue with Dismissible Notice of WP Dependency Installer, [issue #5](https://github.com/senlin/lean-wp/issues/5#issuecomment-323379646)
* Set dismissible period from one week to forever

### 1.1.0

* August 18, 2017
* remove Howdy greeting altogether, because other languages was (of course) still showing up. Fixes [issue #2](https://github.com/senlin/lean-wp/issues/2)
* make installation of additional plugins optional instead of required. Fixes [issue #5](https://github.com/senlin/lean-wp/issues/5)

### 1.0.1

* August 11, 2017
* add condition that checks if Uncategorized category exists. If it doesn't then the default category name change does not happen. Fix [issue #4](https://github.com/senlin/lean-wp/issues/4)
* add changelog to this README.md file

### 1.0.0

* August 9, 2017
* Release version
