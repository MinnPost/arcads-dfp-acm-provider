Changelog
=========

* 0.0.12 (2022-11-23)
    * Optionally add the Ads by Google JavaScript to the enqueued scripts.

* 0.0.11 (2022-06-06)
    * Better versioning of CSS and JavaScript files.

* 0.0.10 (2022-05-04)
    * Fix issues that occur in PHP 8.0.
    * Improve WordPress Code Standards formatting.
    * Upgrade the ArcAds library to 6.2.0.

* 0.0.9 (2021-07-29)
    * Upgrade the ArcAds library to 4.0.1.

* 0.0.8 (2021-03-22)
    * The spacing is too much.

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
