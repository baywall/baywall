<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\ChainTableRecord;
use stdClass;

/**
 * チェーンの情報を記録するテーブル
 */
class ChainTable extends TableBase {

	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->chain() );
	}

	/**
	 * @return ChainTableRecord[]
	 */
	public function all(): array {
		$sql     = <<<SQL
			SELECT `chain_id`, `name`, `network_category_id`, `rpc_url`, `confirmations`, `block_explorer_url`
			FROM `{$this->tableName()}`
		SQL;
		$results = $this->safeGetResults( $sql );

		return array_map(
			fn( stdClass $record ) => new ChainTableRecord( $record ),
			$results
		);
	}

	public function save( Chain $chain ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
				(`chain_id`, `name`, `network_category_id`, `rpc_url`, `confirmations`, `block_explorer_url`)
			VALUES
				(:chain_id, :name, :network_category_id, :rpc_url, :confirmations, :block_explorer_url)
			ON DUPLICATE KEY UPDATE
				`name` = VALUES(`name`),
				`network_category_id` = VALUES(`network_category_id`),
				`rpc_url` = VALUES(`rpc_url`),
				`confirmations` = VALUES(`confirmations`),
				`block_explorer_url` = VALUES(`block_explorer_url`)
		SQL;
		$sql = $this->namedPrepare(
			$sql,
			array(
				':chain_id'            => $chain->id()->value(),
				':name'                => $chain->name(),
				':network_category_id' => $chain->networkCategoryId()->value(),
				':rpc_url'             => $chain->rpcUrl() ? $chain->rpcUrl()->value() : null,
				':confirmations'       => (string) $chain->confirmations()->value(),
				':block_explorer_url'  => $chain->blockExplorerUrl(),
			)
		);

		$this->safeQuery( $sql );
	}
}
