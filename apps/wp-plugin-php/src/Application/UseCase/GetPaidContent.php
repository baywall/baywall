<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Application\ValueObject\AccessToken;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\ForbiddenException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\UnauthorizedException;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockHeight;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\Http\BearerTokenService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;
use RuntimeException;

class GetPaidContent {

	private BearerTokenService $bearer_token_service;
	private AccessTokenService $access_token_service;
	private InvoiceRepository $invoice_repository;
	private ChainRepository $chain_repository;
	private PostRepository $post_repository;
	private BlockNumberProvider $block_number_provider;
	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;

	public function __construct(
		BearerTokenService $bearer_token_service,
		AccessTokenService $access_token_service,
		InvoiceRepository $invoice_repository,
		ChainRepository $chain_repository,
		PostRepository $post_repository,
		BlockNumberProvider $block_number_provider,
		UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository
	) {
		$this->bearer_token_service                     = $bearer_token_service;
		$this->access_token_service                     = $access_token_service;
		$this->invoice_repository                       = $invoice_repository;
		$this->chain_repository                         = $chain_repository;
		$this->post_repository                          = $post_repository;
		$this->block_number_provider                    = $block_number_provider;
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
	}

	public function handle( string $invoice_id_hex_value ): string {
		$invoice_id = InvoiceId::fromHex( Hex::from( $invoice_id_hex_value ) );

		// アクセストークンをHTTPヘッダから取得
		$access_token_value = $this->bearer_token_service->get();
		if ( $access_token_value === null ) {
			throw new UnauthorizedException( '[8AD8257F] Access token is missing.' );
		}
		$access_token = AccessToken::from( $access_token_value );

		// アクセストークンの妥当性（有効期限切れ）チェック
		// ※ 署名が不正な場合はdecode時に例外がスローされる
		if ( ! $this->access_token_service->isValid( $access_token ) ) {
			throw new UnauthorizedException( '[3975F78C] Access token is invalid.' );
		}

		// アクセストークンの所有者（ウォレットアドレス）を取得
		$address = $this->access_token_service->getWalletAddress( $access_token );
		// 請求書IDから請求書情報を取得
		$invoice = $this->invoice_repository->get( $invoice_id );

		// 請求書IDのチェック
		if ( $invoice === null ) {
			throw new \InvalidArgumentException( "[5B98E562] Invoice not found: {$invoice_id}" );
		} elseif ( ! $invoice->consumerAddress()->equals( $address ) ) {
			// 購入者チェック。別のユーザーの請求書IDを指定した場合は例外をスロー
			throw new ForbiddenException( "[200D84DE] Address mismatch. client: {$address}, invoice: {$invoice->consumerAddress()}" );
		}

		// 支払い確認チェック。指定した待機ブロック数経過していない場合は例外をスロー
		if ( ! $this->isConfirmed( $invoice ) ) {
			throw new PaymentRequiredException( "[E868EFEF] Payment is not confirmed for invoice: {$invoice->id()}" );
		}

		return $this->post_repository->get( $invoice->postId() )->paidContent()->value();
	}


	private function isConfirmed( Invoice $invoice ): bool {
		// チェーンのconfirmationsを取得
		$chain_confirmations = $this->chain_repository->get( $invoice->chainId() )->confirmations();
		if ( ! is_int( $chain_confirmations->value() ) ) {
			throw new RuntimeException( "[C6F8F959] Not supported chain for confirmations type. {$$chain_confirmations}" );
		}
		$required_confirmations = BlockHeight::from( $chain_confirmations->value() );

		// 購入時のトランザクションが含まれるブロック番号を取得
		$payment_block_number = $this->unlock_paywall_transfer_event_repository->getBlockNumber( $invoice->id() );

		// 現在のブロック番号を取得
		$current_block_number = $this->block_number_provider->getByChainId( $invoice->chainId(), BlockTag::latest() );

		// 確認ブロック数を計算
		$confirmed_block_height_value = $current_block_number->int() - $payment_block_number->int() + 1;
		// 負の値にならないように念のため補正(購入時のブロック番号が取得できているので1以上)
		$confirmed_block_height = BlockHeight::from( max( 1, $confirmed_block_height_value ) );

		// 支払いが完了しているかどうかを判定
		$is_confirmed = $required_confirmations->value() <= $confirmed_block_height->value();

		return $is_confirmed;
	}
}
