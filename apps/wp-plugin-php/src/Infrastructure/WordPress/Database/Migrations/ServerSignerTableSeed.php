<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use wpdb;


class ServerSignerTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->serverSigner() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', ServerSignerTableSeed_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class ServerSignerTableSeed_0_0_1 extends MigrationBase {

	public function up(): void {
		// TODO: すでにデータが存在する場合はエラーとする

		$private_key      = Ethers::generatePrivateKey();
		$address          = Ethers::privateKeyToAddress( $private_key );
		$base64_key_value = base64_encode( $private_key->value() );

		$this->insert(
			$this->tableName(),
			array(
				'address'    => $address->value(),
				'base64_key' => $base64_key_value,
			)
		);
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
