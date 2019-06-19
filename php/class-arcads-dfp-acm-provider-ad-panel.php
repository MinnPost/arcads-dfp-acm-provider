<?php
/**
 * The plugin class that creates the ad panel
 *
 * @package ArcAds_DFP_ACM_Provider
 */

class ArcAds_DFP_ACM_Provider_Ad_Panel {

	public $version;
	public $file;
	public $option_prefix;
	public $slug;
	public $capability;
	public $ad_code_manager;

	/**
	* Constructor which sets up ad panel
	*/
	public function __construct() {

		$this->version       = arcads_dfp_acm_provider()->version;
		$this->file          = arcads_dfp_acm_provider()->file;
		$this->option_prefix = arcads_dfp_acm_provider()->option_prefix;
		$this->slug          = arcads_dfp_acm_provider()->slug;
		$this->capability    = arcads_dfp_acm_provider()->capability;

		global $ad_code_manager;
		$this->ad_code_manager = $ad_code_manager;

		$this->add_actions();

	}

	private function add_actions() {
		add_filter( 'acm_ad_tag_ids', array( $this, 'ad_tag_ids' ) );
		add_filter( 'acm_ad_code_args', array( $this, 'filter_ad_code_args' ) );
	}

	/**
	* Tag IDs
	*
	* @param int $adcount
	* @return int $adcount
	*
	*/
	public function ad_tag_ids( $ids = array() ) {
		$ids = array(
			array(
				'tag'               => 'leaderboard',
				'url_vars'          => array(
					'sizes' => array(
						0 => array(
							'height' => '90',
							'width'  => '728',
						),
						1 => array(
							'height' => '90',
							'width'  => '970',
						),
					),
				),
				'enable_ui_mapping' => true,
			),
			array(
				'tag'               => 'halfpage',
				'url_vars'          => array(
					'sizes' => array(
						0 => array(
							'height' => '600',
							'width'  => '300',
						),
					),
				),
				'enable_ui_mapping' => true,
			),
			array(
				'tag'               => 'Middle3',
				'url_vars'          => array(
					'size' => 'fluid',
				),
				'enable_ui_mapping' => true,
			),
			array(
				'tag'      => 'dfp_head',
				'url_vars' => array(),
			),
		);

		$embed_prefix      = get_option( $this->option_prefix . 'embed_prefix', 'x' );
		$start_embed_id    = get_option( $this->option_prefix . 'start_tag_id', 'x100' );
		$start_embed_count = intval( str_replace( $embed_prefix, '', $start_embed_id ) ); // ex 100
		$end_embed_id      = get_option( $this->option_prefix . 'end_tag_id', 'x110' );
		$end_embed_count   = intval( str_replace( $embed_prefix, '', $end_embed_id ) ); // ex 110
		for ( $i = $start_embed_count; $i <= $end_embed_count; $i++ ) {
			$ids[] = array(
				'tag'               => $embed_prefix . $i,
				'url_vars'          => array(
					'sizes' => array(
						0 => array(
							'height' => '250',
							'width'  => '300',
						),
					),
					'pos'   => $embed_prefix . $i,
				),
				'enable_ui_mapping' => true,
			);
		}
		return $ids;
	}

	/**
	 * Register the tag ids based on the admin settings
	 * @param array $ids
	 * @return array $ad_tag_ids
	 */
	/*public function ad_tag_ids( $ids = array() ) {
		$tag_list = explode( ', ', get_option( $this->option_prefix . 'tag_list', '' ) );

		$ad_tag_ids = array();
		foreach ( $tag_list as $tag ) {
			$ad_tag_ids[] = array(
				'tag'               => $tag,
				'url_vars'          => array(
					'tag' => $tag,
				),
				'enable_ui_mapping' => true,
			);
		}

		$ad_tag_ids[] = array(
			'tag'      => 'dfp_head',
			'url_vars' => array(),
		);

		return $ad_tag_ids;
	}*/

	/**
	 * Register the tags available for mapping in the UI
	 */
	public function filter_ad_code_args( $ad_code_args ) {
		$ad_code_manager = $this->ad_code_manager;

		foreach ( $ad_code_args as $tag => $ad_code_arg ) {

			if ( 'tag' !== $ad_code_arg['key'] ) {
				continue;
			}

			// Get all of the tags that are registered, and provide them as options
			foreach ( (array) $ad_code_manager->ad_tag_ids as $ad_tag ) {
				if ( isset( $ad_tag['enable_ui_mapping'] ) && $ad_tag['enable_ui_mapping'] ) {
					$ad_code_args[ $tag ]['options'][ $ad_tag['tag'] ] = $ad_tag['tag'];
				}
			}
		}
		return $ad_code_args;
	}

	/**
	 * Register the tag arguments
	 */
	public function ad_code_args() {
		$ad_code_args = array(
			array(
				'key'      => 'tag',
				'label'    => __( 'Tag', 'ad-code-manager' ),
				'editable' => true,
				'required' => true,
				'type'     => 'select',
				'options'  => array(
					// This is added later, through 'acm_ad_code_args' filter
				),
			),
			array(
				'key'      => 'tag_id',
				'label'    => __( 'Tag ID', 'ad-code-manager' ),
				'editable' => true,
				'required' => true,
			),
			array(
				'key'      => 'tag_name',
				'label'    => __( 'Tag Name', 'ad-code-manager' ),
				'editable' => true,
				'required' => true,
			),
		);
		return $ad_code_args;
	}

}
