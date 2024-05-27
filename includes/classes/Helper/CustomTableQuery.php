<?php
/**
 * Class for supporting a custom query for the Custom Table class.
 *
 * @see \DarkMatter\Helper\CustomTable
 *
 * @package DarkMatterPlugin\Helper
 */

namespace DarkMatter\Helper;

/**
 * Class CustomTableQuery
 */
abstract class CustomTableQuery extends CustomQuery {
	/**
	 * Custom table defintion.
	 *
	 * @var CustomTable
	 */
	protected $custom_table;

	/**
	 * Default values for the custom table.
	 *
	 * @var array
	 */
	private $var_defaults = [];

	/**
	 * Custom hook name.
	 *
	 * @var string
	 */
	private $hook_name = '';

	/**
	 * Table to be queried.
	 *
	 * @var string
	 */
	private $table_name = '';

	/**
	 * Constructor.
	 *
	 * @param array       $query        Query arguments.
	 * @param CustomTable $custom_table Custom table class to use.
	 * @param string      $hook_name    Customise the hook name. Will default to the table name, minus the prefix.
	 */
	public function __construct( $query, $custom_table, $hook_name = '' ) {
		$this->init( $custom_table, $hook_name );
		parent::__constructor( $query );
	}

	/**
	 * Define the custom fields for the query.
	 *
	 * @return array
	 */
	private function define_fields() {
		$columns = $this->custom_table->get_columns();
		if ( empty( $columns ) ) {
			return [];
		}

		$types = $this->get_types();

		$var_defaults = [];
		foreach ( $columns as $column_name => $column ) {
			/**
			 * Skip non-queryable columns.
			 */
			if ( isset( $column['queryable'] ) && ! $column['queryable'] ) {
				continue;
			}

			/**
			 * Skip unsupported types.
			 */
			if ( ! array_key_exists( $column['type'], $types ) ) {
				continue;
			}

			$type = $types[ $column['type'] ];
			if ( isset( $column['nullable'] ) && $column['nullable'] ) {
				$value = null;
			} elseif ( ! empty( $column['default'] ) ) {
				$value = $column['default'];

				if ( $type['numeric'] ) {
					$value = absint( $value );
				}
			} else {
				$value = null;
			}

			$var_defaults[ $column_name ] = $value;

			/**
			 * Add the __in and __not_in support.
			 */
			if ( isset( $column['queryable_in'] ) && $column['queryable_in'] ) {
				$var_defaults[ "{$column_name}__in" ]     = [];
				$var_defaults[ "{$column_name}__not_in" ] = [];
			}
		}

		return $var_defaults;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_fields() {
		return $this->var_defaults;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_hook_name() {
		return $this->hook_name;
	}

	/**
	 * Return the ID - aka. Primary Key - column.
	 *
	 * @return string
	 */
	protected function get_id_column() {
		return $this->custom_table->get_primary_key();
	}

	/**
	 * Return a record object, usually a class of the Data.
	 *
	 * @param int|string $record_id Record ID.
	 * @return mixed
	 */
	public abstract function get_record( $record_id );

	/**
	 * Return an array of full objects.
	 *
	 * @return array|int
	 */
	public function get_records() {
		$record_ids = parent::get_records();
		if ( 'ids' === $this->query_vars['fields'] ) {
			return $record_ids;
		}

		$this->records = array_map( [ $this, 'get_record' ], $record_ids );
		return $this->records;
	}

	/**
	 * Return the table name. Defined in the constructor.
	 */
	protected function get_tablename() {
		return $this->table_name;
	}

	/**
	 * Return a set of supported (by this class) SQL types. The bool value states if the key can be "auto incremented".
	 *
	 * @return array
	 */
	private function get_types() {
		return [
			/**
			 * Datetime types.
			 */
			'DATETIME'    => [
				'format'  => 'DATETIME',
				'numeric' => false,
			],
			/**
			 * Numeric types.
			 */
			'BIGINT'      => [
				'format'  => 'BIGINT(%s)',
				'numeric' => true,
			],
			'INT'         => [
				'format'  => 'INT(%s)',
				'numeric' => true,
			],
			'TINYINT'     => [
				'format'  => 'TINYINT(%s)',
				'numeric' => true,
			],
			/**
			 * String types.
			 */
			'CHAR'        => [
				'format'  => 'CHAR(%s)',
				'numeric' => false,
			],
			'VARCHAR'     => [
				'format'  => 'VARCHAR(%s)',
				'numeric' => false,
			],
			'LONGTEXT'    => [
				'format'  => 'LONGTEXT',
				'numeric' => false,
			],
		];
	}

	/**
	 * Initialise the custom table and hook.
	 *
	 * @param CustomTable $custom_table Custom table class to use.
	 * @param string      $hook_name    Customise the hook name. Will default to the table name, minus the prefix.
	 * @return void
	 */
	protected function init( $custom_table, $hook_name = '' ) {
		$this->custom_table = $custom_table;

		$this->table_name = $this->custom_table->get_tablename();

		if ( ! empty( $hook_name ) ) {
			$this->hook_name = $hook_name;
		} else {
			global $wpdb;
			$this->hook_name = str_ireplace( $wpdb->prefix, '', $this->table_name );
		}

		$this->var_defaults = $this->query_vars_where = $this->define_fields();
	}

	/**
	 * Adds records from the given IDs to the cache that do not already exist in the cache.
	 *
	 * @param array $record_ids Record IDs.
	 * @return void
	 */
	protected function prime_caches( $record_ids ) {
		$this->custom_table->prime_caches( $record_ids );
	}
}
