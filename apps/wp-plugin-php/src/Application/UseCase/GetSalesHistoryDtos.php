<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\SalesHistoryDto;
use Cornix\Serendipity\Core\Application\Service\SalesHistoryService;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

class GetSalesHistoryDtos {

	private SalesHistoryService $salesHistoryService;
	public function __construct(
		SalesHistoryService $salesHistoryService
	) {
		$this->salesHistoryService = $salesHistoryService;
	}

	/**
	 *
	 * @return SalesHistoryDto[]
	 */
	public function handle( ?string $filter_invoice_id_value ): array {
		$filter_invoice_id = InvoiceId::fromNullable( $filter_invoice_id_value );

		return $this->salesHistoryService->find( $filter_invoice_id );
	}
}
