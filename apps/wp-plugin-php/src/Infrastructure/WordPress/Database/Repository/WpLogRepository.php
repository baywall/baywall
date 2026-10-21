<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Application\Repository\LogRepository;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\LogTable;

class WpLogRepository implements LogRepository {

	public function __construct( private readonly LogTable $log_table ) {}

	/** @inheritDoc */
	public function deleteOldRecords( int $expiration_seconds ): void {
		$this->log_table->deleteOldRecords( $expiration_seconds );
	}
}
