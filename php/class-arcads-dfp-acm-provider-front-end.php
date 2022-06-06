<?php
/**
 * The plugin class that creates front end functionality to render the ads
 *
 * @package ArcAds_DFP_ACM_Provider
 */

class ArcAds_DFP_ACM_Provider_Front_End {

	public $version;
	public $file;
	public $option_prefix;
	public $slug;
	public $capability;
	public $ad_code_manager;

	/**
	* Constructor which sets up front end rendering
	*/
	public function __construct() {

		$this->version       = arcads_dfp_acm_provider()->version;
		$this->file          = arcads_dfp_acm_provider()->file;
		$this->option_prefix = arcads_dfp_acm_provider()->option_prefix;
		$this->slug          = arcads_dfp_acm_provider()->slug;
		$this->capability    = arcads_dfp_acm_provider()->capability;

		global $ad_code_manager;
		$this->ad_code_manager = $ad_code_manager;

		// for the wp_enqueue_script, keep track of what version of ArcAds is in use.
		$this->arcads_library_version = '6.2.0';

		$this->paragraph_end = array(
			false => '</p>',
			true  => "\n",
		);

		$this->lazy_load_all    = filter_var( get_option( $this->option_prefix . 'lazy_load_ads', false ), FILTER_VALIDATE_BOOLEAN );
		$this->lazy_load_embeds = filter_var( get_option( $this->option_prefix . 'lazy_load_embeds', false ), FILTER_VALIDATE_BOOLEAN );

		$this->dfp_id = filter_var( get_option( $this->option_prefix . 'dfp_id', '1035012' ), FILTER_SANITIZE_STRING );

		$this->collapse_empty_divs = filter_var( get_option( $this->option_prefix . 'collapse_empty_divs', false ), FILTER_VALIDATE_BOOLEAN );

		$this->add_actions();

	}

	private function add_actions() {
		add_filter( 'acm_output_tokens', array( $this, 'acm_output_tokens' ), 15, 3 );
		add_filter( 'acm_output_html', array( $this, 'filter_output_html' ), 10, 2 );
		add_filter( 'acm_display_ad_codes_without_conditionals', array( $this, 'check_conditionals' ) );
		//add_filter( 'acm_whitelisted_conditionals', array( $this, 'allowed_conditionals' ) );
		add_filter( 'acm_conditional_args', array( $this, 'conditional_args' ), 10, 2 );

		// disperse shortcodes in the editor if the settings say to
		$show_in_editor = filter_var( get_option( $this->option_prefix . 'show_in_editor', false ), FILTER_VALIDATE_BOOLEAN );
		if ( true === $show_in_editor ) {
			add_filter( 'content_edit_pre', array( $this, 'insert_inline_ad_in_editor' ), 10, 2 );
		}

		// always either replace the shortcodes with ads, or if they are absent disperse ad codes throughout the content
		add_shortcode( 'cms_ad', array( $this, 'render_shortcode' ) );
		add_filter( 'the_content', array( $this, 'insert_and_render_inline_ads' ), -1 ); // 2000 fails here, but i can't tell if it's because of raw html or something else. -1 fails on standard stories?
		add_filter( 'the_content_feed', array( $this, 'insert_and_render_inline_ads' ), 2000 );

		// add javascript
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts_and_styles' ), 20 );

		add_action( 'wp_head', array( $this, 'action_wp_head' ) );
	}

	/**
	 * Add the initialization code in the head
	 */
	public function action_wp_head() {
		do_action( 'acm_tag', 'dfp_head' );
	}

	public function acm_output_tokens( $output_tokens, $tag_id, $code_to_display ) {
		global $dfp_tile;
		global $dfp_ord;
		global $dfp_pos;
		global $dfp_dcopt;
		global $wp_query;

		//error_log( 'tile is ' . $dfp_tile . ' and ord is ' . $dfp_ord . ' and pos is ' . $dfp_pos . ' and dcopt is ' . $dfp_dcopt . ' and wp query is ' . print_r( $wp_query, true ) );

		//if ( false === $dfp_pos[ $code_to_display['url_vars']['sz'] ] ) {
		if ( isset( $code_to_display['url_vars']['pos'] ) ) {
			$output_tokens['%pos%'] = $code_to_display['url_vars']['pos'];
		}
		//$output_tokens['%test%'] = isset( $_GET['test'] ) && $_GET['test'] == 'on' ? 'on' : '';

		return $output_tokens;
	}

