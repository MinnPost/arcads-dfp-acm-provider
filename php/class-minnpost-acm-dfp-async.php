<?php
/**
 * The main plugin class
 *
 * @package MinnPost_ACM_DFP_Async
 */

class MinnPost_ACM_DFP_Async extends ACM_Provider {

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
	* Ad panel table
	*/
	public $ad_panel_table;

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
	public function __construct() {

		//$this->version       = $version;
		//$this->file          = $file;
		$this->option_prefix = 'minnpost_acm_dfp_async_';
		$this->option_prefix = 'appnexus_acm_provider_';
		$this->slug          = 'minnpost-acm-dfp-async';
		$this->capability    = 'manage_advertising';

		global $ad_code_manager;
		$this->ad_code_manager = $ad_code_manager;

	}

	public function init() {

		// Ad panel setup
		$this->ad_panel = new MinnPost_ACM_DFP_Async_Ad_Panel();

		// tags for AppNexus
		//$this->ad_tag_ids = $this->ad_panel->ad_tag_ids();

		// Default fields for AppNexus
		$this->ad_code_args = $this->ad_panel->ad_code_args();

		// Front end display
		$this->front_end = new MinnPost_ACM_DFP_Async_Front_End();

		// Admin features
		//$this->admin = new MinnPost_ACM_DFP_Async_Admin();

		parent::__construct();

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
