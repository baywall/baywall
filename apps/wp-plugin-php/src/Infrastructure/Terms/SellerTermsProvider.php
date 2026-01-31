<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Terms;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;

class SellerTermsProvider {
	/**
	 * 販売者向け利用規約に署名する時のメッセージを取得します。
	 */
	public function getSigningMessage(): SigningMessage {
		return SigningMessage::from( Config::SELLER_TERMS_SIGNING_MESSAGE );
	}
}