	/**
	 * Filter the output HTML for each ad tag to produce the code we need
	 * @param string $output_html
	 * @param string $tag_id
	 *
	 * @return $output_html
	 * return filtered html for the ad code
	 */
	public function filter_output_html( $output_html, $tag_id ) {
		switch ( $tag_id ) {
			case 'dfp_head':
				$output_html = '';
				break;
			default:
				$matching_ad_code = $this->ad_code_manager->get_matching_ad_code( $tag_id );
				if ( ! empty( $matching_ad_code ) ) {
					$output_html = $this->get_code_to_insert( $tag_id );
				}
		}
		return $output_html;

	}

	/**
	 * Whether to show ads that don't have any conditionals
	 *
	 * @return bool
	 *
	 */
	public function check_conditionals() {
		$show_without_conditionals = filter_var( get_option( $this->option_prefix . 'show_ads_without_conditionals', false ), FILTER_VALIDATE_BOOLEAN );
		return $show_without_conditionals;
	}

	/**
	 * Add conditionals to the allowed list.
	 * As far as I can tell, we don't actually need this.
	 *
	 * @param array $conditionals
	 * @return array $conditionals
	 *
	 */
	/*public function allowed_conditionals( $conditionals ) {
		$conditionals[] = 'is_singular';
		return $conditionals;
	}*/

	/**
	 * Additional arguments for conditionals
	 *
	 * @param array $args
	 * @param string $function
	 * @return array $args
	 *
	 */
	public function conditional_args( $args, $function ) {
		global $wp_query;
		// has_category and has_tag use has_term
		// we should pass queried object id for it to produce correct result

		if ( in_array( $function, array( 'has_category', 'has_tag' ), true ) ) {
			if ( true === $wp_query->is_single ) {
				$args[] = $wp_query->queried_object->ID;
			}
			// as far as I can tell, we don't actually need this. It causes errors in PHP 8.
			//$args['is_singular'] = true;
		}
		return $args;
	}

	/**
	 * Make [cms_ad] a recognized shortcode
	 *
	 * @param array $atts
	 *
	 *
	 */
	public function render_shortcode( $atts ) {
		return;
	}

	/**
	 * Use one or more inline ads, depending on the settings. This does not place them into the post editor, but into the post when it renders.
	 *
	 * @param string $content
	 *
	 * @return $content
	 * return the post content with code for ads inside it at the proper places
	 *
	 */
	public function insert_and_render_inline_ads( $content = '' ) {
		if ( is_feed() ) {
			global $post;
			$current_object = $post;
		} else {
			$current_object = get_queried_object();
		}
		if ( is_object( $current_object ) ) {
			$post_type = isset( $current_object->post_type ) ? $current_object->post_type : '';
			$post_id   = isset( $current_object->ID ) ? $current_object->ID : '';
		} else {
			return $content;
		}
		$in_editor = false; // we are not in the editor right now

		// Should we skip rendering ads?
		$should_we_skip = $this->should_we_skip_ads( $content, $post_type, $post_id, $in_editor );

		// Render any `[cms_ad` shortcodes, whether they were manually added or added by this plugin
		// this should also be used to render the shortcodes added in the editor
		$shortcode = 'cms_ad';
		$pattern   = $this->get_single_shortcode_regex( $shortcode );
		if ( preg_match_all( $pattern, $content, $matches ) && array_key_exists( 2, $matches ) && in_array( $shortcode, $matches[2], true ) ) {

			/*
			[0] => Array (
				[0] => [cms_ad:Middle]
			)

			[1] => Array(
				[0] =>
			)

			[2] => Array(
				[0] => cms_ad
			)

			[3] => Array(
				[0] => :Middle
			)
			*/

			foreach ( $matches[0] as $key => $value ) {
				$position  = ( isset( $matches[3][ $key ] ) && '' !== ltrim( $matches[3][ $key ], ':' ) ) ? ltrim( $matches[3][ $key ], ':' ) : get_option( $this->option_prefix . 'auto_embed_position', 'Middle' );
				$rewrite[] = $this->get_code_to_insert( $position );
				$matched[] = $matches[0][ $key ];
			}

			if ( true === $should_we_skip ) {
				$rewrite = '';
			}

			return str_replace( $matched, $rewrite, $content );
		} else {
			if ( true === $should_we_skip ) {
				return $content;
			}
		}

		$ad_code_manager = $this->ad_code_manager;

		$content = $this->insert_ads_into_content( $content, false );
		return $content;

	}

