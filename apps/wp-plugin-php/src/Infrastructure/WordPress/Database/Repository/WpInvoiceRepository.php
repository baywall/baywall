<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Domain\Entity\Invoice;
use Baywall\Core\Domain\Repository\InvoiceRepository;
use Baywall\Core\Domain\Repository\SearchCondition\InvoiceSearchCondition;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Decimals;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\InvoiceTable;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Domain\ValueObject\Price;
use Baywall\Core\Domain\ValueObject\Symbol;
use Baywall\Core\Infrastructure\WordPress\Database\Record\InvoiceTableRecord;

class WpInvoiceRepository implements InvoiceRepository {
	public function __construct( private readonly InvoiceTable $invoice_table ) {}

	/** @inheritdoc */
	public function save( Invoice $invoice ): void {
		// 請求書情報を保存
		$this->invoice_table->save( $invoice );
	}

	/** @inheritdoc */
	public function get( InvoiceId $invoice_id ): ?Invoice {
		$invoices = $this->findBy( ( new InvoiceSearchCondition() )->setInvoiceId( $invoice_id ) );
		assert( count( $invoices ) <= 1, '[406F62EA]' );

		return $invoices[0] ?? null;
	}

	/** @inheritdoc */
	public function findBy( InvoiceSearchCondition $condition ): array {
		$invoice_records = $this->invoice_table->select( $condition );

		return array_map(
			fn( InvoiceTableRecord $record ) => new Invoice(
				InvoiceId::fromUlidValue( $record->invoiceIdValue() ),
				PostId::from( $record->postIdValue() ),
				ChainId::from( $record->chainIdValue() ),
				Price::from(
					Amount::from( $record->sellingAmountValue() ),
					Symbol::from( $record->sellingSymbolValue() )
				),
				Address::from( $record->sellerAddressValue() ),
				Address::from( $record->paymentTokenAddressValue() ),
				Symbol::from( $record->paymentTokenSymbolValue() ),
				Decimals::from( $record->paymentTokenDecimalsValue() ),
				Amount::from( $record->paymentAmountValue() ),
				Address::from( $record->buyerAddressValue() )
			),
			$invoice_records
		);
	}
}
