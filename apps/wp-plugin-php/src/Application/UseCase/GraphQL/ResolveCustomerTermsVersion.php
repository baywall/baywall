<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Infrastructure\Terms\CustomerTermsProvider;

class ResolveCustomerTermsVersion {
	public function handle( array $root_value, array $args ) {
		// アクセス制御は不要

		// 購入者向け利用規約のバージョンを取得
		return ( new CustomerTermsProvider() )->currentVersion();
	}
}
