<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\RefreshToken;
use Cornix\Serendipity\Core\Domain\Repository\RefreshTokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\RefreshTokenString;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\RefreshTokenTable;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\WpRefreshTokenHashString;

class WpRefreshTokenRepository implements RefreshTokenRepository {

	public function __construct( RefreshTokenTable $refresh_token_table ) {
		$this->refresh_token_table = $refresh_token_table;
	}

	private RefreshTokenTable $refresh_token_table;

	/**
	 * リフレッシュトークン情報を保存（追加）します。
	 */
	public function add( RefreshToken $refresh_token ): void {
		$this->refresh_token_table->add( $refresh_token );
	}

	/**
	 * リフレッシュトークンの文字列から、リフレッシュトークン情報を取得します。
	 */
	public function get( RefreshTokenString $refresh_token_string ): ?RefreshToken {

		$refresh_token_hash_string = WpRefreshTokenHashString::from( $refresh_token_string );

		$record = $this->refresh_token_table->get( $refresh_token_hash_string );
		if ( $record === null ) {
			return null;
		}
		assert( hash_equals( $record->refreshTokenHashValue(), $refresh_token_hash_string->value() ), '[43E7665D]' );

		return RefreshToken::create(
			$refresh_token_string,
			Address::from( $record->walletAddressValue() ),
			UnixTimestamp::fromMySql( $record->expiresAtValue() ),
			UnixTimestamp::fromMySqlNullable( $record->revokedAtValue() )
		);
	}

	/**
	 * リフレッシュトークン情報を更新します。
	 */
	public function update( RefreshToken $refresh_token ): void {
		$this->refresh_token_table->update( $refresh_token );
	}

	/** @inheritdoc */
	public function deleteByCreatedAt( UnixTimestamp $target_time ): void {
		$this->refresh_token_table->deleteByCreatedAt( $target_time );
	}
}