	/**
	 * Place the ad code, or cms shortcode for the ad, into the post body as many times, and in the right location.
	 *
	 * @param string $content
	 * @param bool $in_editor
	 *
	 * @return $content
	 * return the post content with shortcodes for ads inside it at the proper places
	 *
	 */
	private function insert_ads_into_content( $content, $in_editor = false ) {
		$multiple_embeds = get_option( $this->option_prefix . 'multiple_embeds', '0' );
		if ( is_array( $multiple_embeds ) ) {
			$multiple_embeds = $multiple_embeds[0];
		}

		$end      = strlen( $content );
		$position = $end;

		$paragraph_end = $this->paragraph_end[ $in_editor ];

		if ( '1' === $multiple_embeds ) {

			$insert_every_paragraphs = intval( get_option( $this->option_prefix . 'insert_every_paragraphs', 4 ) );
			$minimum_paragraph_count = intval( get_option( $this->option_prefix . 'minimum_paragraph_count', 6 ) );

			$embed_prefix      = get_option( $this->option_prefix . 'embed_prefix', 'x' );
			$start_embed_id    = get_option( $this->option_prefix . 'start_tag_id', 'x100' );
			$start_embed_count = intval( str_replace( $embed_prefix, '', $start_embed_id ) ); // ex 100
			$end_embed_id      = get_option( $this->option_prefix . 'end_tag_id', 'x110' );
			$end_embed_count   = intval( str_replace( $embed_prefix, '', $end_embed_id ) ); // ex 110

			$paragraphs = array();
			$split      = explode( $paragraph_end, $content );
			foreach ( $split as $paragraph ) {
				// filter out empty paragraphs
				if ( strlen( $paragraph ) > 3 ) {
					$paragraphs[] = $paragraph . $paragraph_end;
				}
			}

			$paragraph_count = count( $paragraphs );
			$maximum_ads     = floor( ( $paragraph_count - $minimum_paragraph_count ) / $insert_every_paragraphs ) + $minimum_paragraph_count;

			$ad_num      = 0;
			$counter     = $minimum_paragraph_count;
			$embed_count = $start_embed_count;

			for ( $i = 0; $i < $paragraph_count; $i++ ) {
				if ( 0 === $counter && $embed_count <= $end_embed_count ) {
					// make a shortcode using the number of the shorcode that will be added.
					if ( false === $in_editor ) {
						$shortcode = $this->get_code_to_insert( $embed_prefix . (int) $embed_count );
					} elseif ( true === $in_editor ) {
						$shortcode = '[cms_ad:' . $embed_prefix . (int) $embed_count . ']';
					}
					$otherblocks = '(?:div|dd|dt|li|pre|fieldset|legend|figcaption|details|thead|tfoot|tr|td|style|script|link|h1|h2|h3|h4|h5|h6)';
					if ( preg_match( '!(<' . $otherblocks . '[\s/>])!', $paragraphs[ $i ], $m ) ) {
						continue;
					}
					array_splice( $paragraphs, $i + $ad_num, 0, $shortcode );
					$counter = $insert_every_paragraphs;
					$ad_num++;
					if ( $ad_num > $maximum_ads ) {
						break;
					}
					$embed_count++;
				}
				$counter--;
			}

			if ( true === $in_editor ) {
				$content = implode( $paragraph_end, $paragraphs );
			} else {
				$content = implode( '', $paragraphs );
			}
		} else {
			$tag_id        = get_option( $this->option_prefix . 'auto_embed_position', 'Middle' );
			$top_offset    = get_option( $this->option_prefix . 'auto_embed_top_offset', 1000 );
			$bottom_offset = get_option( $this->option_prefix . 'auto_embed_bottom_offset', 400 );

			// if the content is longer than the minimum ad spot find a break.
			// otherwise place the ad at the end
			if ( $position > $top_offset ) {
				// find the break point
				$breakpoints = array(
					'</p>'             => 4,
					'<br />'           => 6,
					'<br/>'            => 5,
					'<br>'             => 4,
					'<!--pagebreak-->' => 0,
					'<p>'              => 0,
					"\n"               => 2,
				);
				// We use strpos on the reversed needle and haystack for speed.
				foreach ( $breakpoints as $point => $offset ) {
					$length = stripos( $content, $point, $top_offset );
					if ( false !== $length ) {
						$position = min( $position, $length + $offset );
					}
				}
			}
			if ( false === $in_editor ) {
				// If the position is at or near the end of the article.
				if ( $position > $end - $bottom_offset ) {
					$position  = $end;
					$shortcode = $this->get_code_to_insert( $tag_id, 'minnpost-ads-ad-article-end' );
				} else {
					$shortcode = $this->get_code_to_insert( $tag_id, 'minnpost-ads-ad-article-middle' );
				}
			} else {
				$shortcode = "\n" . '[cms_ad:' . $tag_id . ']' . "\n\n";
			}

			$content = substr_replace( $content, $shortcode, $position, 0 );
		}

		return $content;
	}

