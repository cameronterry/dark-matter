<?php
/**
 * Enumeration for the type of Instructions used in Advanced Cache.
 *
 * @package DarkMatter
 */

namespace DarkMatter\Enums;

/**
 * Class InstructionType
 */
abstract class InstructionType {
	/**
	 * Singular, one-off: instructions that are executed once and never again.
	 */
	const Ephemeral  = 1;

	/**
	 * Persistence: instructions that are always run, usually containing logic which is run on every page view.
	 */
	const Perpetual = 2;
}
