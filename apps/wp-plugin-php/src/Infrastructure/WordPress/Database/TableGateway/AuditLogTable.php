<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Application\Port\Clock;
use Baywall\Core\Application\Logging\ValueObject\LogLevel;
use Baywall\Core\Domain\Service\PluginInfoProvider;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

/**
 * 監査ログを記録するテーブル
 */
class AuditLogTable {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider, private readonly Clock $clock, private readonly PluginInfoProvider $plugin_info_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->logAudit();
	}

	/**
	 * 監査ログレコードを保存します。
	 *
	 * @param LogLevel $level   ログレベル
	 * @param int      $user_id WordPressユーザーID（未ログイン時は0）
	 * @param string   $action  操作種別
	 * @param string   $message メッセージ
	 */
	public function insert( LogLevel $level, int $user_id, string $action, string $message ): void {
		$this->wpdb->insert(
			$this->table_name,
			array(
				'created_at'     => $this->clock->now()->value(),
				'plugin_version' => $this->plugin_info_provider->version(),
				'user_id'        => $user_id,
				'level'          => $level->name(),
				'action'         => $action,
				'message'        => $message,
			),
			array( '%d', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
