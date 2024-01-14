<?php
/**
 * Helper class for querying Custom Tables similar to `WP_Query` and `WP_Site_Query`, with caching and hooks.
 *
 * @package DarkMatter\Helper
 */

namespace DarkMatter\Helper;

/**
 * Abstract class Custom_Table_Query
 */
abstract class Custom_Table_Query {
	/**
	 * The amount of found sites for the current query.
	 *
	 * @var int
	 */
	public $found_records = 0;

	/**
	 * Name used for actions and filters within this class.
	 *
	 * @var string
	 */
	protected $hook_name = '';

	/**
	 * The number of pages.
	 *
	 * @var int
	 */
	public $max_num_pages = 0;

	/**
	 * Query variable default values.
	 *
	 * @var array
	 */
	protected $query_var_defaults = [];

	/**
	 * Query variables.
	 *
	 * @var array
	 */
	protected $query_vars = [];

	/**
	 * SQL for database query.
	 *
	 * @var string
	 */
	public $request = '';

	/**
	 * SQL query clauses.
	 *
	 * @var array
	 */
	protected $sql_clauses = [
		'select'  => '',
		'from'    => '',
		'where'   => [],
		'groupby' => '',
		'orderby' => '',
		'limits'  => '',
	];

	/**
	 * Constructor.
	 */
	public function __constructor( $query = '' ) {
		$this->query_var_defaults = $this->get_query_defaults(
			[
				'count'                  => false,
				'fields'                 => '',
				'ID'                     => '',
				'number'                 => 100,
				'no_found_rows'          => true,
				'offset'                 => '',
				'orderby'                => 'id',
				'order'                  => 'ASC',
				'records_per_page'       => 10,
				'search'                 => '',
				'search_columns'         => [],
				'update_site_cache'      => true,
				'update_site_meta_cache' => true,
			]
		);

		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	/**
	 * Get the default values for the query.
	 *
	 * @param array $general_defaults General defaults, including the cache variables and pagination.
	 * @return array
	 */
	abstract protected function get_query_defaults( $general_defaults = [] );

	/**
	 * Retrieves a list of records matching the query vars.
	 *
	 * @return array|int List of record objects, a list of record IDs when 'fields' is set to 'ids', or the number of
	 *                   records when 'count' is passed as a query var.
	 */
	public function get_records() {
		return [];
	}

	/**
	 * Used internally to generate an SQL string for searching across multiple columns.
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string   $search  Search string.
	 * @param string[] $columns Array of columns to search.
	 * @return string Search SQL.
	 */
	protected function get_search_sql( $search, $columns ) {
		global $wpdb;

		$like = '%' . $wpdb->esc_like( $search ) . '%';

		$searches = [];
		foreach ( $columns as $column ) {
			$searches[] = $wpdb->prepare( "$column LIKE %s", $like );
		}

		return '(' . implode( ' OR ', $searches ) . ')';
	}

	/**
	 * Parses an 'order' query variable and cast it to 'ASC' or 'DESC' as necessary.
	 *
	 * @param string $order The 'order' query variable.
	 * @return string The sanitized 'order' query variable.
	 */
	protected function parse_order( $order ) {
		if ( ! is_string( $order ) || empty( $order ) ) {
			return 'ASC';
		}

		if ( 'ASC' === strtoupper( $order ) ) {
			return 'ASC';
		} else {
			return 'DESC';
		}
	}

	/**
	 * @param string|array $query Array or URL query string of parameters.
	 * @return void
	 */
	public function parse_query( $query = '' ) {
		if ( empty( $query ) ) {
			$query = $this->query_vars;
		}

		$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );

		do_action_ref_array( "parse_{$this->hook_name}_query", [ &$this ] );
	}

	/**
	 * Sets up the WordPress query for retrieving records.
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @return array|int List of record objects, a list of record IDs when 'fields' is set to 'ids', or the number of
	 *                   records when 'count' is passed as a query var.
	 */
	public function query( $query ) {
		$this->query_vars = wp_parse_args( $query );
		return $this->get_records();
	}

	/**
	 * Populates found_sites and max_num_pages properties for the current query if the limit clause was used.
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return void
	 */
	protected function set_found_records() {
		global $wpdb;

		if ( $this->query_vars['number'] && ! $this->query_vars['no_found_rows'] ) {
			/**
			 * Filters the query used to retrieve found site count.
			 *
			 * @param string             $found_sites_query SQL query. Default 'SELECT FOUND_ROWS()'.
			 * @param Custom_Table_Query $site_query        The `Custom_Table_Query` instance.
			 */
			$found_sites_query = apply_filters( "found_{$this->hook_name}_query", 'SELECT FOUND_ROWS()', $this );

			$this->found_records = (int) $wpdb->get_var( $found_sites_query );

			if ( ! empty( $this->query_vars['records_per_page'] ) ) {
				$this->max_num_pages = ceil( $this->found_records / $this->query_vars['records_per_page'] );
			}
		}
	}

	/**
	 * Custom name for actions and filters within the custom query.
	 *
	 * @return string
	 */
	abstract protected function set_hookname();
}
