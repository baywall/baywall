<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\Constant\ChainIdValue;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use wpdb;


class TokenTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableName $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->token() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', TokenTableSeed_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class TokenTableSeed_0_0_1 extends MigrationBase {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}
	private Environment $environment;

	public function up(): void {

		$zero_address = Ethers::zeroAddress()->value();
		$Record       = new class( 0, '', '', 0, false ) {
			public function __construct(
				int $chain_id,
				string $address,
				string $symbol,
				int $decimals,
				bool $is_payable
			) {
				$this->chain_id   = $chain_id;
				$this->address    = $address;
				$this->symbol     = $symbol;
				$this->decimals   = $decimals;
				$this->is_payable = $is_payable;
			}
			public int $chain_id;
			public string $address;
			public string $symbol;
			public int $decimals;
			public bool $is_payable;
		};

		$records = array();

		// メインネットのネイティブトークンを登録(Ethereum Mainnetのみ支払可能として指定)
		$records[] = new $Record( ChainIdValue::ETH_MAINNET, $zero_address, 'ETH', 18, true );

		// テストネットのネイティブトークンを登録(Sepoliaのみ支払可能として指定)
		$records[] = new $Record( ChainIdValue::SEPOLIA, $zero_address, 'ETH', 18, true );
		$records[] = new $Record( ChainIdValue::SONEIUM_MINATO, $zero_address, 'ETH', 18, false );

		// 開発モード時はプライベートネットのネイティブトークンを登録
		if ( $this->environment->isDevelopment() ) {
			$records[] = new $Record( ChainIdValue::PRIVATENET_L1, $zero_address, 'ETH', 18, true );
			$records[] = new $Record( ChainIdValue::PRIVATENET_L2, $zero_address, 'MATIC', 18, true );
		}

		foreach ( $records as $record ) {
			$this->insert(
				$this->tableName(),
				array(
					'chain_id'   => $record->chain_id,
					'address'    => $record->address,
					'symbol'     => $record->symbol,
					'decimals'   => $record->decimals,
					'is_payable' => $record->is_payable,
				)
			);
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
