<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\SaveToken;

/**
 * トークンの情報をサーバーに登録します。
 */
class SaveTokenResolver extends ResolverBase {

	public function __construct(
		SaveToken $save_token,
		UserAccessChecker $user_access_checker
	) {
		$this->save_token          = $save_token;
		$this->user_access_checker = $user_access_checker;
	}

	private SaveToken $save_token;
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 */
	public function resolve( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var int */
		$chain_id_value = $args['chainId'];
		/** @var string */
		$address_value = $args['address'];
		/** @var bool */
		$is_payable = $args['isPayable'];

		// トークン情報を保存
		$this->save_token->handle( $chain_id_value, $address_value, $is_payable );

		return true;
	}
}
