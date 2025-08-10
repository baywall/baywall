<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Terms;

use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;

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
	 * ※※※ 過去のバージョンが引数として渡される可能性があるため、過去バージョンでのメッセージが壊れないように注意してください。
	 */
	public function getSigningMessage( TermsVersion $version ): SigningMessage {
		return SigningMessage::from( 'I agree to the seller\'s terms of service v' . $version->value() );
	}
}
