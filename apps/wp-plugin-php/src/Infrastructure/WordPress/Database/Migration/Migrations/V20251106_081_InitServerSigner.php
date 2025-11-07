<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_081_InitServerSigner extends MigrationBase {

	private TransactionService $transaction_service;
	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( TransactionService $transaction_service, MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->transaction_service = $transaction_service;
		$this->wpdb                = $wpdb;
		$this->table_name          = $table_name_provider->serverSigner();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		$this->transaction_service->transactional(
			function () {
				// TODO: すでにデータが存在する場合はエラーとする

				$private_key      = Ethers::generatePrivateKey();
				$address          = Ethers::privateKeyToAddress( $private_key );
				$base64_key_value = base64_encode( $private_key->value() );

				$this->wpdb->insert(
					$this->table_name,
					array(
						'address'    => $address->value(),
						'base64_key' => $base64_key_value,
					)
				);
			}
		);
	}

	public function down(): void {
		$this->wpdb->dbh->query( "TRUNCATE TABLE `{$this->table_name}`;" );
	}
}
