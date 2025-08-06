<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Cache;

class TransientStorage {

	/**
	 * WordPress における set_transient のキー最大長
	 *
	 * - WordPress < 4.4 では 45 文字
	 * - WordPress >= 4.4 では 172 文字
	 *
	 * @see https://developer.wordpress.org/reference/functions/set_transient/#parameters
	 */
	private const MAX_TRANSIENT_KEY_LENGTH = 172;

	public function get( string $transient, $default = null ) {
		$this->checkKey( $transient );

		$result = get_transient( $transient );
		return false === $result ? $default : $result;
	}

	public function set( string $transient, $value, int $expiration = 0 ): void {
		$this->checkKey( $transient );

		$success = set_transient( $transient, $value, $expiration );
		if ( true !== $success ) {
			throw new \RuntimeException( "[DC367CB7] Failed to set transient for key: '{$transient}'" );
		}
	}

	/** キーの検証を行います */
	private function checkKey( string $key ): void {
		if ( strlen( $key ) > self::MAX_TRANSIENT_KEY_LENGTH ) {
			throw new \InvalidArgumentException( '[2870F5C6] Transient key must be a string with a maximum length of ' . self::MAX_TRANSIENT_KEY_LENGTH . " characters. key: {$key}" );
		}
	}
}
