<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Dto\TokenDto;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetPostDto;
use Cornix\Serendipity\Core\Application\UseCase\GetPayableTokens;

class PostResolver extends ResolverBase {

	public function __construct(
		GetPostDto $get_post_dto,
		GetPayableTokens $get_payable_tokens,
		UserAccessChecker $user_access_checker
	) {
		$this->get_post_dto        = $get_post_dto;
		$this->get_payable_tokens  = $get_payable_tokens;
		$this->user_access_checker = $user_access_checker;
	}

	private GetPostDto $get_post_dto;
	private GetPayableTokens $get_payable_tokens;
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$post_dto = $this->get_post_dto->handle( $args['postID'] );

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_dto->id );

		$payable_tokens_callback = function () use ( $root_value, $post_dto ) {
			$payable_token_dtos = $this->get_payable_tokens->handle( $post_dto->id );

			return array_map(
				fn( TokenDto $token_dto ) => $root_value['token'](
					$root_value,
					array(
						'chainID' => $token_dto->chain_id,
						'address' => $token_dto->address,
					)
				),
				$payable_token_dtos
			);
		};

		return array(
			'id'             => $post_dto->id,
			'title'          => $post_dto->title,
			'sellingPrice'   => fn() => $root_value['sellingPrice']( $root_value, array( 'postID' => $post_dto->id ) ),
			'sellingContent' => fn() => $root_value['sellingContent']( $root_value, array( 'postID' => $post_dto->id ) ),
			'payableTokens'  => $payable_tokens_callback,
		);
	}
}
