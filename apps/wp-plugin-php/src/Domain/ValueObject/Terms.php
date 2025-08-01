<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

class Terms {
	/**
	 * @param TermsVersion   $version 利用規約のバージョン
	 * @param SigningMessage $message 利用規約署名用メッセージ
	 */
	private function __construct( TermsVersion $version, SigningMessage $message ) {
		$this->version = $version;
		$this->message = $message;
	}
	public static function from( TermsVersion $version, SigningMessage $message ): self {
		return new self( $version, $message );
	}

	private TermsVersion $version;
	private SigningMessage $message;

	public function version(): TermsVersion {
		return $this->version;
	}

	public function message(): SigningMessage {
		return $this->message;
	}
}
