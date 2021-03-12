=== ArcAds DFP ACM Provider ===
Contributors: minnpost, jonathanstegall
Donate link: https://www.minnpost.com/support/?campaign=7010G0000012fXGQAY
Tags: ads, ad code manager, arcads, dfp
Requires at least: 4.9
Tested up to: 5.7
Stable tag: 0.0.7
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

* 0.0.7 (2021-03-12)
    * Account for when the ad code is null instead of empty.

* 0.0.6 (2021-03-11)
    * When editing posts that have ads, make sure they preserve the spacing that should be around them instead of running into the paragraphs before or after the ads.
    * Upgrade the Arcads library to version 3.0.0.

* 0.0.5 (2020-07-10)
    * Override setting for before/after border for embed ads only.
    * Fix bug that could cause paragraph breaks inside the `<script>` tag

* 0.0.4 (2020-05-15)
    * Override setting for before/after text for embed ads only.

* 0.0.3 (2020-05-15)
    * Add setting for top/bottom border that can go around ads.
    * Add setting for before/after text that can go around ads.

* 0.0.2 (2020-04-06)
    * Previously the prevent ads field only stopped ads from being added to the content. If a post had existing ad shortcodes, they stayed in the content without being rendered as ads. This removes them from the content instead.

* 0.0.1 (2019-06-19)
    * Basic plugin that generates code for the ArcAd wrapper on DFP ads within the ACM plugin.
