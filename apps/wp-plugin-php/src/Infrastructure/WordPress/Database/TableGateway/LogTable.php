<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

/**
 * ログを記録するテーブル
 */
class LogTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->log();
	}

	/**
	 * ログレコードを保存します。
	 *
	 * @param LogLevel    $level    ログレベル
	 * @param LogCategory $category ログカテゴリ
	 * @param string      $message  メッセージ
	 */
	public function insert( LogLevel $level, LogCategory $category, string $message ): void {
		$this->wpdb->insert(
			$this->table_name,
			array(
				'level'    => $level->name(),
				'category' => $category->name(),
				'message'  => $message,
			),
			array( '%s', '%s', '%s' )
		);
	}

	/**
	 * 指定秒数より古いログレコードを削除します。
	 *
	 * @param int $expiration_seconds 保持期間（秒）。この秒数より古いレコードが削除対象となります。
	 */
	public function deleteOldRecords( int $expiration_seconds ): void {
		$table_name = $this->table_name;
		$cutoff     = gmdate( 'Y-m-d H:i:s', time() - $expiration_seconds );
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
	 * @return array ログレコードの連想配列
	 */
	public function selectRecent( int $limit ): array {
		$sql = $this->wpdb->named_prepare(
			"SELECT `id`, `created_at`, `level`, `category`, `message` FROM `{$this->table_name}` ORDER BY `created_at` DESC LIMIT :limit",
			array( ':limit' => $limit )
		);
		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: array();
	}
}
