<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Infrastructure\Util\Strings;

/**
 * RPC URLを表すValueObjectクラス
 */
final class RpcUrl {

	private function __construct( string $rpc_url_value ) {
		self::checkValidRpcUrlFormat( $rpc_url_value );
		$this->rpc_url_value = $rpc_url_value;
	}
	private string $rpc_url_value;

	public static function from( string $rpc_url_value ): self {
		return new self( $rpc_url_value );
	}
	public static function fromNullable( ?string $rpc_url_value ): ?self {
		return is_null( $rpc_url_value ) ? null : new self( $rpc_url_value );
	}

	public function value(): string {
		return $this->rpc_url_value;
	}

	public function __toString(): string {
		return $this->rpc_url_value;
	}

	public function equals( self $other ): bool {
		return $this->rpc_url_value === $other->rpc_url_value;
	}

	private static function checkValidRpcUrlFormat( string $rpc_url_value ): void {
		$is_url = filter_var( $rpc_url_value, FILTER_VALIDATE_URL ) !== false && Strings::starts_with( $rpc_url_value, 'http' );
		if ( ! $is_url ) {
			throw new \InvalidArgumentException( '[A8D0FAC8] Invalid RPC URL format. ' . $rpc_url_value );
		}
	}
}
