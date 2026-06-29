<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Dto\LogDto;
use Cornix\Serendipity\Core\Application\Service\LogQueryService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\LogTable;

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
					(int) $row['id'],
					(float) ( strtotime( $row['created_at'] ) ?: 0.0 ),
					$row['level'],
					$row['category'],
					$row['message']
				);
			},
			$rows
		);
	}
}
