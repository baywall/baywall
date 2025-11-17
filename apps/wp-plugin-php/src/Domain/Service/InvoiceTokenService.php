<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Entity\InvoiceToken;
use Cornix\Serendipity\Core\Domain\Exception\InvalidInvoiceTokenException;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceTokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;

abstract class InvoiceTokenService {

	/** 請求書トークン文字列を生成します */
	abstract protected function generateInvoiceTokenString(): InvoiceTokenString;

	/** 請求書トークンの有効期限を取得します */
	abstract protected function getExpiresAt(): UnixTimestamp;

	private InvoiceTokenRepository $invoice_token_repository;

	protected function __construct( InvoiceTokenRepository $invoice_token_repository ) {
		$this->invoice_token_repository = $invoice_token_repository;
	}

	/**
	 * 請求書トークンを発行します
	 */
	public function issue( InvoiceId $invoice_id ): InvoiceToken {
		$new_invoice_token = InvoiceToken::create(
			$invoice_id,
			$this->generateInvoiceTokenString(),
			$this->getExpiresAt(),
			null // 請求書トークン生成時、`revoked_at`はnullに設定
		);

		$this->invoice_token_repository->add( $new_invoice_token );

		return $new_invoice_token;
	}

	/**
	 * トークンローテーションを実行します
	 *
	 * @param InvoiceId          $invoice_id           請求書ID
	 * @param InvoiceTokenString $invoice_token_string クライアントから送信された請求書トークン文字列
	 */
	public function rotation( InvoiceId $invoice_id, InvoiceTokenString $invoice_token_string ): InvoiceToken {

		$invoice_token = $this->invoice_token_repository->get( $invoice_id, $invoice_token_string );

		// 以下の場合は例外をスロー
		// - データベースに存在しない請求書トークン
		// - 無効化された請求書トークン
		// - 期限切れの請求書トークン
		if ( $invoice_token === null || $invoice_token->isRevoked() || $invoice_token->isExpired() ) {
			throw new InvalidInvoiceTokenException( '[1109F03D] Invalid invoice token.' );
		}

		// 古い請求書トークンを無効化
		$invoice_token->revoke();
		$this->invoice_token_repository->update( $invoice_token );

		// 新しい請求書トークンを生成して保存
		$new_invoice_token = InvoiceToken::create(
			$invoice_id,
			$this->generateInvoiceTokenString(),
			$this->getExpiresAt(),
			null // 請求書トークン生成時、`revoked_at`はnullに設定
		);
		$this->invoice_token_repository->add( $new_invoice_token );

		return $new_invoice_token;
	}
}
