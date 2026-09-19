<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Dto\LogDto;
use Baywall\Core\Application\Service\LogQueryService;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\LogTable;

class WpLogQueryService implements LogQueryService {

	private LogTable $log_table;

	public function __construct( LogTable $log_table ) {
		$this->log_table = $log_table;
	}

	/** @inheritDoc */
	public function findRecent( int $limit ): array {
		$rows = $this->log_table->selectRecent( $limit );
		return array_map(
			function ( array $row ) {
				return new LogDto(
					(int) $row['log_id'],
					(int) $row['created_at'],
					$row['level'],
					$row['category'],
					$row['message']
				);
			},
			$rows
		);
	}
}
