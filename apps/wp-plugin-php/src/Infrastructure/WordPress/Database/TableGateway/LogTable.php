<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Application\Port\Clock;
use Baywall\Core\Application\Logging\ValueObject\LogLevel;
use Baywall\Core\Domain\Service\PluginInfoProvider;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;
use stdClass;

/**
 * ログを記録するテーブル
 */
class LogTable {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider, private readonly Clock $clock, private readonly PluginInfoProvider $plugin_info_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->logApp();
	}

	/**
	 * ログレコードを保存します。
	 *
	 * @param LogLevel $level   ログレベル
	 * @param string   $message メッセージ
	 */
	public function insert( LogLevel $level, string $message ): void {
		$this->wpdb->insert(
			$this->table_name,
			array(
				'created_at'     => $this->clock->now()->value(),
				'plugin_version' => $this->plugin_info_provider->version(),
				'level'          => $level->name(),
				'message'        => $message,
			),
			array( '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * 指定秒数より古いログレコードを削除します。
	 *
	 * @param int $expiration_seconds 保持期間（秒）。この秒数より古いレコードが削除対象となります。
	 */
	public function deleteOldRecords( int $expiration_seconds ): void {
		$table_name = $this->table_name;
		$cutoff     = $this->clock->now()->value() - $expiration_seconds;
		$sql        = $this->wpdb->named_prepare(
			"DELETE FROM `{$table_name}` WHERE `created_at` < :cutoff",
			array( ':cutoff' => $cutoff )
		);
		$this->wpdb->query( $sql );
	}

	/**
	 * 最近のログレコードを取得します。
	 *
	 * @param int $limit 取得件数の上限
	 * @return list<stdClass> ログレコード
	 */
	public function selectRecent( int $limit ): array {
		$sql     = $this->wpdb->named_prepare(
			"SELECT `log_id`, `created_at`, `level`, `message` FROM `{$this->table_name}` ORDER BY `created_at` DESC, `log_id` DESC LIMIT :limit",
			array( ':limit' => $limit )
		);
		$results = $this->wpdb->get_results( $sql );

		return $results;
	}
}
