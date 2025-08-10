<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Infrastructure\Terms\SellerTermsProvider;

class CurrentSellerTermsResolver extends ResolverBase {

	public function __construct(
		UserAccessChecker $user_access_checker,
		SellerTermsProvider $seller_terms_provider
	) {
		$this->user_access_checker   = $user_access_checker;
		$this->seller_terms_provider = $seller_terms_provider;
	}

	private UserAccessChecker $user_access_checker;
	private SellerTermsProvider $seller_terms_provider;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$seller_terms_current_version = $this->seller_terms_provider->currentVersion();
		$signing_message              = $this->seller_terms_provider->getSigningMessage( $seller_terms_current_version ); // 利用規約の署名メッセージを取得

		// 最新の販売者向け利用規約の情報を取得
		return array(
			'version' => $seller_terms_current_version->value(),
			'message' => $signing_message->value(), // TODO: プロパティ名を`signingMessage`に変更
		);
	}
}
