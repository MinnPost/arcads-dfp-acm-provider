<?php
/**
 * The main plugin class
 *
 * @package ArcAds_DFP_ACM_Provider
 */

class ArcAds_DFP_ACM_Provider_Loader {

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
	 * User capability required
	 * @var string
	 */
	public $capability;

	/**
	 * Class constructor
	 */
	public function __construct( $version, $file ) {

		$this->version       = $version;
		$this->file          = $file;
		$this->option_prefix = 'arcads_dfp_acm_provider_';
		$this->slug          = 'arcads-dfp-acm-provider';
		$this->capability    = 'manage_advertising';

	}

	public function init() {

		// add this plugin to the ACM provider list and initialize it
		add_filter( 'acm_register_provider_slug', array( $this, 'acm_register_arcads_dfp_slug' ) );

		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		//register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// methods for deactivating the plugin
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

	}

	/**
	* Register this provider with ACM
	*
	* @param array $providers
	* @return array $providers
	*/
	public function acm_register_arcads_dfp_slug( $providers ) {
		$providers->arcads_dfp = array(
			'provider' => 'ArcAds_DFP_ACM_Provider_Extension',
			'table'    => 'ArcAds_DFP_ACM_Provider_WP_List_Table',
		);
		return $providers;
	}

	/**
	* Display a Settings link on the main Plugins page
	*
	* @param array $links
	* @param string $file
	* @return array $links
	* These are the links that go with this plugin's entry
	*/
	public function plugin_action_links( $links, $file ) {
		if ( plugin_basename( $this->file ) === $file ) {
			$settings = '<a href="' . get_admin_url() . 'options-general.php?page=' . $this->slug . '">' . __( 'Settings', 'arcads-dfp-acm-provider' ) . '</a>';
			array_unshift( $links, $settings );
		}
		return $links;
	}

	/**
	 * Activate plugin
	 *
	 * @return void
	 */
	public function activate() {
		// by default, only administrators can configure the plugin
		$role = get_role( 'administrator' );
		$role->add_cap( $this->capability );
	}

	/**
	 * Deactivate plugin
	 *
	 * @return void
	 */
	public function deactivate() {
		$role = get_role( 'administrator' );
		$role->remove_cap( $this->capability );
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
		load_plugin_textdomain( 'arcads-dfp-acm-provider', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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
