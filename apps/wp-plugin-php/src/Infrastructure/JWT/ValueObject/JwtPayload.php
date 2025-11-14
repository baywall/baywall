<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Base\ArrayValueObject;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;

/**
 * 本プラグインで使用するJWT形式のアクセストークンのペイロードを表すクラス
 */
final class JwtPayload extends ArrayValueObject {

	/** ウォレットアドレス */
	private const PAYLOAD_KEY_WALLET_ADDRESS = 'sub';
	// /** 発行者(URL) */
	// private const PAYLOAD_KEY_ISSUER = 'iss';
	/** 発行日時(UNIXタイムスタンプ) */
	private const PAYLOAD_KEY_ISSUED_AT = 'iat';
	/** 有効期限(UNIXタイムスタンプ) */
	private const PAYLOAD_KEY_EXPIRES_AT = 'exp';

	private Address $wallet_address;
	private UnixTimestamp $issued_at;
	private UnixTimestamp $expires_at;

	private function __construct( Address $wallet_address, UnixTimestamp $issued_at, UnixTimestamp $expires_at ) {
		$jwt_payload_value = array(
			self::PAYLOAD_KEY_WALLET_ADDRESS => $wallet_address->value(),
			self::PAYLOAD_KEY_EXPIRES_AT     => $expires_at->value(),
			self::PAYLOAD_KEY_ISSUED_AT      => $issued_at->value(),
		);
		parent::__construct( $jwt_payload_value );

		// 発行日時 >= 有効期限 の時はエラー
		if ( $issued_at->value() >= $expires_at->value() ) {
			throw new \InvalidArgumentException( "[EC884A2F] Invalid JWT payload: issued_at must be earlier than expires_at. {$issued_at}, {$expires_at}" );
		}

		$this->wallet_address = $wallet_address;
		$this->issued_at      = $issued_at;
		$this->expires_at     = $expires_at;
	}

	// public static function from( array $jwt_payload_value ): self {
	// $wallet_address = Address::from( $jwt_payload_value[ self::PAYLOAD_KEY_WALLET_ADDRESS ] );
	// $expires_at     = UnixTimestamp::from( $jwt_payload_value[ self::PAYLOAD_KEY_EXPIRES_AT ] );
	// $issued_at      = UnixTimestamp::from( $jwt_payload_value[ self::PAYLOAD_KEY_ISSUED_AT ] );
	//
	// return new self( $wallet_address, $expires_at, $issued_at );
	// }

	/** JWTペイロードを作成します */
	public static function create( Address $wallet_address, UnixTimestamp $issued_at, UnixTimestamp $expires_at ): self {
		return new self( $wallet_address, $issued_at, $expires_at );
	}

	/** ウォレットアドレスを取得します */
	public function walletAddress(): Address {
		return $this->wallet_address;
	}

	/** 発行日時を取得します */
	public function issuedAt(): UnixTimestamp {
		return $this->issued_at;
	}

	/** 有効期限を取得します */
	public function expiresAt(): UnixTimestamp {
		return $this->expires_at;
	}
}
