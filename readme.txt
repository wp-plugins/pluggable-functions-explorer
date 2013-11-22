=== Pluggable Functions Explorer ===
Contributors: wpcure
Tags: pluggable functions, overridden functions, reassigned functions, plugin conflict
Requires at least: 3.5
Tested up to: 3.7.1
Stable tag: 1.0.0
License: GPLv2 or later

Check which Pluggable Functions have been overriden (reassigned), and in which PHP file

== Description ==

Dealing with Pluggable Functions can be tricky:

* Which functions have been overridden by which module?
* Each function can effectively only be overridden by a single module, leading to potential conflicts

This plugin attempts to shed light on the Pluggable Functions conditionally declared by WordPress Core. In case a function has been overridden (reassigned) by another module, such as a plugin or theme, the PHP file containing the effective declaration is shown.

== Installation ==

Simply activate the plugin and visit Tools &rsaquo; Pluggable Functions

== Changelog ==

= 1.0.0 =
* Initial version for public release