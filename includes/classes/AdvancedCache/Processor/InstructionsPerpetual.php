<?php
/**
 * Processor for handling perpetual instructions on responses.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Processor;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Instructions\AbstractInstruction;
use DarkMatter\Enums\InstructionType;

/**
 * Class InstructionsPerpetual
 */
class InstructionsPerpetual {
	/**
	 * Instructions.
	 *
	 * @var array
	 */
	private $instructions = [];

	/**
	 * Details of the request.
	 *
	 * @var Request
	 */
	private $request;

	/**
	 * Details of the visitor.
	 *
	 * @var Visitor
	 */
	private $visitor;

	/**
	 * Constructor
	 */
	public function __construct() {
		/**
		 * Get the details of request and visitor.
		 */
		$this->visitor = new Visitor( $_SERVER, $_COOKIE );
		$this->request = new Request( $this->visitor->full_url );

		/**
		 * Register perpetual instructions to be run.
		 *
		 * @param array $instructions All instructions to be run.
		 */
		$this->instructions = apply_filters( 'darkmatter.advancedcache.instructions.perpetual', [] );
	}

	/**
	 * Process all relevant instructions against the body of the response.
	 *
	 * @param string $response Response before the instructions.
	 * @return string Response after all perpetual instructions have run.
	 */
	public function body( $response = '' ) {
		foreach ( $this->instructions as $instruction_row ) {
			/**
			 * Ensure the class is provided.
			 */
			if ( ! isset( $instruction_row['class'] ) || ! class_exists( $instruction_row['class'] ) ) {
				continue;
			}

			/**
			 * Instantiate the instruction.
			 */
			$instruction = new $instruction_row['class']( $this->request, $this->visitor );

			/**
			 * Ensure the instruction is 1) an instruction, and 2) perpetual.
			 */
			if ( ! $instruction instanceof AbstractInstruction && InstructionType::Perpetual !== $instruction->type ) {
				continue;
			}

			$response = $instruction->do( $response );
		}

		return $response;
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return InstructionsPerpetual
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}
