<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\BadRequestException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\UnauthorizedException;
use WP_REST_Request;

/** X-WP-Nonceを関連サービス */
class WpNonceService {
	/**
	 * REST APIリクエストのヘッダに含まれるnonceを検査します。
	 *
	 * @throws BadRequestException nonceがリクエストに含まれていない場合
	 * @throws UnauthorizedException nonceが正しくない場合
	 */
	public function checkRequestHeader( WP_REST_Request $request ): void {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! is_string( $nonce ) || '' === $nonce ) {
			throw new BadRequestException( '[23273B84] Nonce is required.' );
		}

		if ( false === wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			// X-WP-Nonceヘッダが付与されている場合は
			// ここを通る前にWordPressコアでnonceの検査が行われるため、通常はここを通らない。
			// 念のためチェックを行い、失敗した場合はUnauthorizedExceptionをスローする。
			throw new UnauthorizedException( '[6EBF149B] wp_verify_nonce failed' );
		}
	}
}
