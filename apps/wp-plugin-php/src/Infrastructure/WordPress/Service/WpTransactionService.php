<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\TransactionService;
use wpdb;

class WpTransactionService extends TransactionService {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;

	public function beginTransaction(): void {
		$this->wpdb->query( 'START TRANSACTION' );
	}

	public function commit(): void {
		$this->wpdb->query( 'COMMIT' );
	}

	public function rollback(): void {
		$this->wpdb->query( 'ROLLBACK' );
	}
}
