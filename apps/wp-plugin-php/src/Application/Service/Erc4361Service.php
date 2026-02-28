<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Message;
use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Nonce;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\Web3\ERC4361\Erc4361MessageBuilder;

class Erc4361Service {

	private const ERC4361_MESSAGE_VERSION = 1;

	private InvoiceRepository $invoice_repository;
	private Erc4361PropertyProvider $erc4361_property_provider;
	private Erc4361MessageBuilder $message_builder;

	public function __construct( InvoiceRepository $invoice_repository, Erc4361PropertyProvider $erc4361_property_provider, Erc4361MessageBuilder $message_builder ) {
		$this->invoice_repository        = $invoice_repository;
		$this->erc4361_property_provider = $erc4361_property_provider;
		$this->message_builder           = $message_builder;
	}

	/**
	 * ERC-4361の署名用メッセージを作成します
	 *
	 * @param InvoiceId    $invoice_id
	 * @param Erc4361Nonce $nonce
	 * @return Erc4361Message
	 */
	public function createMessage( InvoiceId $invoice_id, Erc4361Nonce $nonce ): Erc4361Message {
		$invoice = $this->invoice_repository->get( $invoice_id );
		if ( $invoice === null ) {
			throw new \RuntimeException( "[D7CC8C7C] Invoice not found: {$invoice_id}" );
		}

		// 環境に応じたドメイン等の情報を取得
		$domain    = $this->erc4361_property_provider->domain();
		$statement = $this->erc4361_property_provider->statement();
		$uri       = $this->erc4361_property_provider->uri();

		return Erc4361Message::from(
			$this->message_builder->buildMessage(
				$domain->value(),
				$invoice->customerAddress()->value(),   // 購入者のアドレスで署名を要求する
				$statement !== null ? $statement->value() : null,
				$uri->value(),
				(string) self::ERC4361_MESSAGE_VERSION,
				(string) $invoice->chainId()->value(),   // 購入時のチェーンID
				$nonce->nonce()->value(),
				$nonce->issuedAt()->toRfc3339Value()
			)
		);
	}
}
