<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Confirmations;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\Web3\Constants\ChainIdConstants;
use Baywall\Core\Infrastructure\Web3\Constants\NetworkCategoryIdConstants;
use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_031_AddChainRecord extends MigrationBase {

	private readonly TransactionService $transaction_service;
	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( TransactionService $transaction_service, MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->transaction_service = $transaction_service;
		$this->wpdb                = $wpdb;
		$this->table_name          = $table_name_provider->chain();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		$now = UnixTimestamp::now()->value();
		$this->transaction_service->transactional(
			function () use ( $now ) {
				// Mainnet --------------------
				// Ethereum
				$this->insert(
					ChainIdConstants::ETHEREUM,
					'Ethereum',
					NetworkCategoryIdConstants::MAINNET,
					null, // RPC URLはnull
					'https://etherscan.io',
					$now
				);

				// Base
				$this->insert(
					ChainIdConstants::BASE,
					'Base',
					NetworkCategoryIdConstants::MAINNET,
					null, // RPC URLはnull
					'https://basescan.org',
					$now
				);

				// Polygon PoS
				$this->insert(
					ChainIdConstants::POLYGON_POS,
					'Polygon PoS',
					NetworkCategoryIdConstants::MAINNET,
					null, // RPC URLはnull
					'https://polygonscan.com',
					$now
				);

				// Testnet --------------------
				// Sepolia
				$this->insert(
					ChainIdConstants::SEPOLIA,
					'Sepolia',
					NetworkCategoryIdConstants::TESTNET,
					null, // RPC URLはnull
					'https://sepolia.etherscan.io',
					$now
				);

				// Base Sepolia
				$this->insert(
					ChainIdConstants::BASE_SEPOLIA,
					'Base Sepolia',
					NetworkCategoryIdConstants::TESTNET,
					null, // RPC URLはnull
					'https://sepolia.basescan.org',
					$now
				);

				// Polygon Amoy
				$this->insert(
					ChainIdConstants::POLYGON_AMOY,
					'Polygon Amoy',
					NetworkCategoryIdConstants::TESTNET,
					null, // RPC URLはnull
					'https://amoy.polygonscan.com',
					$now
				);
			}
		);
	}

	public function down(): void {
		$this->wpdb->query( "TRUNCATE TABLE `{$this->table_name}`;" );
	}

	private function insert( int $chain_id_value, string $name, int $network_category_id, ?string $rpc_url_value, string $block_explorer_url, int $now ): void {
		$chain_id      = ChainId::from( $chain_id_value );
		$confirmations = Confirmations::from( 1 ); // 初期値として設定する確認数は1
		$rpc_url       = RpcUrl::fromNullable( $rpc_url_value );
		$this->wpdb->insert(
			$this->table_name,
			array(
				'chain_id'            => $chain_id->value(),
				'name'                => $name,
				'network_category_id' => $network_category_id,
				'rpc_url'             => $rpc_url?->value(),
				'confirmations'       => $confirmations->value(),
				'block_explorer_url'  => $block_explorer_url,
				'created_at'          => $now,
				'updated_at'          => $now,
			)
		);
	}
}
