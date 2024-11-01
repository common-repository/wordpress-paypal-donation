=== WordPress PayPal Donation ===
Contributors: Thomas Stachl, Alberto Buschetto
Donate link: http://thomas.stachl.me/plugins/wordpress-paypal-donation/
Tags: donate, donation, button, paypal, money, easy, post, theme, customizable,
Requires at least: 2.7
Tested up to: 2.8.4
Stable tag: 1.01

This plugin adds a PayPal donation button to posts and/or theme.

== Description ==

Another small and beautiful plugin. It adds a fully customizable PayPal button
to the posts. The only thing you have to do is put [donate] in your post or
<?php wordpress_paypal_donation(); ?> in your theme. Via the options panel it
is fully customizable.

If you want special settings in one post or somewhere else you can set some
attributes. For example:

= Updates =
27.09.2009
----------
Page Style has been added.

= Attributes to customize =
email		= Your PayPal Account
title		= The title of the page where you redirect your donators
return_url	= The URL where the donators are redirectet.
cancel_url	= The cancel URL.
amount		= The amount you want to have.
ccode		= Currency code (USD = United States, EUR = Europe, ...)
image		= The Image of the button. 

= Customize in post =
If you want to customize the button in your post you write:
[donate email="me@example.com" title="Donate for me once more" return_url=...]

That's all!

= Customize in theme =
It's nearly as easy as in posts. You write:
<?php wordpress_paypal_donation('email=me@example.com&title=Donate for me&...); ?>


<a href="http://thomas.stachl.me/2008/11/30/tutorials/wordpress-paypal-donation/">Live demo (at the bottom)!</a>

== Installation ==

1. Install `wordpress-paypal-donation` through the 'Plugins' menu in WordPress
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Change the settings through the 'Settings' menu in WordPress

== Frequently Asked Questions ==

No questions til now.

== Screenshots ==

1. Screenshot of the donate button in a post.
2. Screenshot of the configuration panel for this plugin.
3. Screenshot of the menu button.
