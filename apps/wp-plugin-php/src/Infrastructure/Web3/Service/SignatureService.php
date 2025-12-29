<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\Entity\Base\Signer;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Bytes32;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Reimpl\Ethers\EthersWallet;

/** 署名関連のサービスクラス */
class SignatureService {
	/** EIP191に準拠した署名を行います */
	public function signMessage( Signer $signer, SigningMessage $message ): Signature {
		$wallet = new EthersWallet( $signer->privateKey()->value() );
		return Signature::from( $wallet->signMessage( $message->value() ) );
	}

	/** 署名からアドレスを復元します */
	public function recoverAddress( SigningMessage $message, Signature $signature ): Address {
		$address_value = Ethers::verifyMessage( $message->value(), $signature->hex()->value() );
		if ( $address_value === null ) {
			throw new \RuntimeException( '[8FD0A3B4] Failed to recover address from signature.' );
		}
		return Address::from( $address_value );
	}

	/** EIP191に準拠したメッセージハッシュを取得します */
	public function hashMessage( SigningMessage $message ): Bytes32 {
		return Bytes32::fromHex( Hex::from( Ethers::hashMessage( $message->value() ) ) );
	}
}
