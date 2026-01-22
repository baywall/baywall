<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Terms;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;

/** @deprecated */
class SellerTermsProvider {

	private const CURRENT_SELLER_TERMS_VERSION = 1;

	/**
	 * 現在の販売者向け利用規約のバージョンを取得します。
	 */
	public function currentVersion(): TermsVersion {
		return TermsVersion::from( self::CURRENT_SELLER_TERMS_VERSION );
	}

	/**
	 * 販売者向け利用規約に署名する時のメッセージを取得します。
	 */
	// TODO: 引数を削除
	public function getSigningMessage( ?TermsVersion $version = null ): SigningMessage {
		return SigningMessage::from( Config::SELLER_TERMS_SIGNING_MESSAGE );
	}
}
