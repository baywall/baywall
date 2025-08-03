<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\Constant\ChainIdValue;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use wpdb;


class OracleTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableName $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->oracle() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', OracleTableSeed_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class OracleTableSeed_0_0_1 extends MigrationBase {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}
	private Environment $environment;

	public function up(): void {

		$Record = new class( 0, '', '', '' ) {
			public function __construct(
				int $chain_id,
				string $address,
				string $base_symbol,
				string $quote_symbol
			) {
				$this->chain_id     = $chain_id;
				$this->address      = $address;
				$this->base_symbol  = $base_symbol;
				$this->quote_symbol = $quote_symbol;
			}
			public int $chain_id;
			public string $address;
			public string $base_symbol;
			public string $quote_symbol;
		};

		$records = array();

		// Oracleのアドレスは以下のURLで確認可能
		// https://docs.chain.link/data-feeds/price-feeds/addresses

		// ■ Fiat
		// 以下のコマンドでFiatのOracleアドレスを確認可能
		// curl -s https://reference-data-directory.vercel.app/feeds-mainnet.json | jq '.[] | select(.docs.assetClass == "Fiat")'
		if ( 'ja' === substr( get_locale(), 0, 2 ) || $this->environment->isDevelopment() ) {
			// サイトの言語が日本語の場合、もしくは開発モード時は、`JPY / USD`を登録
			$records[] = new $Record( ChainIdValue::ETH_MAINNET, '0xBcE206caE7f0ec07b545EddE332A47C2F75bbeb3', 'JPY', 'USD' );
		}
		// ■ Crypto
		$records[] = new $Record( ChainIdValue::ETH_MAINNET, '0x5f4eC3Df9cbd43714FE2740f5E3616155c5b8419', 'ETH', 'USD' );

		// テスト中はプライベートネットのOracleを登録
		if ( $this->environment->isTesting() ) {
			// プライベートネットのOracleを登録
			$records[] = new $Record( ChainIdValue::PRIVATENET_L1, '0x3F3B6a555F3a7DeD78241C787e0cDD8E431A64A8', 'ETH', 'USD' );
			$records[] = new $Record( ChainIdValue::PRIVATENET_L1, '0xc886d2C1BEC5819b4B8F84f35A9885519869A8EE', 'JPY', 'USD' );
		}

		foreach ( $records as $record ) {
			$this->insert(
				$this->tableName(),
				array(
					'chain_id'     => $record->chain_id,
					'address'      => $record->address,
					'base_symbol'  => $record->base_symbol,
					'quote_symbol' => $record->quote_symbol,
				)
			);
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
