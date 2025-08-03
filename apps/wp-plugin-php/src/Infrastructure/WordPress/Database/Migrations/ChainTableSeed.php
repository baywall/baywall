<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Infrastructure\Constant\ChainIdValue;
use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\Config\InitialBlockExplorerURL;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use wpdb;


class ChainTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableName $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->chain() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', ChainTableSeed_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class ChainTableSeed_0_0_1 extends MigrationBase {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}
	private Environment $environment;

	public function up(): void {
		$Record = new class( 1, '', NetworkCategoryID::mainnet(), null, '', '' ) {
			public function __construct(
				int $chain_id,
				string $name,
				NetworkCategoryID $network_category_id,
				?string $rpc_url,
				string $confirmations,
				?string $block_explorer_url
			) {
				$this->chain_id            = $chain_id;
				$this->name                = $name;
				$this->network_category_id = $network_category_id;
				$this->rpc_url             = $rpc_url;
				$this->confirmations       = $confirmations;
				$this->block_explorer_url  = $block_explorer_url;
			}
			public int $chain_id;
			public string $name;
			public NetworkCategoryID $network_category_id;
			public ?string $rpc_url;
			public string $confirmations;
			public ?string $block_explorer_url;
		};

		$records = array(
			new $Record( ChainIdValue::ETH_MAINNET, 'Ethereum Mainnet', NetworkCategoryID::mainnet(), null, '1', InitialBlockExplorerURL::ETH_MAINNET ),
			new $Record( ChainIdValue::SEPOLIA, 'Sepolia', NetworkCategoryID::testnet(), null, '1', InitialBlockExplorerURL::SEPOLIA ),
			new $Record( ChainIdValue::SONEIUM_MINATO, 'Soneium Testnet Minato', NetworkCategoryID::testnet(), null, '1', InitialBlockExplorerURL::SONEIUM_MINATO ),
		);
		// 開発モード時はプライベートネットのチェーン情報も登録
		if ( $this->environment->isDevelopment() ) {
			$records[] = new $Record( ChainIdValue::PRIVATENET_L1, 'Privatenet1', NetworkCategoryID::privatenet(), $this->getPrivatenetRpcURL( ChainIdValue::PRIVATENET_L1 ), '1', InitialBlockExplorerURL::PRIVATENET_L1 );
			$records[] = new $Record( ChainIdValue::PRIVATENET_L2, 'Privatenet2', NetworkCategoryID::privatenet(), $this->getPrivatenetRpcURL( ChainIdValue::PRIVATENET_L2 ), '1', InitialBlockExplorerURL::PRIVATENET_L2 );
		}

		foreach ( $records as $record ) {
			$this->insert(
				$this->tableName(),
				array(
					'chain_id'            => $record->chain_id,
					'name'                => $record->name,
					'network_category_id' => $record->network_category_id->value(),
					'rpc_url'             => $record->rpc_url,
					'confirmations'       => $record->confirmations,
					'block_explorer_url'  => $record->block_explorer_url,
				)
			);
		}
	}

	/**
	 * 指定されたチェーンIDに対応するプライベートネットのRPC URLを取得します。
	 *
	 * @param int $chain_ID
	 */
	private function getPrivatenetRpcURL( int $chain_ID ): ?string {
		// プライベートネットのURLを取得する関数
		$privatenet = function ( int $number ): string {
			assert( in_array( $number, array( 1, 2 ), true ) );
			$prefix = $this->environment->isTesting() ? 'tests-' : '';
			return "http://{$prefix}privatenet-{$number}.local";
		};

		switch ( $chain_ID ) {
			case ChainIdValue::PRIVATENET_L1:
				return $privatenet( 1 );
			case ChainIdValue::PRIVATENET_L2:
				return $privatenet( 2 );
			default:
				throw new \InvalidArgumentException( '[9739363E] Invalid chain ID. ' . $chain_ID );
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
