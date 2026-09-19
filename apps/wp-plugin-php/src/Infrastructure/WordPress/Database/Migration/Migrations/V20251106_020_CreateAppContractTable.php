<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\Web3\Constants\ChainIdConstants;
use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_020_CreateAppContractTable extends MigrationBase {

	/** 本番環境で使用する App コントラクトアドレス（全チェーン共通） */
	private const PRODUCTION_APP_CONTRACT_ADDRESS = '0xa9B8F9aAb08A700fB17a10568502A573Ae63F625';

	/** 初期投入するチェーンID（メインネット3・テストネット3） */
	private const CHAIN_IDS = array(
		ChainIdConstants::ETHEREUM,
		ChainIdConstants::SEPOLIA,
		ChainIdConstants::POLYGON_POS,
		ChainIdConstants::POLYGON_AMOY,
		ChainIdConstants::BASE,
		ChainIdConstants::BASE_SEPOLIA,
	);

	private TransactionService $transaction_service;
	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( TransactionService $transaction_service, MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->transaction_service = $transaction_service;
		$this->wpdb                = $wpdb;
		$this->table_name          = $table_name_provider->appContract();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// 定数は EIP-55 形式（大文字を含む）。`address` 列の CHECK 制約は小文字 hex のみ許可するため、
		// Address 値オブジェクトで形式検証と小文字正規化を行ってから投入する。
		// 定数の破損は DDL より前に検出するため、CREATE TABLE の前に求める
		$address = Address::from( self::PRODUCTION_APP_CONTRACT_ADDRESS )->value();
		$now     = UnixTimestamp::now()->value();

		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`                       bigint        unsigned  NOT NULL,
				`updated_at`                       bigint        unsigned  NOT NULL,
				`chain_id`                         bigint        unsigned  NOT NULL,
				`address`                          varchar(191)            NOT NULL,
				PRIMARY KEY (`chain_id`),
				CONSTRAINT `chk_{$this->table_name}_address` CHECK (CONVERT(`address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$')
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );

		// テーブル作成は暗黙コミットを伴うためトランザクションに含められない。INSERT のみをまとめて実行する
		// `CHAIN_IDS` は `ChainIdConstants` の正の int リテラルで `ChainId` の検証（正数のみ）を自明に通るため、
		// `V20251106_031_AddChainRecord` と異なり `ChainId::from()` による確認は行わない
		$this->transaction_service->transactional(
			function () use ( $address, $now ) {
				foreach ( self::CHAIN_IDS as $chain_id ) {
					$this->wpdb->insert(
						$this->table_name,
						array(
							'chain_id'   => $chain_id,
							'address'    => $address,
							'created_at' => $now,
							'updated_at' => $now,
						)
					);
				}
			}
		);
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
