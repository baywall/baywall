<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\Entity\Invoice;
use Baywall\Core\Domain\Repository\ServerSignerRepository;
use Baywall\Core\Domain\ValueObject\Bytes;
use Baywall\Core\Domain\ValueObject\Hex;
use Baywall\Core\Domain\ValueObject\Signature;
use Baywall\Core\Lib\Ether\Ethers;
use Baywall\Core\Lib\Ether\EthersSigningKey;

/** 署名用ウォレットで請求書の署名メッセージを作成・署名するサービスクラス */
class ServerSignerService {
	public function __construct(
		private readonly ServerSignerRepository $server_signer_repository
	) {}

	/** 署名用のメッセージを作成します */
	public function createSigningMessageBytes( Invoice $invoice ): Bytes {
		$types  = array(
			'uint64',
			'address',
			'address',
			'address',
			'uint64',
			'uint128',
			'address',
			'uint256',
		);
		$values = array(
			$invoice->chainId()->value(),
			$this->server_signer_repository->getAddress()->value(),
			$invoice->sellerAddress()->value(),
			$invoice->buyerAddress()->value(),
			$invoice->postId()->value(),
			$invoice->id()->hex()->value(),
			$invoice->paymentTokenAddress()->value(),
			$invoice->paymentAmount()->hex()->value(),
		);

		return Bytes::fromHex( Hex::from( Ethers::solidityPacked( $types, $values ) ) );
	}

	/** 署名用メッセージに署名します */
	public function signMessageBytes( Bytes $message_bytes ): Signature {
		// コントラクト側が keccak256(メッセージ) からアドレスを復元して検証するため、EIP-191 プレフィックス付きの署名にはしない
		$signing_key = new EthersSigningKey( $this->server_signer_repository->get()->privateKey()->value() );
		return Signature::from( $signing_key->sign( Ethers::keccak256( $message_bytes->hex()->value() ) ) );
	}
}
