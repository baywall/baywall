<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Repository\Erc4361NonceRepository;
use Baywall\Core\Application\Service\AccessTokenCookieProvider;
use Baywall\Core\Application\Service\AccessTokenService;
use Baywall\Core\Application\Service\Erc4361Service;
use Baywall\Core\Application\Service\RefreshTokenCookieProvider;
use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Domain\Exception\HttpStatus\BadRequestException;
use Baywall\Core\Domain\Service\RefreshTokenService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Signature;
use Baywall\Core\Infrastructure\Cookie\CookieWriter;
use Baywall\Core\Infrastructure\Web3\Service\SignatureService;

/** ERC-4361の署名を検証してアクセストークン(+リフレッシュトークン)を発行するクラス */
class ResolveIssueAccessTokenByErc4361Signature {


	public function __construct(
		private readonly TransactionService $transaction_service,
		private Erc4361Service $erc4361_service,
		private Erc4361NonceRepository $erc4361_nonce_repository,
		private readonly SignatureService $signature_service,
		private readonly RefreshTokenService $refresh_token_service,
		private readonly RefreshTokenCookieProvider $refresh_token_cookie_provider,
		private readonly AccessTokenCookieProvider $access_token_cookie_provider,
		private readonly AccessTokenService $access_token_service,
		private readonly CookieWriter $cookie_writer
	) {}

	public function handle( array $root_value, array $args ) {
		$address   = Address::from( $args['address'] );
		$chain_id  = ChainId::from( $args['chainId'] );
		$signature = Signature::from( $args['signature'] );

		// 指定されたアドレスから、保存済みのnonceを取得
		$stored_nonce = $this->erc4361_nonce_repository->get( $address );

		// 保存済みのnonceを使って署名用メッセージを再構築
		$message = $this->erc4361_service->createMessage( $address, $chain_id, $stored_nonce );
		// 再構築したメッセージと、受け取った署名からアドレスを計算
		$recovered_address = $this->signature_service->recoverAddress( $message, $signature );

		if ( ! $address->equals( $recovered_address ) ) {
			// 署名の検証に失敗した場合はエラー
			// ※ 第三者がリクエストを送信している可能性もあるため、保存済みnonce削除は行わない
			throw new BadRequestException( "[27FA5840] ERC-4361 signature verification failed for address: {$address}" );
		}

		return $this->transaction_service->transactional(
			function () use ( $address ) {
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
