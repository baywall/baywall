<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\Entity\Base\Signer;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;

/** 署名関連のサービスクラス */
class SignatureService {
	/** 署名を行います */
	public function signMessage( Signer $signer, SigningMessage $message ): Signature {
		return Ethers::signMessage( $signer->privateKey(), $message );
	}

	/** 署名からアドレスを復元します */
	public function recoverAddress( SigningMessage $message, Signature $signature ): Address {
		return Ethers::verifyMessage( $message, $signature );
	}
}
