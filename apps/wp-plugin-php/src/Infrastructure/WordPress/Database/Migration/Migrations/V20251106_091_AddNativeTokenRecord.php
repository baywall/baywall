<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Decimals;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\ChainIdConstants;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_091_AddNativeTokenRecord extends MigrationBase {

	private TransactionService $transaction_service;
	private MyWpdb $wpdb;
	private string $table_name;
	private Environment $environment;

	public function __construct( TransactionService $transaction_service, MyWpdb $wpdb, TableNameProvider $table_name_provider, Environment $environment ) {
		$this->transaction_service = $transaction_service;
		$this->wpdb                = $wpdb;
		$this->table_name          = $table_name_provider->token();
		$this->environment         = $environment;
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		$this->transaction_service->transactional(
			function () {
				// メインネットのネイティブトークンを登録
				$this->insert( ChainIdConstants::ETHEREUM, 'ETH' );

				// テストネットのネイティブトークンを登録
				$this->insert( ChainIdConstants::SEPOLIA, 'ETH' );

				// 開発モード及びテストモード時はプライベートネットのネイティブトークンを登録
				if ( $this->environment->isDevelopment() || $this->environment->isTesting() ) {
					$this->insert( ChainIdConstants::PRIVATENET1, 'ETH' );
					$this->insert( ChainIdConstants::PRIVATENET2, 'POL' );
				}
			}
		);
	}

	public function down(): void {
		$this->wpdb->dbh->query( "TRUNCATE TABLE `{$this->table_name}`;" );
	}

	private function insert( int $chain_id_value, string $symbol_value ): void {
		$this->wpdb->insert(
			$this->table_name,
			array(
				'chain_id'   => ChainId::from( $chain_id_value )->value(),
				'address'    => Address::nativeToken()->value(),
				'symbol'     => Symbol::from( $symbol_value )->value(),
				'decimals'   => Decimals::from( 18 )->value(),  // ネイティブトークンは小数点以下18桁で固定
				'is_payable' => (int) false,    // 初期値は支払い不可の状態にする
			)
		);
	}
}
