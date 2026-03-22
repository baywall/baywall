<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\LockService;
use wpdb;

class WpLockService extends LockService {

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;

	/** @inheritdoc */
	public function acquire( string $key, int $timeout ): bool {
		// ロックの取得に成功した場合は 1 を返し、試行がタイムアウトになった場合
		// (たとえば、ほかのクライアントがすでにその名前をロックしている場合) は 0 を返し、
		// エラー (メモリー不足や mysqladmin kill によるスレッドの停止など) が発生した場合は NULL を返します。
		// @see https://dev.mysql.com/doc/refman/8.0/ja/locking-functions.html#function_get-lock
		$sql    = $this->wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $key, $timeout );
		$result = $this->wpdb->get_var( $sql );
		assert( $result === '1' || $result === '0' || is_null( $result ), "[2B7D3C13] result: {$result}" );
		return is_null( $result ) ? false : (bool) $result;
	}

	/** @inheritdoc */
	public function release( string $key ): bool {
		// GET_LOCK() を使用して取得された文字列 str によって名前が付けられたロックを解除します。
		// ロックが解除された場合は 1 を返し、このスレッドによってロックが確立されなかった場合
		// (その場合、ロックは解除されません) は 0 を返し、名前付きのロックが存在しない場合は NULL を返します。
		// GET_LOCK() を呼び出しても取得されなかった場合や、事前に解除された場合は、ロックが存在しません。
		// @see https://dev.mysql.com/doc/refman/8.0/ja/locking-functions.html#function_release-lock
		$sql    = $this->wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $key );
		$result = $this->wpdb->get_var( $sql );
		assert( $result === '1' || $result === '0' || is_null( $result ), "[6C8335E9] result: {$result}" );
		return is_null( $result ) ? false : (bool) $result;
	}
}
