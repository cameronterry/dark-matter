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
	private $content = '';

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
	protected $type = InstructionType::Ephemeral;

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
	 * @param ResponseEntry $response_entry
	 * @return void
	 */
	public function appendAfter( $response_entry ) {
		$this->complete( $response_entry );
	}

	/**
	 * Complete the instruction.
	 *
	 * @param ResponseEntry $response_entry
	 * @return void
	 */
	private function complete( $response_entry ) {
		/**
		 * Ephemeral instructions are to update the Response entry.
		 */
		if ( InstructionType::Ephemeral === $this->type ) {
			$response_entry->save();
		}
	}

	/**
	 * Replace the tag with the content.
	 *
	 * @param ResponseEntry $response_entry
	 * @return void
	 */
	public function replace( $response_entry ) {
		$tag = sanitize_title_with_dashes( $this->tag );

		$response_entry->body = str_replace( "<!--instruction:{$tag}-->", $this->content, $response_entry->body );

//		$response_entry->body = preg_replace(
//			"#<!--instruction:{$tag}-->(?s:.*?)<!--\/instruction:{$tag}-->#",
//			$this->content,
//			$response_entry->body
//		);

		$this->complete( $response_entry );
	}
}
