<?php
/**
 * Scaffold abstract class for defining an Instruction.
 *
 * @package DarkMatter\AdvancedCache
 */

namespace DarkMatter\AdvancedCache\Instructions;

use DarkMatter\AdvancedCache\Data\Request;
use DarkMatter\AdvancedCache\Data\ResponseEntry;
use DarkMatter\AdvancedCache\Processor\Visitor;
use DarkMatter\Enums\InstructionType;

/**
 * Class AbstractInstruction
 */
abstract class AbstractInstruction {
	/**
	 * Content to be added by the Instruction.
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * The tag which is used for processing the instruction.
	 *
	 * @var string
	 */
	protected $tag = '';

	/**
	 * Instruction type. Should use `InstructionType` enum.
	 *
	 * @var int
	 */
	public $type = InstructionType::Ephemeral;

	/**
	 * Constructor.
	 *
	 * @param Request $request Details on the request.
	 * @param Visitor $visitor Details on the visitor.
	 */
	abstract public function __construct( $request, $visitor );

	/**
	 * After the instruction content after the Instruction comment.
	 *
	 * @param string $body
	 * @return string
	 */
	protected function appendAfter( $body ) {
		$tag = \preg_replace('/[^a-zA-Z]+/', '', $this->tag );
		return str_replace( "<!--instruction:{$tag}-->", $tag . $this->content, $body );
	}

	/**
	 * Perform the instruction changes to the HTML body.
	 *
	 * @param string $body Response body prior to instruction being run.
	 * @return string Response body after instruction is run.
	 */
	abstract public function do( $body );

	/**
	 * Replace the tag with the content.
	 *
	 * @param string $body
	 * @return string
	 */
	protected function replace( $body ) {
		$tag = \preg_replace('/[^a-zA-Z]+/', '', $this->tag );
		return str_replace( "<!--instruction:{$tag}-->", $this->content, $body );
	}
}