	/**
	 * Get ad code to insert for a given tag.
	 *
	 * @param string $tag_id
	 * @param string $class
	 *
	 * @return $output
	 * return the necessary ad code for the specified tag type
	 *
	 */
	public function get_code_to_insert( $tag_id, $class = '' ) {
		$output_script = ''; // this could be empty
		// get the code to insert
		$ad_code_manager  = $this->ad_code_manager;
		$ad_tags          = $ad_code_manager->ad_tag_ids;
		$matching_ad_code = $ad_code_manager->get_matching_ad_code( $tag_id );
		if ( null === $matching_ad_code ) {
			return '';
		}
		if ( ! empty( $matching_ad_code ) ) {
			$tt = $matching_ad_code['url_vars'];
			// @todo There might be a case when there are two tags registered with the same dimensions
			// and the same tag id ( which is just a div id ). This confuses DFP Async, so we need to make sure
			// that tags are unique

			// Parse ad tags to output flexible unit dimensions
			$unit_sizes = $this->parse_ad_tag_sizes( $tt );
			$position   = '';
			if ( isset( $matching_ad_code['url_vars']['pos'] ) ) {
				$position = esc_attr( $matching_ad_code['url_vars']['pos'] );
			}

			$id       = '';
			$url_type = '';

			if ( is_single() ) {
				$id       = get_the_ID();
				$url_type = 'post';
			} elseif ( is_page() ) {
				$id       = get_the_ID();
				$url_type = 'page';
			} elseif ( is_front_page() ) {
				$url_type = 'front-page';
			} elseif ( is_home() ) {
				$url_type = 'home';
			} elseif ( is_category() ) {
				$id       = get_query_var( 'cat' );
				$url_type = 'category';
			} elseif ( is_tag() ) {
				$id       = get_query_var( 'tag' );
				$url_type = 'tag';
			} elseif ( is_author() ) {
				$author   = get_queried_object();
				$id       = $author->ID;
				$url_type = 'author';
			}

			// allow developers to set/override the targeting ID or url type or position manually
			$id       = apply_filters( $this->option_prefix . 'set_targeting_id', $id );
			$url_type = apply_filters( $this->option_prefix . 'set_targeting_url_type', $url_type );
			$position = apply_filters( $this->option_prefix . 'set_targeting_position', $position );

			$targeting = '';

			if ( '' !== $position || '' !== $id || '' !== $url_type ) {
				$targeting = array();

				if ( '' !== $position ) {
					$targeting['pos'] = $position;
				}

				if ( '' !== $id ) {
					$targeting['id'] = esc_attr( $id );
				}

				if ( '' !== $url_type ) {
					$targeting['url_type'] = esc_attr( $url_type );
				}

				$targeting = json_encode( $targeting, JSON_FORCE_OBJECT );
			}

			if ( '' !== $targeting ) {
				$targeting = ',targeting: ' . $targeting;
			}

			$arcads_prerender = $this->lazy_loaded_or_not( $matching_ad_code['url_vars']['tag_id'] );

			$output_script = "<div><script>arcAds.registerAd({id: 'acm-ad-tag-" . esc_attr( $matching_ad_code['url_vars']['tag_id'] ) . "',slotName: '" . esc_attr( $matching_ad_code['url_vars']['tag_name'] ) . "',dimensions: " . json_encode( $unit_sizes ) . $targeting . $arcads_prerender . ',});</script></div>';

		}

		if ( isset( $matching_ad_code['url_vars']['tag_header'] ) && '' !== $matching_ad_code['url_vars']['tag_header'] ) {
			$tag_header = '<header class="m-ad-region-notice">' . $matching_ad_code['url_vars']['tag_header'] . '</header>';
		} else {
			$tag_header = '';
		}

		if ( isset( $matching_ad_code['url_vars']['tag_footer'] ) && '' !== $matching_ad_code['url_vars']['tag_footer'] ) {
			$tag_footer = '<footer class="m-ad-region-notice">' . $matching_ad_code['url_vars']['tag_footer'] . '</footer>';
		} else {
			$tag_footer = '';
		}

		$tags_no_border_or_text = explode( ', ', get_option( $this->option_prefix . 'tags_no_border_or_text', '' ) );
		$tags_no_border_or_text = array_map( 'trim', $tags_no_border_or_text );
		$ad_border              = false;
		$text_before_ad         = '';
		$text_after_ad          = '';

		if ( ! in_array( $matching_ad_code['url_vars']['tag_id'], $tags_no_border_or_text, true ) ) {
			$ad_border      = get_option( $this->option_prefix . 'border_around_ads', '0' );
			$text_before_ad = get_option( $this->option_prefix . 'text_before_ad', '' );
			$text_after_ad  = get_option( $this->option_prefix . 'text_after_ad', '' );

			// different before/after text for embed ads
			$is_embed_ad     = false;
			$multiple_embeds = get_option( $this->option_prefix . 'multiple_embeds', '0' );
			if ( is_array( $multiple_embeds ) ) {
				$multiple_embeds = $multiple_embeds[0];
			}

			// if multiples are enabled, check to see if the id is in the embed tag range
			if ( '1' === $multiple_embeds ) {
				$embed_prefix        = get_option( $this->option_prefix . 'embed_prefix', 'x' );
				$start_embed_id      = get_option( $this->option_prefix . 'start_tag_id', 'x100' );
				$start_embed_count   = intval( str_replace( $embed_prefix, '', $start_embed_id ) ); // ex 100
				$end_embed_id        = get_option( $this->option_prefix . 'end_tag_id', 'x110' );
				$end_embed_count     = intval( str_replace( $embed_prefix, '', $end_embed_id ) ); // ex 110
				$current_embed_count = intval( str_replace( $embed_prefix, '', $matching_ad_code['url_vars']['tag_id'] ) ); // ex 108
				if ( ( $current_embed_count >= $start_embed_count && $current_embed_count <= $end_embed_count ) ) {
					$is_embed_ad = true;
				}
			}
			// if there is an auto embed ad, we should auto load it also.
			$auto_embed = get_option( $this->option_prefix . 'auto_embed_position', 'Middle' );
			if ( $auto_embed === $matching_ad_code['url_vars']['tag_id'] ) {
				$is_embed_ad = true;
			}

			if ( ! is_singular() ) {
				$is_embed_ad = false;
			}

			if ( true === $is_embed_ad ) {
				if ( '1' !== $ad_border ) {
					$ad_border = get_option( $this->option_prefix . 'border_around_embed_ads', '0' );
				}
				$text_before_ad = get_option( $this->option_prefix . 'embed_text_before_ad', $text_before_ad );
				$text_after_ad  = get_option( $this->option_prefix . 'embed_text_after_ad', $text_after_ad );
			}

			if ( '' !== $text_before_ad ) {
				$text_before_ad = '<div class="a-text-around-ad a-text-before-ad">' . apply_filters( 'the_content', $text_before_ad ) . '</div>';
			}
			if ( '' !== $text_after_ad ) {
				$text_after_ad = '<div class="a-text-around-ad a-text-after-ad">' . apply_filters( 'the_content', $text_after_ad ) . '</div>';
			}
		}

		$output_html = '<div class="acm-ad ad-' . $matching_ad_code['url_vars']['tag_id'] . '" id="acm-ad-tag-' . $matching_ad_code['url_vars']['tag_id'] . '"></div>';

		$more_classes = '';
		if ( '1' === $ad_border && ! in_array( $matching_ad_code['url_vars']['tag_id'], $tags_no_border_or_text, true ) ) {
			$more_classes = ' acm-ad-container-bordered';
		}

		if ( '' !== $text_before_ad || '' !== $text_after_ad || '1' === $ad_border ) {
			$output_html = '<div class="acm-ad-container' . $more_classes . '">' . $text_before_ad . $output_html . $text_after_ad . '</div>';
		}

		if ( ! isset( $output_html ) ) {
			$output_html = '';
		}
		$output = array(
			'html'   => $output_html,
			'script' => $output_script,
		);

		// use the function we already have for the placeholder ad to filter what we're displaying
		if ( function_exists( 'acm_no_ad_users' ) ) {
			$output = acm_no_ad_users( $output, $tag_id );
		} else {
			$output = $output['html'] . $output['script'];
		}

		if ( '' !== $tag_header || '' !== $tag_footer ) {
			$output = '<div class="acm-ad-wrapper ad-' . $matching_ad_code['url_vars']['tag_id'] . '">' . $tag_header . $output . $tag_footer . '</div>';
		}

		return $output;
	}

