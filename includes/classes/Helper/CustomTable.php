<?php
/**
 * Helper for handling and managing the creation of custom tables.
 *
 * @package DarkMatter\Helper
 */

namespace DarkMatter\Helper;

/**
 * Abstract Class Custom_Table
 */
abstract class CustomTable {
	/**
	 * Field definition.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Helper method for adding a record to the Custom Table.
	 *
	 * @param array $data Data, with keys matching the columns of the Custom Table.
	 * @return bool|int|\mysqli_result|null
	 */
	protected function add( $data = [] ) {
		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->parse_args( $data );
		if ( empty( $data ) ) {
			return false;
		}

		global $wpdb;
		return $wpdb->insert( $this->get_tablename(), $data );
	}

	/**
	 * Translates the column definitions into something supported by SQL's Create Table.
	 *
	 * @return string[]|false
	 */
	private function define_columns() {
		$columns = $this->get_columns();
		if ( empty( $columns ) ) {
			return false;
		}

		$types = $this->get_types();

		$columns_sql = [];
		foreach ( $columns as $name => $definition ) {
			$definition = wp_parse_args(
				$definition,
				[
					'autoincrement' => false,
					'default'       => false,
					'nullable'      => true,
					'queryable'     => false,
					'type'          => '',
					'type_storage'  => 0,
				]
			);

			/**
			 * Handle the SQL type.
			 */
			if ( ! array_key_exists( $definition['type'], $types ) ) {
				return false;
			}

			/**
			 * Handle the storage value if required.
			 */
			$type = $types[ $definition['type'] ];
			if ( false !== stripos( $type['format'], '%s' ) ) {
				if ( empty( $definition['type_storage'] ) ) {
					return false;
				}

				$column = sprintf(
					'%1$s %2$s',
					$name,
					sprintf( $type['format'], $definition['type_storage'] )
				);
			} else {
				$column = $name;
			}

			if ( isset( $definition['nullable'] ) && ! $definition['nullable'] ) {
				$column .= ' NOT NULL';
			}

			if ( isset( $definition['default'] ) && false !== $definition['default'] ) {
				$column .= ' ' . sprintf( 'DEFAULT \'%1$s\'', $definition['default'] );
			}

			if ( isset( $definition['autoincrement'] ) && $definition['autoincrement'] && $type['numeric'] ) {
				$column .= ' AUTO_INCREMENT';
			}

			$columns_sql[] = $column;
		}

		return $columns_sql;
	}

	/**
	 * Define indexes.
	 *
	 * @return array|false
	 */
	protected function define_indexes() {
		$index_definitions = $this->get_indexes();
		if ( empty( $index_definitions ) ) {
			return false;
		}

		$column_definitions = $this->get_columns();

		$indexes = [];
		foreach ( $index_definitions as $name => $columns ) {
			/**
			 * Name must be an alphanumeric string.
			 */
			if ( is_int( $name ) ) {
				return false;
			}

			if ( ! is_array( $columns ) ) {
				$columns = [ $columns ];
			}
			foreach ( $columns as $column ) {
				/**
				 * Remove the max index length, so we can check the column.
				 */
				$column_name = trim( strtok( $column, '(' ) );

				/**
				 * Make sure the column exists.
				 */
				if ( ! array_key_exists( $column_name, $column_definitions ) ) {
					return false;
				}
			}

			$indexes[] = sprintf( 'KEY %1$s (%2$s)', $name, implode( ',', $columns ) );
		}

		return $indexes;
	}

	/**
	 * Define the primary key.
	 *
	 * @return false|string
	 */
	protected function define_primary_key() {
		$primary_key = $this->get_primary_key();
		if ( empty( $primary_key ) ) {
			return false;
		}

		$column_definitions = $this->get_columns();
		if ( ! array_key_exists( $primary_key, $column_definitions ) ) {
			return false;
		}

		/**
		 * Note the double space. This is needed by `dbDelta()`.
		 *
		 * @see dbDelta()
		 */
		return sprintf( 'PRIMARY KEY  (%1$s)', $primary_key );
	}

