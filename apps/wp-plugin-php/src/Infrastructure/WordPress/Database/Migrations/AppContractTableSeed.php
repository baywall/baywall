<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Registry\ChainIdRegistry;
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

abstract class AppContractTableSeedBase extends MigrationBase {
	protected function add( ChainID $chain_id, string $address_value ): void {
		$address = Address::from( $address_value );
		$this->insert(
			$this->tableName(),
			array(
				'chain_id'                        => $chain_id->value(),
				'address'                         => $address->value(),
				'crawled_block_number'            => null,
				'crawled_block_number_updated_at' => null,
			)
		);
	}
}

/** @internal */
class AppContractTableSeed_0_0_1 extends AppContractTableSeedBase {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}
	private Environment $environment;

	public function up(): void {

		// TODO: メインネットのコントラクトアドレスを登録

		if ( $this->environment->isDevelopment() ) {
			$this->add( ChainIdRegistry::privatenetL1(), '0x5FbDB2315678afecb367f032d93F642f64180aa3' );
			$this->add( ChainIdRegistry::privatenetL2(), '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512' );

			// テストネットで開発用のコントラクトアドレスを使用
			$this->add( ChainIdRegistry::sepolia(), '0x6e98081f56608E3a9414823239f65c0e6399561d' );
			$this->add( ChainIdRegistry::soneiumMinato(), '0x6a9214D8264C00d884225542d3af47cf5De2049f' );
		} else {
			// TODO: テストネットのコントラクトアドレスを登録
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
