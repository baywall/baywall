<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Auth;

use Baywall\Core\Domain\Exception\ValidationFailedException;
use Baywall\Core\Domain\Exception\AuthenticationFailedException;
use WP_REST_Request;

/** X-WP-Nonceを関連サービス */
class WpNonceService {
	/**
	 * REST APIリクエストのヘッダに含まれるnonceを検査します。
	 *
	 * @throws ValidationFailedException nonceがリクエストに含まれていない場合
	 * @throws AuthenticationFailedException nonceが正しくない場合
	 */
	public function checkRequestHeader( WP_REST_Request $request ): void {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! is_string( $nonce ) || '' === $nonce ) {
			throw new ValidationFailedException( '[23273B84] Nonce is required.' );
		}

		if ( false === wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			// X-WP-Nonceヘッダが付与されている場合は
			// ここを通る前にWordPressコアでnonceの検査が行われるため、通常はここを通らない。
			// 念のためチェックを行い、失敗した場合はAuthenticationFailedExceptionをスローする。
			throw new AuthenticationFailedException( '[6EBF149B] wp_verify_nonce failed' );
		}
	}
}
