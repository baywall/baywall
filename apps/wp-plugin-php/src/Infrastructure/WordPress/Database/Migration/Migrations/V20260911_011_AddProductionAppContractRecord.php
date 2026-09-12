<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Infrastructure\Web3\Constants\ChainIdConstants;
use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

/**
 * 本番環境で使用する App コントラクトアドレスを全チェーンへ投入するマイグレーション
 * ※ アドレスは Chain ごとに同一のものを使用する
 */
class V20260911_011_AddProductionAppContractRecord extends MigrationBase {

	/** 本番環境で使用する App コントラクトアドレス（全チェーン共通） */
	private const PRODUCTION_APP_CONTRACT_ADDRESS = '0xa9B8F9aAb08A700fB17a10568502A573Ae63F625';

	/** 本番用アドレスを投入するチェーンID（メインネット3・テストネット3） */
	private const CHAIN_IDS = array(
		ChainIdConstants::ETHEREUM,
		ChainIdConstants::SEPOLIA,
		ChainIdConstants::POLYGON_POS,
		ChainIdConstants::POLYGON_AMOY,
		ChainIdConstants::BASE,
		ChainIdConstants::BASE_SEPOLIA,
	);

	/** 本マイグレーション適用前からレコードが存在する（ゼロアドレスが投入されている）チェーンID */
	private const EXISTING_CHAIN_IDS = array(
		ChainIdConstants::SEPOLIA,
		ChainIdConstants::BASE_SEPOLIA,
		ChainIdConstants::POLYGON_AMOY,
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
		return '0.0.7-alpha.1';
	}

	public function up(): void {
		// 定数は EIP-55 形式（大文字を含む）。`address` 列の CHECK 制約は小文字 hex のみ許可するため、
		// Address 値オブジェクトで形式検証と小文字正規化を行ってから投入する
		$address = Address::from( self::PRODUCTION_APP_CONTRACT_ADDRESS )->value();

		$this->transaction_service->transactional(
			function () use ( $address ) {
				foreach ( self::CHAIN_IDS as $chain_id ) {
					$this->upsert( $chain_id, $address );
				}
			}
		);
	}

	public function down(): void {
		$this->transaction_service->transactional(
			function () {
				// 本番環境における適用前の状態（V20260726_011 が投入した状態）へ復元する
				// ※ 開発/テスト環境は setup-devtest-db.php が次回のマイグレーション実行時に上書きする
				$zero_address = Address::zero()->value();
				foreach ( self::EXISTING_CHAIN_IDS as $chain_id ) {
					$this->upsert( $chain_id, $zero_address );
				}

				// 本マイグレーションで新規追加したレコード（メインネット）は削除する
				foreach ( array_diff( self::CHAIN_IDS, self::EXISTING_CHAIN_IDS ) as $chain_id ) {
					$this->wpdb->delete( $this->table_name, array( 'chain_id' => $chain_id ) );
				}
			}
		);
	}

	/**
	 * レコードが存在すればアドレスを更新し、存在しなければ挿入します
	 *
	 * 呼び出し元に依存せず、値オブジェクトによるアドレスの形式検証・小文字正規化を行います
	 * （`V20260726_011_AddAppContractRecord::insert()` と同一の責務分担）
	 */
	private function upsert( int $chain_id, string $address ): void {
		ChainId::from( $chain_id ); // verify
		$normalized_address = Address::from( $address )->value();

		$sql = <<<SQL
			INSERT INTO `{$this->table_name}` (`chain_id`, `address`)
			VALUES (:chain_id, :address)
			ON DUPLICATE KEY UPDATE `address` = VALUES(`address`)
		SQL;
		$sql = $this->wpdb->named_prepare(
			$sql,
			array(
				':chain_id' => $chain_id,
				':address'  => $normalized_address,
			)
		);

		$this->wpdb->query( $sql );
	}
}