	/**
	 * Define the columns for the table.
	 *
	 * @return array
	 */
	public abstract function get_columns();

	/**
	 * Retrieve a single record.
	 *
	 * @param int|string $id     ID to return.
	 * @param string     $output Optional. The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
	 *                           correspond to an stdClass object, an associative array, or a numeric array,
	 *                           respectively. Default OBJECT.
	 * @return array|object|\stdClass|null
	 */
	public function get_record( $id, $output = OBJECT ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->get_tablename()} WHERE {$this->get_primary_key()} = %s",
				$id
			),
			$output
		);
	}

	/**
	 * Define indexes for the table. Note: the array keys must match columns defined in `$this->get_columns()`.
	 *
	 * @return array Key is name of the indexes, value is an array of keys.
	 */
	protected abstract function get_indexes();

	/**
	 * Primary Key.
	 *
	 * @return string
	 */
	public abstract function get_primary_key();

	/**
	 * The name of the database table.
	 *
	 * @return string
	 */
	public abstract function get_tablename();

	/**
	 * Return a set of supported (by this class) SQL types. The bool value states if the key can be "auto incremented".
	 *
	 * @return array
	 */
	protected function get_types() {
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
	 * Creates a custom table based on the fields' definition.
	 *
	 * @see dbDelta()
	 *
	 * @return array|false
	 */
	public function create_update_table() {
		$body = [];

		$columns = $this->define_columns();
		if ( ! $columns ) {
			return false;
		}

		$body[] = implode( ',' . PHP_EOL, $columns );

		$primary_key = $this->define_primary_key();
		if ( ! empty( $primary_key ) ) {
			$body[] = $primary_key;
		}

		$indexes = $this->define_indexes();
		if ( ! empty( $indexes ) ) {
			$body[] = implode( ',' . PHP_EOL, $indexes );
		}

		$charset_collate = $this->get_charset_collate();

		$sql = sprintf(
			'CREATE TABLE %1$s (
%2$s
)
%3$s',
			$this->get_tablename(),
			implode( ',' . PHP_EOL, $body ),
			$charset_collate
		);

		return dbDelta( $sql );
	}

	/**
	 * Delete a database table record.
	 *
	 * @param int $id Identifier of the record to delete.
	 * @return bool|int|\mysqli_result|null
	 */
	public function delete( $id ) {
		global $wpdb;
		return $wpdb->delete(
			$this->get_tablename(),
			[
				$this->get_primary_key() => $id,
			],
		);
	}

	/**
	 * Charset for the table. Defaults to `$wpdb->get_charset_collate()`.
	 *
	 * @see wpdb::get_charset_collate()
	 *
	 * @return string
	 */
	protected function get_charset_collate() {
		global $wpdb;
		return $wpdb->get_charset_collate();
	}

	/**
	 * Parsed the data args prior to database entry.
	 *
	 * @param array $data Data to be parsed.
	 * @return array|false
	 */
	protected function parse_args( $data ) {
		if ( empty( $data[ $this->get_primary_key() ] ) ) {
			return false;
		}

		$columns = $this->get_columns();
		if ( empty( $columns ) ) {
			return false;
		}

		$_args = wp_array_slice_assoc( $data, array_keys( $columns ) );
		if ( empty( $_args ) ) {
			return false;
		}

		foreach ( $_args as $field => $value ) {
			if ( ! empty( $columns[ $field ]['sanitize'] ) && is_callable( $columns[ $field ]['sanitize'] ) ) {
				$value = call_user_func_array( $columns[ $field ]['sanitize'], $value );
			} else {
				$value = sanitize_text_field( $value );
			}

			$_args[ $field ] = $value;
		}

		return wp_unslash( $_args );
	}

	/**
	 * Update a database table record.
	 *
	 * @param array $data Record to be updated.
	 * @return bool|int|\mysqli_result|null
	 */
	public function update( $data ) {
		$data = $this->parse_args( $data );
		if ( false === $data ) {
			return false;
		}

		global $wpdb;
		return $wpdb->update(
			$this->get_tablename(),
			$data,
			[
				$this->get_primary_key() => $data[ $this->get_primary_key() ]
			]
		);
	}
}
