<?php
/**
 * Class file for the MinnPost_ACM_DFP_Async_Ad_Panel class.
 *
 * @file
 */

class MinnPost_ACM_DFP_Async_Ad_Panel {

	public $option_prefix;
	public $version;
	public $slug;
	public $capability;
	public $ad_code_manager;

	/**
	* Constructor which sets up ad panel
	*/
	public function __construct() {

		$this->option_prefix   = minnpost_acm_dfp_async()->option_prefix;
		$this->version         = minnpost_acm_dfp_async()->version;
		$this->slug            = minnpost_acm_dfp_async()->slug;
		$this->ad_code_manager = minnpost_acm_dfp_async()->ad_code_manager;

		$this->add_actions();

	}

	private function add_actions() {
		add_filter( 'acm_ad_tag_ids', array( $this, 'ad_tag_ids' ) );
	}

	/**
	* Tag IDs
	*
	* @param int $adcount
	* @return int $adcount
	*
	*/
	public function ad_tag_ids( $ids ) {
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