	/**
	 * Return a prerender JavaScript method if this ad should be lazy loaded
	 *
	 * @param string $output_html   The non lazy loaded html
	 * @param string $tag_id        The ad tag id
	 *
	 * @return $arcads_prerender    The prerender method for arcads
	 *
	 */
	private function lazy_loaded_or_not( $tag_id ) {
		// are we lazy loading or not
		$lazy_load        = false;
		$arcads_prerender = '';

		if ( is_feed() ) {
			return false;
		}

		if ( true === $this->lazy_load_all ) {
			$lazy_load = true;
		} elseif ( true === $this->lazy_load_embeds ) {
			$lazy_load = false; // we only want to lazy load the embeds, so set it to true when necessary
			// lazy load embeds only
			$multiple_embeds = get_option( $this->option_prefix . 'multiple_embeds', '0' );
			if ( is_array( $multiple_embeds ) ) {
				$multiple_embeds = $multiple_embeds[0];
			}

			// if multiples are enabled, check to see if the id is in the embed tag range
			if ( '1' === $multiple_embeds ) {
				$embed_prefix        = get_option( $this->option_prefix . 'embed_prefix', 'x' );
				$start_embed_id      = get_option( $this->option_prefix . 'start_tag_id', 'x100' );
				$start_embed_count   = intval( str_replace( $embed_prefix, '', $start_embed_id ) ); // ex 100
				$end_embed_id        = get_option( $this->option_prefix . 'end_tag_id', 'x110' );
				$end_embed_count     = intval( str_replace( $embed_prefix, '', $end_embed_id ) ); // ex 110
				$current_embed_count = intval( str_replace( $embed_prefix, '', $tag_id ) ); // ex 108
				if ( ( $current_embed_count >= $start_embed_count && $current_embed_count <= $end_embed_count ) ) {
					$lazy_load = true;
				}
			}
			// if there is an auto embed ad, we should auto load it also.
			$auto_embed = get_option( $this->option_prefix . 'auto_embed_position', 'Middle' );
			if ( $auto_embed === $tag_id ) {
				$lazy_load = true;
			}

			if ( ! is_singular() ) {
				$lazy_load = false;
			} // if we're only supposed to lazy load embeds, don't do it unless this is a singular post

			// allow individual posts to disable lazyload. this can be useful in the case of unresolvable javascript conflicts.
			if ( is_singular() ) {
				global $post;
				if ( get_post_meta( $post->ID, $this->option_prefix . 'post_prevent_lazyload', true ) ) {
					$lazy_load = false;
				}
			}
		}
		// if lazy load is true for this ad, add a prerender method to the script for lazy loading
		if ( true === $lazy_load ) {
			$arcads_prerender = ',prerender: window.addLazyLoad';
		}

		return $arcads_prerender;
	}

