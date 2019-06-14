<?php
/**
 * The main plugin class
 *
 * @package MinnPost_ACM_DFP_Async
 */

class MinnPost_ACM_DFP_Async {

	/**
	 * The version number for this release of the plugin.
	 * This will later be used for upgrades and enqueuing files
	 *
	 * This should be set to the 'Plugin Version' value defined
	 * in the plugin header.
	 *
	 * @var string A PHP-standardized version number string
	 */
	public $version;

	/**
	 * Filesystem path to the main plugin file
	 * @var string
	 */
	public $file;

	/**
	* @var object
	* Load the parent plugin
	*/
	public $ad_code_manager;

	/**
	 * Prefix for plugin options
	 * @var string
	 */
	public $option_prefix;

	/**
	 * Plugin slug
	 * @var string
	 */
	public $slug;

	/**
	* @var object
	* Methods to get data for use by the plugin and other places
	*/
	public $ad_panel;

	/**
	* @var object
	* Front end display
	*/
	public $front_end;

	/**
	* @var object
	* Administrative interface
	*/
	//public $admin;

	/**
	 * Class constructor
	 *
	 * @param string $version The current plugin version
	 * @param string $file The main plugin file
	 */
	public function __construct( $version, $file ) {

		$this->version       = $version;
		$this->file          = $file;
		$this->option_prefix = 'minnpost_acm_dfp_async_';
		$this->option_prefix = 'appnexus_acm_provider_';
		$this->slug          = 'minnpost-acm-dfp-async';
		$this->capability    = 'manage_advertising';
		// parent plugin
		$this->ad_code_manager = $this->load_parent();

	}

	public function init() {

		// Ad panel setup
		$this->ad_panel = new MinnPost_ACM_DFP_Async_Ad_Panel();

		// Front end display
		$this->front_end = new MinnPost_ACM_DFP_Async_Front_End();

		// Admin features
		//$this->admin = new MinnPost_ACM_DFP_Async_Admin();

	}

	/**
	* Load and set values we don't need until the parent plugin is actually loaded
	*
	*/
	public function load_parent() {
		if ( ! class_exists( 'Ad_Code_Manager' ) ) {
			return false;
		} else {
			global $ad_code_manager;
			return $ad_code_manager;
		}
	}

	/**
	 * Get the URL to the plugin admin menu
	 *
	 * @return string          The menu's URL
	 */
	public function get_menu_url() {
		$url = 'options-general.php?page=' . $this->slug;
		return admin_url( $url );
	}

	/**
	 * Load up the localization file if we're using WordPress in a different language.
	 *
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'minnpost-acm-dfp-async', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Sanitize a string of HTML classes
	 *
	 */
	public function sanitize_html_classes( $classes, $sep = ' ' ) {
		$return = '';
		if ( ! is_array( $classes ) ) {
			$classes = explode( $sep, $classes );
		}
		if ( ! empty( $classes ) ) {
			foreach ( $classes as $class ) {
				$return .= sanitize_html_class( $class ) . ' ';
			}
		}
		return $return;
	}

}
