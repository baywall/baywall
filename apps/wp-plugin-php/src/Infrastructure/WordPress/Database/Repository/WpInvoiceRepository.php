<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\SearchCondition\InvoiceSearchCondition;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\InvoiceTable;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\InvoiceTableRecord;

class WpInvoiceRepository implements InvoiceRepository {

	public function __construct( InvoiceTable $invoice_table ) {
		$this->invoice_table = $invoice_table;
	}

	private InvoiceTable $invoice_table;

	/** @inheritdoc */
	public function save( Invoice $invoice ): void {
		// 請求書情報を保存
		$this->invoice_table->save( $invoice );
	}

	/** @inheritdoc */
	public function get( InvoiceId $invoice_id ): ?Invoice {
		$invoice_records = $this->invoice_table->select( ( new InvoiceSearchCondition() )->setInvoiceId( $invoice_id ) );
		assert( count( $invoice_records ) <= 1, '[406F62EA]' );
		return empty( $invoice_records ) ? null : new InvoiceImpl( $invoice_records[0] );
	}

	/** @inheritdoc */
	public function findBy( InvoiceSearchCondition $condition ): array {
		$invoice_records = $this->invoice_table->select( $condition );
		return array_map( fn( InvoiceTableRecord $record ) => new InvoiceImpl( $record ), $invoice_records );
	}
}

/** @internal */
class InvoiceImpl extends Invoice {
	public function __construct( InvoiceTableRecord $invoice_record ) {
		parent::__construct(
			InvoiceId::fromUlidValue( $invoice_record->idValue() ),
			PostId::from( $invoice_record->postIdValue() ),
			ChainId::from( $invoice_record->chainIdValue() ),
			Price::from(
				Amount::from( $invoice_record->sellingAmountValue() ),
				Symbol::from( $invoice_record->sellingSymbolValue() )
			),
			Address::from( $invoice_record->sellerAddressValue() ),
			Address::from( $invoice_record->paymentTokenAddressValue() ),
			Amount::from( $invoice_record->paymentAmountValue() ),
			Address::from( $invoice_record->customerAddressValue() ),
		);
	}
}