	/**
	* Enqueue JavaScript and CSS for front end
	*
	*/
	public function add_scripts_and_styles() {

		$arcads_dependencies = array();
		$css_dependencies    = array();

		// put the polyfill for intersectionobserver here
		if ( true === filter_var( get_option( $this->option_prefix . 'use_intersectionobserver_polyfill', false ), FILTER_VALIDATE_BOOLEAN ) ) {
			wp_enqueue_script( 'intersectionobserverpolyfill', plugins_url( 'assets/js/intersection-observer.min.js', $this->file ), array(), $this->version, true );
			$arcads_dependencies[] = 'intersectionobserverpolyfill';
		}
		wp_enqueue_script( 'arcads', plugins_url( 'assets/js/arcads.min.js', $this->file ), $arcads_dependencies, $this->arcads_library_version, false );
		wp_add_inline_script(
			'arcads',
			"
			const arcAds = new ArcAds({
				dfp: {
					id: '" . $this->dfp_id . "',
					collapseEmptyDivs: '" . $this->collapse_empty_divs . "',
				}
			});
			"
		);
		if ( true === $this->lazy_load_all || true === $this->lazy_load_embeds ) {
			// allow individual posts to disable lazyload. this can be useful in the case of unresolvable javascript conflicts.
			if ( is_singular() ) {
				global $post;
				if ( get_post_meta( $post->ID, 'wp_lozad_lazyload_prevent_lozad_lazyload', true ) ) {
					return;
				}
			}
			wp_add_inline_script(
				'arcads',
				"
				window.addLazyLoad = function( ad ) {
					return new Promise( function( resolve, reject ) {
						// The 'ad' argument will provide information about the unit
						var this_ad_id = ad.adId;
						var options    = {
						  rootMargin: '300px 0px'
						}
						// If you do not resolve the promise the advertisement will not display
						function handler( entries, observer ) {
						  for ( entry of entries ) {
						    if ( entry.isIntersecting ) {
						      resolve();
						    }
						  }
						}
						let observer = new IntersectionObserver( handler, options );
						observer.observe( document.getElementById( this_ad_id ) );
					});
				}
				"
			);
		}

		wp_enqueue_style( $this->slug . '-front-end', plugins_url( 'assets/css/' . $this->slug . '-front-end.min.css', $this->file ), $css_dependencies, $this->version, 'all' );
	}

