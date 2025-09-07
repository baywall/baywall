<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Exception\ChainConnectionException;
use Cornix\Serendipity\Core\Application\Exception\InvoiceNonceMismatchException;
use Cornix\Serendipity\Core\Application\Exception\PurchaseValidationException;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetPaidContentByNonce;
use Cornix\Serendipity\Core\Application\UseCase\GetPostDto;

class ResolveRequestPaidContentByNonce {

	// ここの定数は、GraphQLのエラーコードと一致させること
	private const ERROR_CODE_INVALID_NONCE           = 'INVALID_NONCE';
	private const ERROR_CODE_INVALID_CHAIN_ID        = 'INVALID_CHAIN_ID';
	private const ERROR_CODE_PAYWALL_LOCKED          = 'PAYWALL_LOCKED'; // TODO: 削除
	private const ERROR_CODE_TRANSACTION_UNCONFIRMED = 'TRANSACTION_UNCONFIRMED';

	private UserAccessChecker $user_access_checker;
	private GetPostDto $get_post_dto;
	private GetPaidContentByNonce $get_paid_content_by_nonce;

	public function __construct(
		UserAccessChecker $user_access_checker,
		GetPostDto $get_post_dto,
		GetPaidContentByNonce $get_paid_content_by_nonce
	) {
		$this->user_access_checker       = $user_access_checker;
		$this->get_post_dto              = $get_post_dto;
		$this->get_paid_content_by_nonce = $get_paid_content_by_nonce;
	}

	public function handle( array $root_value, array $args ) {
		/** @var string */
		$invoice_nonce_value = $args['nonce'];
		/** @var string */
		$invoice_id_value = $args['invoiceId'];
		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $this->get_post_dto->handleByInvoiceId( $invoice_id_value )->id );

		try {
			$result = $this->get_paid_content_by_nonce->handle( $invoice_id_value, $invoice_nonce_value );
			return array(
				'content'   => $result->paid_content,
				'newNonce'  => $result->new_nonce,
				'errorCode' => null,
			);
		} catch ( \Throwable $e ) {
			// 例外が発生した場合はエラーコードを設定
			if ( $e instanceof InvoiceNonceMismatchException ) {
				// invoice に紐づく nonce が期待する値と一致しなかった場合
				$error_code = self::ERROR_CODE_INVALID_NONCE;
			} elseif ( $e instanceof ChainConnectionException ) {
				// チェーンへの接続に失敗した場合
				$error_code = self::ERROR_CODE_INVALID_CHAIN_ID;
			} elseif ( $e instanceof PurchaseValidationException ) {
				// 購入が確認できなかった場合
				$error_code = self::ERROR_CODE_TRANSACTION_UNCONFIRMED;
			} else {
				throw $e; // その他の例外はそのまま投げる
			}
			return array(
				'content'   => null,
				'newNonce'  => null,
				'errorCode' => $error_code,
			);
		}
	}
}
