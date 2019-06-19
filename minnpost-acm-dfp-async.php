<?php
/*
Plugin Name: MinnPost ACM DFP Async
Plugin URI:
Description:
Version: 0.0.6
Author: Jonathan Stegall
Author URI: https://code.minnpost.com
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: minnpost-acm-dfp-async
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
 * @since 0.0.6
 * @var string
 */
define( 'MINNPOST_ACM_DFP_ASYNC_FILE', __FILE__ );

/**
 * The plugin's current version
 *
 * @since 0.0.6
 * @var string
 */
define( 'MINNPOST_ACM_DFP_ASYNC_VERSION', '0.0.1' );

// Load the autoloader.
require_once( 'lib/autoloader.php' );

/**
 * Retrieve the instance of the main plugin class
 *
 * @since 2.6.0
 * @return MinnPost_ACM_DFP_Async
 */
function minnpost_acm_dfp_async() {
	static $plugin;

	if ( is_null( $plugin ) ) {
		$plugin = new MinnPost_ACM_DFP_Async( MINNPOST_ACM_DFP_ASYNC_VERSION, __FILE__ );
	}

	return $plugin;
}

//minnpost_acm_dfp_async()->init();

// add this plugin to the ACM provider list and initialize it
if ( ! function_exists( 'acm_register_arcads_slug' ) ) :
	add_filter( 'acm_register_provider_slug', 'acm_register_arcads_slug' );
	function acm_register_arcads_slug( $providers ) {
		$providers->arcads = array(
			'provider' => 'MinnPost_ACM_DFP_Async',
			'table'    => 'MinnPost_ACM_DFP_Async_Ad_Panel_Table',
		);
		return $providers;
	}
endif;