	/**
	 * Get regular expression for a specific shortcode
	 *
	 * @param string $shortcode
	 * @return string $regex
	 *
	 */
	private function get_single_shortcode_regex( $shortcode ) {
		// The  $shortcode_tags global variable contains all registered shortcodes.
		global $shortcode_tags;

		// Store the shortcode_tags global in a temporary variable.
		$temp_shortcode_tags = $shortcode_tags;

		// Add only one specific shortcode name to the $shortcode_tags global.
		//
		// Replace 'related_posts_by_tax' with the shortcode you want to get the regex for.
		// Don't include the brackets from a shortcode.
		$shortcode_tags = array( $shortcode => '' );

		// Create the regex for your shortcode.
		$regex = '/' . get_shortcode_regex() . '/s';

		// Restore the $shortcode_tags global.
		$shortcode_tags = $temp_shortcode_tags;

		// Print the regex.
		return $regex;
	}

	/**
	 * Insert one or more inline ads into the post editor, depending on the settings. Editors can then rearrange them as desired.
	 *
	 * @param string $content
	 * @param int $post_id
	 *
	 * @return $content
	 * return the post content into the editor with shortcodes for ads inside it at the proper places
	 *
	 */
	public function insert_inline_ad_in_editor( $content, $post_id ) {

		/*
		// todo: i think this would be nice, but i think it won't work like this
		$user_id = get_current_user_id();
		if ( ! user_can( $user_id, $this->capability ) ) {
			return $content;
		}*/

		$post_type = get_post_type( $post_id );
		$in_editor = true;

		// should we skip rendering ads?
		$should_we_skip = $this->should_we_skip_ads( $content, $post_type, $post_id, $in_editor );
		if ( true === $should_we_skip ) {
			return $content;
		}

		$ad_code_manager = $this->ad_code_manager;

		$content = $this->insert_ads_into_content( $content, true );
		return $content;

	}

