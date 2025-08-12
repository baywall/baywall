<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\AppContractTableRecord;
use Cornix\Serendipity\Core\Infrastructure\Format\UnixTimestampFormat;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use stdClass;

/**
 * Appコントラクトの情報を記録するテーブル
 * ※ `crawled_block_number`の初期化は invoice の発行時に行われます。
 */
class AppContractTable extends TableBase {

	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->appContract() );
	}

	/**
	 * @return AppContractTableRecord[]
	 */
	public function all(): array {
		$sql     = <<<SQL
			SELECT `chain_id`, `address`, `crawled_block_number`, `crawled_block_number_updated_at`
			FROM `{$this->tableName()}`
		SQL;
		$results = $this->safeGetResults( $sql );

		return array_map(
			fn( stdClass $record ) => new AppContractTableRecord( $record ),
			$results
		);
	}

	public function save( AppContract $app_contract ): void {
		$crawled_block_number_value      = $app_contract->crawledBlockNumber() ?
			$app_contract->crawledBlockNumber()->int() :
			null;
		$crawled_block_number_updated_at = $app_contract->crawledBlockNumberUpdatedAt() ?
			UnixTimestampFormat::toMySQL( $app_contract->crawledBlockNumberUpdatedAt() ) :
			null;

		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
				(`chain_id`, `address`, `crawled_block_number`, `crawled_block_number_updated_at`)
			VALUES
				(:chain_id, :address, :crawled_block_number, :crawled_block_number_updated_at)
			ON DUPLICATE KEY UPDATE
				`address` = VALUES(`address`),
				`crawled_block_number` = VALUES(`crawled_block_number`),
				`crawled_block_number_updated_at` = VALUES(`crawled_block_number_updated_at`)
		SQL;
		$sql = $this->namedPrepare(
			$sql,
			array(
				':chain_id'                        => $app_contract->chain()->id()->value(),
				':address'                         => $app_contract->address()->value(),
				':crawled_block_number'            => $crawled_block_number_value,
				':crawled_block_number_updated_at' => $crawled_block_number_updated_at,
			)
		);

		$this->safeQuery( $sql );
	}
}
