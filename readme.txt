=== ArcAds DFP ACM Provider ===
Contributors: minnpost, jonathanstegall
Donate link: https://www.minnpost.com/support/?campaign=7010G0000012fXGQAY
Tags: ads, ad code manager, arcads, dfp
Requires at least: 4.9
Tested up to: 5.0
Stable tag: 0.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin extends the [Ad Code Manager plugin](https://github.com/Automattic/Ad-Code-Manager) to provide functionality for the ArcAds wrapper, which is itself a wrapper on DFP.

== Description ==

This plugin extends the [Ad Code Manager plugin](https://github.com/Automattic/Ad-Code-Manager) to provide functionality for the ArcAds wrapper, which is itself a wrapper on DFP. It enables settings for supported ad tag types, tag IDs, lazy loading, and separate settings for embed ads within articles. It also provides the ad table for the Ad Code Manager admin interface.

== Installation ==

#### Activate the plugin

In the Plugins list in WordPress, activate the plugin and find the settings link (you can also find this plugin's settings in the main Settings list in WordPress, under the ArcAds DFP Ad Settings menu item once it is activated).

The plugin's settings URL is `https://<your site>/wp-admin/options-general.php?page=arcads-dfp-acm-provider`. Ad codes appear in the Ad Code Manager's interface at `https://<your site>/wp-admin/tools.php?page=ad-code-manager`.

== Changelog ==

* 0.0.1 (2019-06-19)

    * Basic plugin that generates code for the ArcAd wrapper on DFP ads within the ACM plugin.
