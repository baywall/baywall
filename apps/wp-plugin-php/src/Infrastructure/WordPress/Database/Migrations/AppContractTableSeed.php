<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\Constant\ChainIdValue;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use wpdb;


class AppContractTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableName $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->appContract() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', AppContractTableSeed_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class AppContractTableSeed_0_0_1 extends MigrationBase {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}
	private Environment $environment;

	public function up(): void {
		$table_name = $this->tableName();
		foreach ( $this->data() as $chain_id => $address ) {
			$this->insert(
				$table_name,
				array(
					'chain_id'                        => $chain_id,
					'address'                         => $address,
					'crawled_block_number'            => null,
					'crawled_block_number_updated_at' => null,
				)
			);
		}
	}


	/** @return array<int,string> */
	private function data(): array {
		/** @var array<int,string> */
		$data = array(
			// TODO: ここに本番環境用のコントラクトアドレスを定義
		);

		// 開発モード時は開発用のコントラクトアドレスを使用(テストネットのアドレスは上書き)
		if ( $this->environment->isDevelopment() ) {
			$data[ ChainIdValue::PRIVATENET_L1 ]  = '0x5FbDB2315678afecb367f032d93F642f64180aa3';
			$data[ ChainIdValue::PRIVATENET_L2 ]  = '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512';
			$data[ ChainIdValue::SEPOLIA ]        = '0x6e98081f56608E3a9414823239f65c0e6399561d';
			$data[ ChainIdValue::SONEIUM_MINATO ] = '0x6a9214D8264C00d884225542d3af47cf5De2049f';
		}

		return $data;
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
