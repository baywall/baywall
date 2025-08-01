<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\InvoiceTable;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\InvoiceTableRecord;

class InvoiceRepositoryImpl implements InvoiceRepository {

	public function __construct( InvoiceTable $invoice_table ) {
		$this->invoice_table = $invoice_table;
	}

	private InvoiceTable $invoice_table;

	/** @inheritdoc */
	public function save( Invoice $invoice ): void {
		// 請求書情報を保存
		$this->invoice_table->insert( $invoice );
	}

	/** @inheritdoc */
	public function get( InvoiceID $invoice_ID ): ?Invoice {
		$invoice_record = $this->invoice_table->select( $invoice_ID );
		return is_null( $invoice_record ) ? null : new InvoiceImpl( $invoice_record );
	}
}

/** @internal */
class InvoiceImpl extends Invoice {
	public function __construct( InvoiceTableRecord $invoice_record ) {
		parent::__construct(
			InvoiceID::from( $invoice_record->idValue() ),
			$invoice_record->postIdValue(),
			ChainID::from( $invoice_record->chainIdValue() ),
			Price::from(
				Amount::from( $invoice_record->sellingAmountValue() ),
				Symbol::from( $invoice_record->sellingSymbolValue() )
			),
			Address::from( $invoice_record->sellerAddressValue() ),
			Address::from( $invoice_record->paymentTokenAddressValue() ),
			Amount::from( $invoice_record->paymentAmountValue() ),
			Address::from( $invoice_record->consumerAddressValue() ),
			InvoiceNonce::from( $invoice_record->nonceValue() ),
		);
	}
}
