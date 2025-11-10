<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\RefreshTokenTableRecord;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Entity\RefreshTokenInfo;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\HashedRefreshToken;

/**
 * 認証用のリフレッシュトークンの情報を記録するテーブル
 */
class RefreshTokenTable extends TableBase {

	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->refreshToken() );
	}

	public function get( HashedRefreshToken $hashed_refresh_token ): ?RefreshTokenTableRecord {
		$sql = <<<SQL
			SELECT `refresh_token_hash`, `wallet_address`, `expires_at`, `revoked_at`
			FROM `{$this->tableName()}`
			WHERE `refresh_token_hash` = %s
			LIMIT 1
		SQL;
		$sql = $this->prepare( $sql, $hashed_refresh_token->value() );

		$record = $this->safeGetRow( $sql );

		return $record === null ? null : new RefreshTokenTableRecord( $record );
	}

	public function add( RefreshTokenInfo $refresh_token_info ): void {
		if ( $refresh_token_info->revokedAt() !== null ) {
			// 新規追加時、無効化日時はnull
			throw new \InvalidArgumentException( '[991AECA4] When adding a new refresh token, revoked_at must be null.' );
		}

		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			( `refresh_token_hash`, `wallet_address`, `expires_at`, `revoked_at` )
			VALUES ( %s, %s, %s, %s )
		SQL;
		$sql = $this->prepare(
			$sql,
			$refresh_token_info->hashedRefreshToken()->value(),
			$refresh_token_info->walletAddress()->value(),
			$refresh_token_info->expiresAt()->value(),
			null // 追加時はrevoked_atはNULLで登録
		);

		$result = $this->safeQuery( $sql );
		if ( $result !== 1 ) {
			throw new \RuntimeException( '[2005DFF8] Failed to insert refresh token record.' );
		}
	}
}
