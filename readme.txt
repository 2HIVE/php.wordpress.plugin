=== 2Hive ===
Contributors: 2hive
Tags: content moderation, 2hive
Requires at least: 3.0.1
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

2Hive Content Moderation Service integration

== Description ==
2Hive WordPress performs posts and comments moderation through 2Hive Content Moderation Service. After activation it will automatically send new posts and comments to the 2Hive and will remove (archive) ones automatically in case of inappropriate content was detected by 2Hive.

Features:

1. Easy installation
2. Transparent integration with 2Hive
3. Flexible settings
4. High quality

== Installation ==
1. Upload "2HivePlugin.php" to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Get your 2Hive API Key in your Account Page: http://2hive.org/project/account
4. Go to your 2Hive configuration page, and save your API key
5. Go to 2Hive http://2hive.org/project/rules and create new rules with types: "new_comment" and "new_post"
6. Go to http://2hive.org/project/moderators and invite your moderators or enable 2Hive Moderation Team. 
7. You service under protect 24x7!

== Frequently Asked Questions ==
= How do I get an API Key =
Please register at http://2hive.org/project/signup (skip if you have an account). Go to your Account Page: http://2hive.org/project/account

= How do I control Moderation Rules? =
Please define rules in http://2hive.org/project/rules. 

= What Moderation Rules for new posts? =
Please create a Moderation rule with Type=new_post

= What Moderation Rules for new comments? =
Please create a Moderation rule with Type=new_comment

= What is 2Hive Moderation Team? =
2Hive Moderation Team is high skilled Moderators. You can use them through 2Hive Service. 

= Is it possible to moderate content by my own, i.e. without 2Hive Team? =
Yes, this is possible! 

= How much does it cost to use 2Hive Service? =
Please see our pricing table chart: http://2hive.org/static/projects/pricing

== Changelog ==

= 1.0 =
* Initial release.
