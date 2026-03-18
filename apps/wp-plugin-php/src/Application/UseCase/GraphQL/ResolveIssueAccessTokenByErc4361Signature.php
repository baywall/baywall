<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\Erc4361NonceRepository;
use Cornix\Serendipity\Core\Application\Service\AccessTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\Erc4361Service;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\BadRequestException;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\SignatureService;

/** ERC-4361の署名を検証してアクセストークン(+リフレッシュトークン)を発行するクラス */
class ResolveIssueAccessTokenByErc4361Signature {

	private TransactionService $transaction_service;
	private Erc4361Service $erc4361_service;
	private Erc4361NonceRepository $erc4361_nonce_repository;
	private SignatureService $signature_service;
	private RefreshTokenService $refresh_token_service;
	private RefreshTokenCookieProvider $refresh_token_cookie_provider;
	private AccessTokenCookieProvider $access_token_cookie_provider;
	private AccessTokenService $access_token_service;
	private CookieWriter $cookie_writer;

	public function __construct( TransactionService $transaction_service, Erc4361Service $erc4361_service, Erc4361NonceRepository $erc4361_nonce_repository, SignatureService $signature_service, RefreshTokenService $refresh_token_service, RefreshTokenCookieProvider $refresh_token_cookie_provider, AccessTokenCookieProvider $access_token_cookie_provider, AccessTokenService $access_token_service, CookieWriter $cookie_writer ) {
		$this->transaction_service           = $transaction_service;
		$this->erc4361_service               = $erc4361_service;
		$this->erc4361_nonce_repository      = $erc4361_nonce_repository;
		$this->signature_service             = $signature_service;
		$this->refresh_token_service         = $refresh_token_service;
		$this->refresh_token_cookie_provider = $refresh_token_cookie_provider;
		$this->access_token_cookie_provider  = $access_token_cookie_provider;
		$this->access_token_service          = $access_token_service;
		$this->cookie_writer                 = $cookie_writer;
	}

	public function handle( array $root_value, array $args ) {
		$address   = Address::from( $args['address'] );
		$chain_id  = ChainId::from( $args['chainId'] );
		$signature = Signature::from( $args['signature'] );

		return $this->transaction_service->transactional(
			function () use ( $address, $chain_id, $signature ) {
				// 指定されたアドレスから、保存済みのnonceを取得
				$stored_nonce = $this->erc4361_nonce_repository->get( $address );

				// 保存済みのnonceを使って署名用メッセージを再構築
				$message = $this->erc4361_service->createMessage( $address, $chain_id, $stored_nonce );
				// 再構築したメッセージと、受け取った署名からアドレスを計算
				$recovered_address = $this->signature_service->recoverAddress( $message, $signature );

				if ( ! $address->equals( $recovered_address ) ) {
					// 署名の検証に失敗した場合はエラー
					throw new BadRequestException( "[27FA5840] ERC-4361 signature verification failed for address: {$address}" );
				}

				// リフレッシュトークンを発行し、クッキーに保存
				$refresh_token        = $this->refresh_token_service->issue( $address );
				$refresh_token_cookie = $this->refresh_token_cookie_provider->get( $refresh_token );
				$this->cookie_writer->set( $refresh_token_cookie );

				// アクセストークンを発行
				$access_token        = $this->access_token_service->issue( $address );
				$access_token_cookie = $this->access_token_cookie_provider->get( $access_token );
				$this->cookie_writer->set( $access_token_cookie );

				// 保存していたnonceをリポジトリから削除
				$this->erc4361_nonce_repository->delete( $address );

				return array(
					'success' => true,
				);
			}
		);
	}
}
