<?php

class MinnPost_ACM_DFP_Async_Ad_Panel_Table extends ACM_WP_List_Table {
	/**
	 * Register table settings
	 *
	 * @uses parent::__construct
	 * @return null
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'arcads_acm_wp_list_table', //Singular label
				'plural'   => 'arcads_acm_wp_list_table', //plural label, also this well be one of the table css class
				'ajax'     => true,
			)
		);
	}

	/**
	 * @return array The columns that shall be used
	 */
	function filter_columns() {
		return array(
			'cb'           => '<input type="checkbox" />',
			'id'           => __( 'ID', 'ad-code-manager' ),
			'tag'          => __( 'Tag', 'ad-code-manager' ),
			'tag_id'       => __( 'Tag ID', 'ad-code-manager' ),
			'tag_name'     => __( 'Tag Name', 'ad-code-manager' ),
			'priority'     => __( 'Priority', 'ad-code-manager' ),
			'operator'     => __( 'Logical Operator', 'ad-code-manager' ),
			'conditionals' => __( 'Conditionals', 'ad-code-manager' ),
		);
	}

	/**
	 * This is nuts and bolts of table representation
	 */
	function get_columns() {
		add_filter( 'acm_list_table_columns', array( $this, 'filter_columns' ) );
		return parent::get_columns();
	}

	/**
	 * Set which columns can be sortable
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'tag'          => array( 'tag', false ),
			'tag_id'       => array( 'tag_id', false ),
			'tag_name'     => array( 'tag_name', false ),
			'priority'     => array( 'priority', false ),
			'operator'     => array( 'operator', false ),
			'conditionals' => array( 'conditionals', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Sort the columns. Because of how the plugin stores array parameters, some of the structures here have to be defined manually (conditionals, especially)
	 */
	function usort_reorder( $a, $b ) {

		// If no sort, default to tag
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'post_id';
		// If no order, default to asc
		$order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';

		// Determine sort order
		if ( isset( $a['url_vars'][ $orderby ] ) ) {
			$result = strcmp( $a['url_vars'][ $orderby ], $b['url_vars'][ $orderby ] );
		} elseif ( isset( $a[ $orderby ] ) ) {
			if ( is_array( $a[ $orderby ] ) ) {
				if ( 'conditionals' === $orderby ) {
					$result = strcmp( $a[ $orderby ][0]['function'], $b[ $orderby ][0]['function'] );
				}
			} elseif ( isset( $a[ $orderby ] ) ) {
				$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
			}
		}

		// Send final sort direction to usort
		return ( 'asc' === $order ) ? $result : -$result;
	}

	/**
	 * Prepare table data. We have to manually keep this in sync with the plugin's version because it doesn't seem like something we can get from the parent itself
	 */
	function prepare_items() {

		global $ad_code_manager;

		$screen = get_current_screen();

		$this->items = $ad_code_manager->get_ad_codes();

		if ( empty( $this->items ) ) {
			return;
		}

		/* -- Pagination parameters -- */
		//Number of elements in your table?
		$totalitems = count( $this->items ); //return the total number of affected rows

		//How many to display per page?
		$perpage = apply_filters( 'acm_list_table_per_page', 50 );

		//Which page is this?
		$paged = ! empty( $_GET['paged'] ) ? intval( $_GET['paged'] ) : '';

		//Page Number
		if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) {
			$paged = 1;
		}
		//How many pages do we have in total?

		$totalpages = ceil( $totalitems / $perpage );

		//adjust the query to take pagination into account

		if ( ! empty( $paged ) && ! empty( $perpage ) ) {
			$offset = ( $paged - 1 ) * $perpage;
		}

		/* -- Register the pagination -- */
		$this->set_pagination_args(
			array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page'    => $perpage,
			)
		);
		//The pagination links are automatically built according to those parameters

		/* -- Register the Columns -- */
		$columns               = $this->get_columns();
		$hidden                = array(
			'id',
		);
		$this->_column_headers = array( $columns, $hidden, $this->get_sortable_columns() );

		/**
		 * Items are set in Ad_Code_Manager class
		 * All we need to do is to prepare it for pagination
		 */
		$this->items = array_slice( $this->items, $offset, $perpage );

		// this is the part where we modify the items. everything else is from the parent class.
		usort( $this->items, array( $this, 'usort_reorder' ) );
		$this->items = $this->items;
	}

	/**
	 * Output the tag cell in the list table
	 */
	function column_tag( $item ) {
		$output  = isset( $item['tag'] ) ? esc_html( $item['tag'] ) : esc_html( $item['url_vars']['tag'] );
		$output .= $this->row_actions_output( $item );
		return $output;
	}

}
