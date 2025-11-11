<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\RefreshTokenTableRecord;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Entity\RefreshTokenInfo;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\RefreshTokenHash;

/**
 * 認証用のリフレッシュトークンの情報を記録するテーブル
 */
class RefreshTokenTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->refreshToken();
	}

	public function get( RefreshTokenHash $refresh_token_hash ): ?RefreshTokenTableRecord {
		$sql = $this->wpdb->prepare(
			<<<SQL
				SELECT `refresh_token_hash`, `wallet_address`, `expires_at`, `revoked_at`
				FROM `{$this->table_name}`
				WHERE `refresh_token_hash` = :refresh_token_hash
				LIMIT 1
			SQL,
			array( ':refresh_token_hash' => $refresh_token_hash->value() )
		);

		$record = $this->wpdb->getRow( $sql );

		return $record === null ? null : new RefreshTokenTableRecord( $record );
	}

	public function add( RefreshTokenInfo $refresh_token_info ): void {
		if ( $refresh_token_info->revokedAt() !== null ) {
			// 新規追加時、無効化日時はnull
			throw new \InvalidArgumentException( '[991AECA4] When adding a new refresh token, revoked_at must be null.' );
		}

		$result = $this->wpdb->insert(
			$this->table_name,
			array(
				'refresh_token_hash' => $refresh_token_info->refreshTokenHash()->value(),
				'wallet_address'     => $refresh_token_info->walletAddress()->value(),
				'expires_at'         => $refresh_token_info->expiresAt()->toMySqlValue(),
				'revoked_at'         => null, // 追加時はrevoked_atはNULLで登録
			)
		);

		if ( $result !== 1 ) {
			throw new \RuntimeException( '[2005DFF8] Failed to insert refresh token record.' );
		}
	}
}
