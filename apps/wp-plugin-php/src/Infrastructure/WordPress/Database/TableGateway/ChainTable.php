<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Baywall\Core\Infrastructure\WordPress\Database\ValueObject\ChainTableRecord;
use stdClass;

/**
 * チェーンの情報を記録するテーブル
 */
class ChainTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->chain();
	}

	/**
	 * @return ChainTableRecord[]
	 */
	public function all(): array {
		$sql     = <<<SQL
			SELECT `chain_id`, `name`, `network_category_id`, `rpc_url`, `confirmations`, `max_logs_range`, `block_explorer_url`
			FROM `{$this->table_name}`
		SQL;
		$results = $this->wpdb->get_results( $sql );

		return array_map(
			fn( stdClass $record ) => new ChainTableRecord( $record ),
			$results
		);
	}

	public function save( Chain $chain ): void {
		$now = UnixTimestamp::now()->value();
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
				(`chain_id`, `name`, `network_category_id`, `rpc_url`, `confirmations`, `max_logs_range`, `block_explorer_url`, `created_at`, `updated_at`)
			VALUES
				(:chain_id, :name, :network_category_id, :rpc_url, :confirmations, :max_logs_range, :block_explorer_url, :created_at, :updated_at)
			ON DUPLICATE KEY UPDATE
				`name` = VALUES(`name`),
				`network_category_id` = VALUES(`network_category_id`),
				`rpc_url` = VALUES(`rpc_url`),
				`confirmations` = VALUES(`confirmations`),
				`max_logs_range` = VALUES(`max_logs_range`),
				`block_explorer_url` = VALUES(`block_explorer_url`),
				`updated_at` = VALUES(`updated_at`)
		SQL;
		$sql = $this->wpdb->named_prepare(
			$sql,
			array(
				':chain_id'            => $chain->id()->value(),
				':name'                => $chain->name(),
				':network_category_id' => $chain->networkCategoryId()->value(),
				':rpc_url'             => $chain->rpcUrl() ? $chain->rpcUrl()->value() : null,
				':confirmations'       => $chain->confirmations()->value(),
				':max_logs_range'      => $chain->maxLogsRange(),
				':block_explorer_url'  => $chain->blockExplorerUrl(),
				':created_at'          => $now,
				':updated_at'          => $now,
			)
		);

		$this->wpdb->query( $sql );
	}
}
