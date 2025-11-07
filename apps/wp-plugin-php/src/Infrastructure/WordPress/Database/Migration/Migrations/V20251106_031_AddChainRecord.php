<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\ChainIdConstants;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_031_AddChainRecord extends MigrationBase {

	private TransactionService $transaction_service;
	private MyWpdb $wpdb;
	private string $table_name;
	private Environment $environment;

	public function __construct( TransactionService $transaction_service, MyWpdb $wpdb, TableNameProvider $table_name_provider, Environment $environment ) {
		$this->transaction_service = $transaction_service;
		$this->wpdb                = $wpdb;
		$this->table_name          = $table_name_provider->chain();
		$this->environment         = $environment;
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		$this->transaction_service->transactional(
			function () {
				// Mainnet --------------------
				// Ethereum
				$this->insert(
					ChainIdConstants::ETHEREUM,
					'Ethereum',
					NetworkCategoryId::mainnet(),
					null, // RPC URLはnull
					'https://etherscan.io'
				);

				// Testnet --------------------
				// Sepolia
				$this->insert(
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
					$this->insert(
						ChainIdConstants::PRIVATENET1,
						'Privatenet1',
						NetworkCategoryId::privatenet(),
						$is_development ? 'http://privatenet-1.test' : 'http://tests-privatenet-1.test',
						'http://localhost:10101'    // ブロックエクスプローラーURL
					);

					// Privatenet 2
					$this->insert(
						ChainIdConstants::PRIVATENET2,
						'Privatenet2',
						NetworkCategoryId::privatenet(),
						$is_development ? 'http://privatenet-2.test' : 'http://tests-privatenet-2.test',
						'http://localhost:10102'    // ブロックエクスプローラーURL
					);
				}
			}
		);
	}

	public function down(): void {
		$this->wpdb->dbh->query( "TRUNCATE TABLE `{$this->table_name}`;" );
	}

	private function insert( int $chain_id_value, string $name, NetworkCategoryId $network_category_id, ?string $rpc_url_value, string $block_explorer_url ): void {
		$chain_id      = ChainId::from( $chain_id_value );
		$confirmations = Confirmations::from( 1 ); // 初期値として設定する確認数は1
		$rpc_url       = RpcUrl::fromNullable( $rpc_url_value );
		$this->wpdb->insert(
			$this->table_name,
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
