<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Registry\ChainIdRegistry;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use wpdb;


// Oracleのアドレスは以下のURLで確認可能
// https://docs.chain.link/data-feeds/price-feeds/addresses

// ■ Fiatのアドレス確認方法
// 以下のコマンドでFiatのOracleアドレスを確認可能
// curl -s https://reference-data-directory.vercel.app/feeds-mainnet.json | jq '.[] | select(.docs.assetClass == "Fiat")'

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
abstract class OracleTableSeedBase extends MigrationBase {
	protected function add( ChainID $chain_id, string $address_value, string $base_symbol_value, string $quote_symbol_value ): void {
		// Address, Symbolオブジェクトを経由することでフォーマットチェックを実施
		$address      = Address::from( $address_value );
		$base_symbol  = Symbol::from( $base_symbol_value );
		$quote_symbol = Symbol::from( $quote_symbol_value );
		$this->insert(
			$this->tableName(),
			array(
				'chain_id'     => $chain_id->value(),
				'address'      => $address->value(),
				'base_symbol'  => $base_symbol->value(),
				'quote_symbol' => $quote_symbol->value(),
			)
		);
	}
}

/** @internal */
class OracleTableSeed_0_0_1 extends OracleTableSeedBase {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}
	private Environment $environment;

	public function up(): void {
		// ■ Fiat
		if ( 'ja' === substr( get_locale(), 0, 2 ) || $this->environment->isDevelopment() ) {
			// サイトの言語が日本語の場合、もしくは開発モード時は、`JPY / USD`を登録
			$this->add( ChainIdRegistry::ethMainnet(), '0xBcE206caE7f0ec07b545EddE332A47C2F75bbeb3', 'JPY', 'USD' );
		}

		// ■ Crypto
		$this->add( ChainIdRegistry::ethMainnet(), '0x5f4eC3Df9cbd43714FE2740f5E3616155c5b8419', 'ETH', 'USD' );

		// テスト中はプライベートネットのOracleを登録
		if ( $this->environment->isTesting() ) {
			// プライベートネットのOracleを登録
			$this->add( ChainIdRegistry::privatenetL1(), '0x3F3B6a555F3a7DeD78241C787e0cDD8E431A64A8', 'ETH', 'USD' );
			$this->add( ChainIdRegistry::privatenetL1(), '0xc886d2C1BEC5819b4B8F84f35A9885519869A8EE', 'JPY', 'USD' );
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
