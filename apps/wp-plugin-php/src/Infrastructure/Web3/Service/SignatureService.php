<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Signature;
use Baywall\Core\Domain\ValueObject\SigningMessage;
use Baywall\Core\Lib\Ether\Ethers;

/** 署名関連のサービスクラス */
class SignatureService {
	/** 署名からアドレスを復元します */
	public function recoverAddress( SigningMessage $message, Signature $signature ): Address {
		$address_value = Ethers::verifyMessage( $message->value(), $signature->hex()->value() );
		if ( $address_value === null ) {
			throw new \RuntimeException( '[8FD0A3B4] Failed to recover address from signature.' );
		}
		return Address::from( $address_value );
	}
}
