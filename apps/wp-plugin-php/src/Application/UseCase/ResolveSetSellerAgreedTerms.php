<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\SaveSeller;

class ResolveSetSellerAgreedTerms {

	private SaveSeller $save_seller;
	private UserAccessChecker $user_access_checker;

	public function __construct(
		SaveSeller $save_seller,
		UserAccessChecker $user_access_checker
	) {
		$this->save_seller         = $save_seller;
		$this->user_access_checker = $user_access_checker;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var int */
		$version_value = $args['version'];
		/** @var string */
		$signature_value = $args['signature'];

		// 販売者情報を保存
		$this->save_seller->handle( $version_value, $signature_value );

		return true;
	}
}
