<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\GraphQL\Rule;

use GraphQL\Error\Error;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Validator\Rules\ValidationRule;
use GraphQL\Validator\ValidationContext;

/**
 * GraphQLのmutation呼び出し時のフィールド数を制限するルール
 */
class MutationFieldLimitRule extends ValidationRule {

	private int $max_fields;

	public function __construct( int $max_fields ) {
		$this->max_fields = $max_fields;
	}

	public function getVisitor( ValidationContext $context ): array {
		return array(
			'OperationDefinition' => function ( OperationDefinitionNode $node ) use ( $context ) {
				if ( $node->operation !== 'mutation' ) {
					return;
				}

				$top_level_fields = $node->selectionSet
					? count( $node->selectionSet->selections )
					: 0;

				if ( $top_level_fields > $this->max_fields ) {
					$context->reportError(
						new Error( '[BBF09EBD] Only one top-level mutation field is allowed per request.' )
					);
				}
			},
		);
	}
}
