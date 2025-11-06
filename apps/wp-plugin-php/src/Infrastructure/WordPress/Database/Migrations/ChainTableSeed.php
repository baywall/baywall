<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\ChainIdConstants;
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
	protected function insertChainRecord( int $chain_id_value, string $name, NetworkCategoryId $network_category_id, ?string $rpc_url_value, string $block_explorer_url ): void {
		$chain_id      = ChainId::from( $chain_id_value );
		$confirmations = Confirmations::from( 1 ); // 初期値として設定する確認数は1
		$rpc_url       = RpcUrl::fromNullable( $rpc_url_value );
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
		// Mainnet --------------------
		// Ethereum
		$this->insertChainRecord(
			ChainIdConstants::ETHEREUM,
			'Ethereum',
			NetworkCategoryId::mainnet(),
			null, // RPC URLはnull
			'https://etherscan.io'
		);

		// Testnet --------------------
		// Sepolia
		$this->insertChainRecord(
			ChainIdConstants::SEPOLIA,
			'Sepolia',
			NetworkCategoryId::testnet(),
			null, // RPC URLはnull
			'https://sepolia.etherscan.io'
		);

		// 開発、テスト時はプライベートネットのチェーン情報も登録
		if ( $this->environment->isDevelopment() || $this->environment->isTesting() ) {
			$is_development = $this->environment->isDevelopment();

			// Privatenet 1
			$this->insertChainRecord(
				ChainIdConstants::PRIVATENET1,
				'Privatenet1',
				NetworkCategoryId::privatenet(),
				$is_development ? 'http://privatenet-1.test' : 'http://tests-privatenet-1.test',
				'http://localhost:10101'    // ブロックエクスプローラーURL
			);

			// Privatenet 2
			$this->insertChainRecord(
				ChainIdConstants::PRIVATENET2,
				'Privatenet2',
				NetworkCategoryId::privatenet(),
				$is_development ? 'http://privatenet-2.test' : 'http://tests-privatenet-2.test',
				'http://localhost:10102'    // ブロックエクスプローラーURL
			);
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