	/**
	 * Determine whether the current post should get automatic ad insertion.
	 *
	 * @param string $content
	 * @param string $post_type
	 * @param int $post_id
	 * @param bool $in_editor
	 *
	 * @return bool
	 * return true to skip rendering ads, false otherwise
	 *
	 */
	private function should_we_skip_ads( $content, $post_type, $post_id, $in_editor ) {

		// This is on the story, so we can access the loop
		if ( false === $in_editor ) {
			// Stop if this is not being called In The Loop.
			if ( ! in_the_loop() || ! is_main_query() ) {
				return true;
			}
			if ( ! is_single() && ! is_feed() ) {
				return true;
			}
		} else {
			// Check that there isn't a line starting with `[cms_ad` already.
			// If there is, stop adding automatic short code(s). Assume the user is doing it manually.
			if ( false !== stripos( $content, '[cms_ad' ) || false !== stripos( $content, '<img class="mceItem mceAdShortcode' ) ) {
				return true;
			}
		}

		// Don't add ads if this post is not a supported type
		$post_types = get_option( $this->option_prefix . 'post_types', array( 'post' ) );
		if ( ! in_array( $post_type, $post_types, true ) ) {
			return true;
		}

		// If this post has the option set to not add automatic ads, do not add them to the editor view. If we're not in the editor, ignore this value because they would have been manually added at this point.
		// This field name is stored in the plugin options.
		$field_automatic_name  = get_option( $this->option_prefix . 'prevent_automatic_ads_field', '_post_prevent_arcads_ads' );
		$field_automatic_value = get_option( $this->option_prefix . 'prevent_automatic_ads_field_value', 'on' );

		// If we are in the editor, this determines whether ads automatically get added.
		if ( true === $in_editor && get_post_meta( $post_id, $field_automatic_name, true ) === $field_automatic_value ) {
			return true;
		}

		// In the front end view, if this post has the above option set to not add automatic ads, skip them unless they have been manually added.
		// We can also set this value with a developer hook.
		$prevent_automatic_ads = apply_filters( $this->option_prefix . 'prevent_automatic_ads', false, $post_id );
		$skip_automatic_ads    = false; // default it to false
		if ( get_post_meta( $post_id, $field_automatic_name, true ) === $field_automatic_value || true === $prevent_automatic_ads ) {
			$skip_automatic_ads = true;
		}

		if ( false === $in_editor && true === $skip_automatic_ads && false === stripos( $content, '[cms_ad' ) && false === stripos( $content, '<img class="mceItem mceAdShortcode' ) ) {
			return true;
		}

		// Stop altogether if this post has the option set to not add any ads.
		// This field name is stored in the plugin options.
		$field_name  = get_option( $this->option_prefix . 'prevent_ads_field', '_post_prevent_arcads_ads' );
		$field_value = get_option( $this->option_prefix . 'prevent_ads_field_value', 'on' );
		if ( get_post_meta( $post_id, $field_name, true ) === $field_value ) {
			return true;
		}

		// allow developers to prevent all ads
		$prevent_ads = apply_filters( $this->option_prefix . 'prevent_ads', false, $post_id );
		if ( true === $prevent_ads ) {
			return true;
		}

		// If we don't have any paragraphs, let's skip the ads for this post
		if ( ! stripos( wpautop( $content ), $this->paragraph_end[ $in_editor ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Allow ad sizes to be defined as: arrays, basic width x height, or fluid. This allows for multiple ad sizes for the same unit, as well as allowing for DFP's native fluid ads.
	 *
	 * @param string $content
	 * @param string $post_type
	 * @param int $post_id
	 * @param bool $in_editor
	 *
	 * @return bool
	 * return true to skip rendering ads, false otherwise
	 *
	 */
	public function parse_ad_tag_sizes( $url_vars ) {
		if ( empty( $url_vars ) ) {
			return;
		}
		$unit_sizes_output = '';
		if ( ! empty( $url_vars['sizes'] ) ) {
			// if size is not an array, we assume it is a text field with comma separated sizes. ex 728x90 is one size.
			// this means we run explode twice.
			if ( ! is_array( $url_vars['sizes'] ) ) {
				$url_vars['sizes'] = explode( ',', $url_vars['sizes'] );
				foreach ( $url_vars['sizes'] as $key => $value ) {
					if ( 'fluid' !== $value ) {
						$current_size              = explode( 'x', $value );
						$url_vars['sizes'][ $key ] = array(
							'width'  => $current_size[0],
							'height' => $current_size[1],
						);
					} else {
						$url_vars['sizes'][ $key ] = $value;
					}
				}
			}
			$unit_sizes_output = array();
			foreach ( $url_vars['sizes'] as $unit_size ) {
				if ( is_array( $unit_size ) ) {
					$unit_sizes_output[] = array(
						(int) $unit_size['width'],
						(int) $unit_size['height'],
					);
				} else {
					$unit_sizes_output[] = $unit_size;
				}
			}
		} else { // fallback for old style width x height
			if ( isset( $url_vars['width'] ) && isset( $url_vars['height'] ) ) {
				$unit_sizes_output = array(
					(int) $url_vars['width'],
					(int) $url_vars['height'],
				);
			} else {
				$unit_sizes_output = 'fluid';
			}
		}
		return $unit_sizes_output;
	}

}
