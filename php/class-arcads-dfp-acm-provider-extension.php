<?php
/**
 * The plugin class that creates the ACM provider
 *
 * @package ArcAds_DFP_ACM_Provider
 */

class ArcAds_DFP_ACM_Provider_Extension extends ACM_Provider {

	public $version;
	public $file;
	public $option_prefix;
	public $slug;
	public $capability;
	public $ad_code_manager;

	public $crawler_user_agent = 'Mediapartners-Google';

	public function __construct() {

		$this->version       = arcads_dfp_acm_provider()->version;
		$this->file          = arcads_dfp_acm_provider()->file;
		$this->option_prefix = arcads_dfp_acm_provider()->option_prefix;
		$this->slug          = arcads_dfp_acm_provider()->slug;
		$this->capability    = arcads_dfp_acm_provider()->capability;

		global $ad_code_manager;
		$this->ad_code_manager = $ad_code_manager;

		$this->ad_panel  = new ArcAds_DFP_ACM_Provider_Ad_Panel();
		$this->front_end = new ArcAds_DFP_ACM_Provider_Front_End();
		$this->admin     = new ArcAds_DFP_ACM_Provider_Admin();

		// tags for DFP
		$this->ad_tag_ids = $this->ad_panel->ad_tag_ids();

		// Default fields for DFP
		$this->ad_code_args = $this->ad_panel->ad_code_args();

		parent::__construct();
	}

}
