<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;

class Seller {

	private Address $address;
	private TermsVersion $agreed_terms_version;
	private SigningMessage $signing_message;
	private Signature $signature;

	public function __construct(
		Address $address,
		TermsVersion $agreed_terms_version,
		SigningMessage $signing_message,
		Signature $signature
	) {
		$this->address              = $address;
		$this->agreed_terms_version = $agreed_terms_version;
		$this->signing_message      = $signing_message;
		$this->signature            = $signature;
	}

	/** 販売者ウォレットアドレス */
	public function address(): Address {
		return $this->address;
	}

	/** 同意した利用規約バージョンを取得します */
	public function agreedTermsVersion(): TermsVersion {
		return $this->agreed_terms_version;
	}

	/** 利用規約同意時にウォレットに表示されていたメッセージを取得します */
	public function signingMessage(): SigningMessage {
		return $this->signing_message;
	}

	/** ウォレットで署名を行った際の署名を取得します */
	public function signature(): Signature {
		return $this->signature;
	}
}
