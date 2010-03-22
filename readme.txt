=== Different Type ===
Contributors: Bueltge
Donate link: http://bueltge.de/wunschliste/
Tags: post, meta box
Requires at least: 2.8
Tested up to: 2.9
Stable tag: 0.1

Add different types to posts.

== Description ==
Add new areas on the post-edit-area: subtitle, additional informations and a field for listdata. You can use this for more custom content on themes and you can also learn hwo it is easy to add meta boxes to this area an WordPress Backend.

== Installation ==
1. Unpack the download-package
1. Upload all files to the `/wp-content/plugins/` directory, include folders
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the new Template Tags to your templates

Example:
`<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
	<h2><?php the_title(); ?></h2>
	<h3><?php if ( function_exists('the_DifferentTypeFacts') ) the_DifferentTypeFacts($post->ID, 'heading'); ?></h3>
	<div class="entry">
		<?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>'); ?>
		
		<?php if ( function_exists('the_DifferentTypeFacts') ) the_DifferentTypeFacts($post->ID, 'additional-info'); ?>
		<?php if ( function_exists('the_DifferentTypeFacts') ) the_DifferentTypeFacts($post->ID, 'listdata'); ?>
`


== Screenshots ==
1. Edit-Area of posts and the new meta box in WP 2.9
1. Example Fronetend on Kubrick-Theme

== Changelog ==
= v0.1 (12/24/2009) =
* 24. day of my advent calender is this plugin for learn custom your edit area
