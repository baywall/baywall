<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Repository\Erc4361NonceRepository;
use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Message;
use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Nonce;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\Web3\ERC4361\Erc4361MessageBuilder;

class Erc4361Service {

	private const ERC4361_MESSAGE_VERSION = 1;

	private Erc4361PropertyProvider $erc4361_property_provider;
	private Erc4361MessageBuilder $message_builder;
	private Erc4361NonceRepository $repository;

	public function __construct( Erc4361PropertyProvider $erc4361_property_provider, Erc4361MessageBuilder $message_builder, Erc4361NonceRepository $repository ) {
		$this->erc4361_property_provider = $erc4361_property_provider;
		$this->message_builder           = $message_builder;
		$this->repository                = $repository;
	}

	/**
	 * ERC-4361の署名用メッセージを作成します
	 */
	public function createMessage( Address $address, ChainId $chain_id, Erc4361Nonce $nonce ): Erc4361Message {
		// 環境に応じたドメイン等の情報を取得
		$domain    = $this->erc4361_property_provider->domain();
		$statement = $this->erc4361_property_provider->statement();
		$uri       = $this->erc4361_property_provider->uri();

		return Erc4361Message::from(
			$this->message_builder->buildMessage(
				$domain->value(),
				$address->value(),   // 署名を要求するアドレス
				$statement !== null ? $statement->value() : null,
				$uri->value(),
				(string) self::ERC4361_MESSAGE_VERSION,
				(string) $chain_id->value(), // 署名を要求するチェーンID
				$nonce->nonce()->value(),
				$nonce->issuedAt()->toRfc3339Value()
			)
		);
	}

	/** 古いERC-4361用のnonceデータをリポジトリから削除します */
	public function purgeExpiredNonceData(): void {
		$expirationTime = UnixTimestamp::from( UnixTimestamp::now()->value() - Config::ERC4361_NONCE_DATA_EXPIRATION );
		$this->repository->deleteExpired( $expirationTime );
	}
}
