<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Seller;
use Cornix\Serendipity\Core\Domain\Repository\SellerRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\SellerTable;

class WpSellerRepository implements SellerRepository {

	private SellerTable $seller_table;

	public function __construct( SellerTable $seller_table ) {
		$this->seller_table = $seller_table;
	}

	public function get(): ?Seller {
		$records = $this->seller_table->all();
		// 現在の実装では販売者は1つしか保存できない仕様
		if ( count( $records ) > 1 ) {
			throw new \Exception( '[801E97D5] There should be only one seller record.' );
		}

		if ( count( $records ) === 0 ) {
			return null;
		}

		$record = $records[0];
		return new Seller(
			Address::from( $record->sellerAddressValue() ),
			SigningMessage::from( $record->signingMessageValue() ),
			Signature::from( $record->signatureValue() )
		);
	}

	public function save( Seller $seller ): void {
		// 既存の販売者情報がある場合は削除
		$prev_seller = $this->get();
		if ( $prev_seller !== null ) {
			$result = $this->seller_table->delete( $prev_seller->address() );
			assert( $result === 1, "[D6A207D6] Failed to delete seller data. {$result}" );
		}

		$this->seller_table->add(
			$seller->address(),
			$seller->signingMessage(),
			$seller->signature()
		);
	}
}
