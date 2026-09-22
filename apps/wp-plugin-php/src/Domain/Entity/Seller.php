<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Entity;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Signature;
use Baywall\Core\Domain\ValueObject\SigningMessage;

class Seller {


	public function __construct(
		private readonly Address $address,
		private readonly SigningMessage $signing_message,
		private readonly Signature $signature
	) {}

	/** 販売者ウォレットアドレス */
	public function address(): Address {
		return $this->address;
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
