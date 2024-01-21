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
abstract class Custom_Table {
	/**
	 * Field definition.
	 *
	 * @var array
	 */
	protected static $fields = [];

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

		$columns = [];
		foreach ( $columns as $name => $definition ) {
			$definition = wp_parse_args(
				$definition,
				[
					'default'      => '',
					'queryable'    => false,
					'type'         => '',
					'type_storage' => 0,
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
			if ( false !== stripos( '%s', $type ) ) {
				if ( empty( $definition['type_storage'] ) ) {
					return false;
				}

				$column = sprintf(
					'%1$s %2$s',
					$name,
					sprintf( $type, $definition['type_storage'] )
				);
			} else {
				$column = $name;
			}

			/**
			 * Handle extras.
			 */
			if ( isset( $definition['nullable'] ) ) {
				$column .= ' ' . $definition['nullable'] ? 'NULL' : 'NOT NULL';
			}

			if ( isset( $definition['default'] ) ) {
				$column .= ' ' . sprintf( 'DEFAULT \'%1$s\'', $definition['default'] );
			}

			if ( isset( $definition['autoincrement'] ) && $definition['autoincrement'] && $types[ $definition['type'] ] ) {
				$column .= ' AUTO_INCREMENT';
			}

			$columns[] = $column;
		}

		return $columns;
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
	protected abstract function get_columns();

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
	protected abstract function get_primary_key();

	/**
	 * The name of the database table.
	 *
	 * @return string
	 */
	protected abstract function get_tablename();

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
			'DATETIME'    => false,
			/**
			 * Numeric types.
			 */
			'BIGINT(%s)'  => true,
			'INT(%s)'     => true,
			'TINYINT(%s)' => true,
			/**
			 * String types.
			 */
			'CHAR(%s)'    => false,
			'VARCHAR(%s)' => false,
			'LONGTEXT'    => false,
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
			$body[] = implode( ',' . PHP_EOL, $columns );
		}

		$charset_collate = $this->get_charset_collate();

		$sql = sprintf(
			'CREATE TABLE %1$s (%2$s) %3$s',
			self::$tablename,
			implode( ',' . PHP_EOL, $body ),
			$charset_collate
		);

		return dbDelta( $sql );
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
}
