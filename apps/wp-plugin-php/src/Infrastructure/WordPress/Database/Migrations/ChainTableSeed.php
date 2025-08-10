<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Registry\ChainIdRegistry;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use wpdb;


class ChainTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
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

abstract class ChainTableSeedBase extends MigrationBase {
	protected function insertChainRecord( ChainId $chain_id, string $name, NetworkCategoryId $network_category_id, ?RpcUrl $rpc_url, string $block_explorer_url ): void {
		$confirmations = Confirmations::from( 1 ); // 初期値として設定する確認数は1
		$this->insert(
			$this->tableName(),
			array(
				'chain_id'            => $chain_id->value(),
				'name'                => $name,
				'network_category_id' => $network_category_id->value(),
				'rpc_url'             => $rpc_url ? $rpc_url->value() : null,
				'confirmations'       => (string) $confirmations,
				'block_explorer_url'  => $block_explorer_url,
			)
		);
	}
}

/** @internal */
class ChainTableSeed_0_0_1 extends ChainTableSeedBase {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}

	private Environment $environment;

	public function up(): void {
		// Mainnet
		$mainnet         = NetworkCategoryId::mainnet();
		$mainnet_rpc_url = null; // Mainnetの場合、RPC URLの初期値はnull
		$this->insertChainRecord( ChainIdRegistry::ethMainnet(), 'Ethereum Mainnet', $mainnet, $mainnet_rpc_url, 'https://etherscan.io' );

		// Testnet
		$testnet         = NetworkCategoryId::testnet();
		$testnet_rpc_url = null; // Testnetの場合、RPC URLの初期値はnull
		$this->insertChainRecord( ChainIdRegistry::sepolia(), 'Sepolia', $testnet, $testnet_rpc_url, 'https://sepolia.etherscan.io' );
		$this->insertChainRecord( ChainIdRegistry::soneiumMinato(), 'Soneium Testnet Minato', $testnet, $testnet_rpc_url, 'https://soneium-minato.blockscout.com' );

		// 開発モード時はプライベートネットのチェーン情報も登録
		if ( $this->environment->isDevelopment() ) {
			$privatenet           = NetworkCategoryId::privatenet();
			$privatenet_rpc_url_1 = $this->getPrivatenetRpcUrl( ChainIdRegistry::privatenetL1() );
			$privatenet_rpc_url_2 = $this->getPrivatenetRpcUrl( ChainIdRegistry::privatenetL2() );

			$this->insertChainRecord( ChainIdRegistry::privatenetL1(), 'Privatenet1', $privatenet, $privatenet_rpc_url_1, 'http://localhost:10101' );
			$this->insertChainRecord( ChainIdRegistry::privatenetL2(), 'Privatenet2', $privatenet, $privatenet_rpc_url_2, 'http://localhost:10102' );
		}
	}

	/**
	 * 指定されたプライベートネットのチェーンIDに対応するRPC URLを取得します。
	 */
	private function getPrivatenetRpcUrl( ChainId $chain_id ): RpcUrl {
		// プライベートネットのURLを取得する関数
		$privatenet = function ( int $number ): RpcUrl {
			assert( in_array( $number, array( 1, 2 ), true ) );
			$prefix = $this->environment->isTesting() ? 'tests-' : '';
			return RpcUrl::from( "http://{$prefix}privatenet-{$number}.local" );
		};

		if ( $chain_id->equals( ChainIdRegistry::privatenetL1() ) ) {
			return $privatenet( 1 );
		} elseif ( $chain_id->equals( ChainIdRegistry::privatenetL2() ) ) {
			return $privatenet( 2 );
		} else {
			throw new \InvalidArgumentException( "[11301D24] Invalid chain ID. {$chain_id}" );
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
