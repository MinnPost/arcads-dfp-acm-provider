<?php
/*
Plugin Name: ArcAds DFP ACM Provider
Plugin URI:
Description:
Version: 0.0.4
Author: MinnPost
Author URI: https://code.minnpost.com
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: arcads-dfp-acm-provider
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * The full path to the main file of this plugin
 *
 * This can later be passed to functions such as
 * plugin_dir_path(), plugins_url() and plugin_basename()
 * to retrieve information about plugin paths
 *
 * @since 0.0.1
 * @var string
 */
define( 'ARCADS_DFP_ACM_PROVIDER_FILE', __FILE__ );

/**
 * The plugin's current version
 *
 * @since 0.0.1
 * @var string
 */
define( 'ARCADS_DFP_ACM_PROVIDER_VERSION', '0.0.4' );

// Load the autoloader.
require_once( 'lib/autoloader.php' );

/**
 * Retrieve the instance of the main plugin class
 *
 * @since 0.0.1
 * @return MinnPost_ACM_DFP_Async
 */
function arcads_dfp_acm_provider() {
	static $plugin;

	if ( is_null( $plugin ) ) {
		$plugin = new ArcAds_DFP_ACM_Provider_Loader( ARCADS_DFP_ACM_PROVIDER_VERSION, __FILE__ );
	}

	return $plugin;
}

arcads_dfp_acm_provider()->init();
