<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Infrastructure\Format\UnixTimestampFormat;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\RefreshTokenTable;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Entity\RefreshTokenInfo;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\HashedRefreshToken;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\RefreshToken;

class WpRefreshTokenRepository {

	public function __construct( RefreshTokenTable $refresh_token_table, UnixTimestampFormat $unix_timestamp_format ) {
		$this->refresh_token_table   = $refresh_token_table;
		$this->unix_timestamp_format = $unix_timestamp_format;
	}

	private RefreshTokenTable $refresh_token_table;
	private UnixTimestampFormat $unix_timestamp_format;

	/**
	 * リフレッシュトークン情報を保存（追加）します。
	 */
	public function add( RefreshTokenInfo $refresh_token_info ): void {
		$this->refresh_token_table->add( $refresh_token_info );
	}

	/**
	 * リフレッシュトークンから、リフレッシュトークン情報を取得します。
	 */
	public function get( RefreshToken $refresh_token ): ?RefreshTokenInfo {
		$record = $this->refresh_token_table->get( $refresh_token->hash() );
		if ( $record === null ) {
			return null;
		}

		assert( $record->refreshTokenHashValue() === $refresh_token->hash()->value(), '[459CDA79]' );
		return RefreshTokenInfo::create(
			HashedRefreshToken::from( $record->refreshTokenHashValue() ),
			Address::from( $record->walletAddressValue() ),
			$this->unix_timestamp_format->fromMySQL( $record->expiresAtValue() ),
			$this->unix_timestamp_format->fromMySQL( $record->revokedAtValue() )
		);
	}
}
