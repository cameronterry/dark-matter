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
abstract class CustomQuery {
	/**
	 * The amount of found sites for the current query.
	 *
	 * @var int
	 */
	public $found_records = 0;

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
	 * Query variables used for constructing the WHERE clause.
	 *
	 * @var array
	 */
	protected $query_vars_where = [];

	/**
	 * List of records located by the query.
	 *
	 * @var array
	 */
	public $records = [];

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
				'count'               => false,
				'fields'              => '',
				'ID'                  => '',
				'no_found_rows'       => false,
				'number'              => 10,
				'offset'              => '',
				'orderby'             => 'id',
				'order'               => 'ASC',
				'page'                => 1,
				'search'              => '',
				'search_columns'      => [],
				'update_record_cache' => true,
			]
		);

		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	/**
	 * Return fields and definitions.
	 *
	 * @return array
	 */
	abstract protected function get_fields();

	/**
	 * Custom name for actions and filters within the custom query.
	 *
	 * @return string Name to be used with actions and filters.
	 */
	abstract protected function get_hook_name();

	/**
	 * Get the default values for the query.
	 *
	 * @param array $general_defaults General defaults, including the cache variables and pagination.
	 * @return array
	 */
	protected function get_query_defaults( $general_defaults = [] ) {
		$this->query_vars_where = $this->get_fields();
		return array_merge( $this->query_vars_where, $general_defaults );
	}

	abstract protected function get_id_column();

	/**
	 * Retrieve the name of the custom table.
	 *
	 * @return string Name of the custom table. Must include `$wpdb->prefix` if site specific or `$wpdb->base_prefix` or network specific.
	 */
	abstract protected function get_tablename();

	/**
	 * Retrieves a list of record IDs matching the query vars.
	 *
	 * @return int|array
	 */
	public function get_record_ids() {
		$order = $this->parse_order( $this->query_vars['order'] );

		/**
		 * Handle pagination.
		 */
		$page   = absint( $this->query_vars['page'] );
		$number = absint( $this->query_vars['number'] );

		$page_start = absint( ( $page - 1 ) * $this->query_vars['number'] ) . ', ';
		$limits = 'LIMIT ' . $page_start . $number;

		global $wpdb;

		/**
		 * Fields
		 */
		if ( $this->query_vars['count'] ) {
			$fields = 'COUNT(*)';
		} else {
			$fields = "{$this->get_tablename()}.{$this->get_id_column()}";
		}

		$join = '';

		foreach ( $this->query_vars_where as $name => $default ) {
			if ( ! isset( $this->query_vars[ $name ] ) || empty( $this->query_vars[ $name ] ) ) {
				continue;
			}

			if ( false !== stripos( $name, '__in' ) ) {
				$column_name = strtok( $name, '__' );
				$this->sql_clauses['where'][ $name ] = "{$this->get_tablename()}.$column_name IN ( '" . implode( "', '", $wpdb->_escape( $this->query_vars[ $name ] ) ) . "' )";
			} elseif ( false !== stripos( $name, '__not__in' ) ) {
				$column_name = strtok( $name, '__' );
				$this->sql_clauses['where'][ $name ] = "{$this->get_tablename()}.$column_name NOT IN ( '" . implode( "', '", $wpdb->_escape( $this->query_vars[ $name ] ) ) . "' )";
			} else {
				$this->sql_clauses['where'][ $name ] = "{$this->get_tablename()}.$name = '" . $wpdb->_escape( $this->query_vars[ $name ] ) . "'";
			}
		}

		$where = implode( ' AND ', $this->sql_clauses['where'] );

		if ( ! empty( $where ) ) {
			$where = "WHERE {$where}";
		}

		$found_rows = '';
		if ( ! $this->query_vars['no_found_rows'] ) {
			$found_rows = 'SQL_CALC_FOUND_ROWS';
		}

		$this->sql_clauses['select']  = "SELECT $found_rows $fields";
		$this->sql_clauses['from']    = "FROM {$this->get_tablename()} $join";
		$this->sql_clauses['limits']  = $limits;

		$this->request = "
			{$this->sql_clauses['select']}
			{$this->sql_clauses['from']}
			{$where}
			{$this->sql_clauses['groupby']}
			{$this->sql_clauses['orderby']}
			{$this->sql_clauses['limits']}
		";

		if ( $this->query_vars['count'] ) {
			return (int) $wpdb->get_var( $this->request );
		}

		$record_ids = $wpdb->get_col( $this->request );

		return $record_ids;
	}

	/**
	 * Retrieves a list of records matching the query vars.
	 *
	 * @return array|int List of record objects, a list of record IDs when 'fields' is set to 'ids', or the number of
	 *                   records when 'count' is passed as a query var.
	 */
	public function get_records() {
		$this->parse_query();

		/**
		 * Fires before records are retrieved.
		 *
		 * @param CustomQuery $query Current instance of `Custom_Table_Query` (passed by reference)
		 */
		do_action_ref_array( "pre_get_{$this->get_hook_name()}", [ &$this ] );

		$record_data = null;

		/**
		 * Filter the record data before the database query takes place. Non-null value to bypass default record
		 * queries.
		 *
		 * @param array|int|null     $record_data Return an array of site data to short-circuit the record query, record
		 *                                        count as an integer if 'count' is set, or null to run normal queries.
		 * @param CustomQuery $query       The Custom_Table_Query instance, passed by reference.
		 * @return array|int|null
		 */
		$record_data = apply_filters_ref_array( "{$this->get_hook_name()}_pre_query", [ $record_data, &$this ] );
		if ( null !== $record_data ) {
			if ( is_array( $record_data ) && ! $this->query_vars['count'] ) {
				$this->records = $record_data;
			}

			return $record_data;
		}

		/**
		 * Remove anything not supported by the class.
		 */
		$_args = wp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) );

		/**
		 * Ignore fields which do not affect the outcome of the query for the cache key.
		 */
		unset( $_args['fields'], $_args['update_records_cache'] );

		$record_ids = $this->get_record_ids();
		if ( ! empty( $record_ids ) ) {
			$this->set_found_records();
		}

		$this->records = $record_ids;
		return $record_ids;
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
	 * Parses arguments passed to the query with default query parameters.
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @return void
	 */
	public function parse_query( $query = '' ) {
		if ( empty( $query ) ) {
			$query = $this->query_vars;
		}

		$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );

		do_action_ref_array( "parse_{$this->get_hook_name()}_query", [ &$this ] );
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
	 * Populates found_records and max_num_pages properties for the current query if the limit clause was used.
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return void
	 */
	protected function set_found_records() {
		global $wpdb;

		if ( $this->query_vars['number'] && ! $this->query_vars['no_found_rows'] ) {
			/**
			 * Filters the query used to retrieve found record count.
			 *
			 * @param string             $found_records_query SQL query. Default 'SELECT FOUND_ROWS()'.
			 * @param CustomQuery $site_query          The `Custom_Table_Query` instance.
			 */
			$found_records_query = apply_filters( "found_{$this->get_hook_name()}_query", 'SELECT FOUND_ROWS()', $this );

			$this->found_records = (int) $wpdb->get_var( $found_records_query );

			if ( ! empty( $this->query_vars['number'] ) ) {
				$this->max_num_pages = ceil( $this->found_records / $this->query_vars['number'] );
			}
		}
	}
}
