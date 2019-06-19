<?php
/**
 * The plugin class that creates the ACM provider
 *
 * @package ArcAds_DFP_ACM_Provider
 */

class ArcAds_DFP_ACM_Provider_Extension extends ACM_Provider {

	public $option_prefix;
	public $version;
	public $slug;
	public $capability;

	//public $default_domain;
	//public $server_path;
	//public $default_url;

	public $crawler_user_agent = 'Mediapartners-Google';

	public function __construct() {

		$this->option_prefix = arcads_dfp_acm_provider()->option_prefix;
		$this->version       = arcads_dfp_acm_provider()->version;
		$this->slug          = arcads_dfp_acm_provider()->slug;
		$this->capability    = arcads_dfp_acm_provider()->capability;

		global $ad_code_manager;
		$this->ad_code_manager = $ad_code_manager;

		$this->ad_panel  = new ArcAds_DFP_ACM_Provider_Ad_Panel();
		$this->front_end = new ArcAds_DFP_ACM_Provider_Front_End();

		// tags for AppNexus
		$this->ad_tag_ids = $this->ad_panel->ad_tag_ids();

		// Default fields for AppNexus
		$this->ad_code_args = $this->ad_panel->ad_code_args();

		parent::__construct();
	}

}
