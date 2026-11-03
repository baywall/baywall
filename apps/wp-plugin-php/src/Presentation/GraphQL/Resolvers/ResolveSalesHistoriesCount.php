<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetSalesHistoriesCount;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;

class ResolveSalesHistoriesCount implements Resolver {
	public function __construct(
		private readonly GetSalesHistoriesCount $get_sales_histories_count
	) {}

	public function handle( array $root_value, array $args ): int {
		return $this->get_sales_histories_count->handle(
			InvoiceId::fromUlidValueNullable( $args['filter']['invoiceId'] ?? null ),
			ChainId::fromNullable( $args['filter']['chainId'] ?? null ),
			TransactionHash::fromNullable( $args['filter']['txHash'] ?? null ),
			isset( $args['filter']['dateFrom'] ) ? (int) $args['filter']['dateFrom'] : null,
			isset( $args['filter']['dateTo'] ) ? (int) $args['filter']['dateTo'] : null
		);
	}
}
