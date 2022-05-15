<?php
/**
 * Storage class for individual instructions.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Data;

/**
 * Class InstructionEntry
 */
class InstructionEntry implements \DarkMatter\Interfaces\Storeable {
	/**
	 * Convert the Instruction Entry to JSON.
	 *
	 * @return string JSON string of the Instruction Entry.
	 */
	public function to_json() {
		return '';
	}

	/**
	 * Populate this Instruction Entry with the data from JSON.
	 *
	 * @param string $json JSON string to extract the data from.
	 * @return void
	 */
	public function from_json( $json = '' ) {

	}

	/**
	 * Save the Instruction Entry.
	 *
	 * @return string|false URL key on success. False otherwise.
	 */
	public function save() {
		return '';
	}
}
