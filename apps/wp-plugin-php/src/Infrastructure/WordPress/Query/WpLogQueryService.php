<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Query;

use Baywall\Core\Application\Dto\LogDto;
use Baywall\Core\Application\Port\LogQueryService;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\LogTable;
use stdClass;

class WpLogQueryService implements LogQueryService {
	public function __construct( private readonly LogTable $log_table ) {}

	/** @inheritDoc */
	public function findRecent( int $limit ): array {
		$rows = $this->log_table->selectRecent( $limit );
		return array_map(
			function ( stdClass $row ) {
				return new LogDto(
					(int) $row->log_id,
					(int) $row->created_at,
					(string) $row->level,
					(string) $row->message
				);
			},
			$rows
		);
	}
}
