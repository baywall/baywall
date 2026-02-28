<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Dto\PriceDto;
use Cornix\Serendipity\Core\Application\Dto\SalesHistoryDto;
use Cornix\Serendipity\Core\Application\Dto\SalesHistoryInvoiceDto;
use Cornix\Serendipity\Core\Application\Service\SalesHistoryService;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\Decimals;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Record\SalesHistoryViewRecord;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\SalesHistoryView;

class WpSalesHistoryService implements SalesHistoryService {

	private SalesHistoryView $sales_history_view;

	public function __construct( SalesHistoryView $sales_history_view ) {
		$this->sales_history_view = $sales_history_view;
	}

	/** @inheritDoc */
	public function find( ?InvoiceId $filter_invoice_id ): array {
		return array_map(
			fn ( SalesHistoryViewRecord $record ) => $this->recordToDto( $record ),
			$this->sales_history_view->select( $filter_invoice_id )
		);
	}

	private function recordToDto( SalesHistoryViewRecord $record ): SalesHistoryDto {

		$invoice_dto = new SalesHistoryInvoiceDto(
			$record->invoice_id,
			$record->created_at_unix,
			$record->post_id,
			new PriceDto( $record->selling_amount, $record->selling_symbol ),
			$record->chain_id,
			$record->chain_name
		);

		// トークン転送イベントは数量から価格への変換が必要
		$to_price_dto_callback = fn ( ?string $amount ) => $amount === null ? null : new PriceDto(
			(string) Amount::fromBaseUnitAndDecimals( $amount, Decimals::from( $record->payment_token_decimals ) ),
			$record->payment_token_symbol
		);

		return new SalesHistoryDto(
			$invoice_dto,
			$record->post_title,
			$record->transaction_hash,
			$record->customer_address,
			$to_price_dto_callback( $record->payment_amount ),
			$record->contract_address,
			$to_price_dto_callback( $record->contract_received_amount ),
			$record->seller_address,
			$to_price_dto_callback( $record->seller_received_amount ),
			$record->affiliate_address,
			$to_price_dto_callback( $record->affiliate_received_amount )
		);
	}
}
