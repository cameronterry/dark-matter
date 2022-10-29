<?php
/**
 * Processor for running instructions on responses.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Processor;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Instructions\AbstractInstruction;
use DarkMatter\Enums\InstructionType;

/**
 * Class Instructions
 */
class Instructions {
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
		foreach ( $this->instructions as $instruction ) {
			$instruction_response = self::run(
				$response,
				$instruction,
				InstructionType::Ephemeral,
				$this->request,
				$this->visitor
			);

			if ( ! empty( $instruction_response ) ) {
				$response = $instruction_response;
			}
		}

		return $response;
	}

	/**
	 * Instruction runner.
	 *
	 * @param string  $body        Response to be processed.
	 * @param string  $instruction Namespaced class to be called, inherited from AbstractInstruction.
	 * @param int     $type        InstructionType.
	 * @param Request $request     Details of the request.
	 * @param Visitor $visitor     Details of the visitor.
	 * @return string
	 */
	public static function run( $body, $instruction, $type, $request, $visitor ) {
		/**
		 * Ensure the class is provided.
		 */
		if ( ! isset( $instruction ) || ! class_exists( $instruction ) ) {
			return '';
		}

		/**
		 * Instantiate the instruction.
		 */
		$obj = new $instruction( $request, $visitor );

		/**
		 * Ensure the instruction is 1) an instruction, and 2) perpetual.
		 */
		if ( ! $obj instanceof AbstractInstruction && $type !== $obj->type ) {
			return '';
		}

		return $obj->do( $body );
	}

	/**
	 * Return the Singleton Instance of the class.
	 *
	 * @return Instructions
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}
