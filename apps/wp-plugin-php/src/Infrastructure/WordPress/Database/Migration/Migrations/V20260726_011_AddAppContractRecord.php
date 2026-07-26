<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20260726_011_AddAppContractRecord extends MigrationBase {

	private TransactionService $transaction_service;
	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( TransactionService $transaction_service, MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->transaction_service = $transaction_service;
		$this->wpdb                = $wpdb;
		$this->table_name          = $table_name_provider->appContract();
	}

	public function version(): string {
		return '0.0.4';
	}

	public function up(): void {
		$this->transaction_service->transactional(
			function () {
				// Sepolia
				$this->insert( 11155111, '0x0000000000000000000000000000000000000000' );
				// Base Sepolia
				$this->insert( 84532, '0x0000000000000000000000000000000000000000' );
				// Polygon Amoy
				$this->insert( 80002, '0x0000000000000000000000000000000000000000' );
			}
		);
	}

	public function down(): void {
		$this->wpdb->query( "DELETE FROM `{$this->table_name}` WHERE chain_id IN (11155111, 84532, 80002);" );
	}

	private function insert( int $chain_id, string $address ): void {
		ChainId::from( $chain_id ); // verify
		Address::from( $address ); // verify
		$this->wpdb->insert(
			$this->table_name,
			array(
				'chain_id' => $chain_id,
				'address'  => $address,
			)
		);
	}
}
