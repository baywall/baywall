<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\InitCrawledBlockNumber;
use Cornix\Serendipity\Core\Application\UseCase\IssueInvoice;

class IssueInvoiceResolver extends ResolverBase {

	public function __construct(
		IssueInvoice $issue_invoice,
		InitCrawledBlockNumber $init_crawled_block_number,
		UserAccessChecker $user_access_checker,
		TransactionService $transaction_service
	) {
		$this->issue_invoice             = $issue_invoice;
		$this->init_crawled_block_number = $init_crawled_block_number;
		$this->user_access_checker       = $user_access_checker;
		$this->transaction_service       = $transaction_service;
	}

	private IssueInvoice $issue_invoice;
	private InitCrawledBlockNumber $init_crawled_block_number;
	private UserAccessChecker $user_access_checker;
	private TransactionService $transaction_service;

	/**
	 * #[\Override]
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_id = $args['postID'];
		/** @var int */
		$chain_id_value = $args['chainId'];
		/** @var string */
		$token_address_value = $args['tokenAddress'];
		/** @var string */
		$consumer_address_value = $args['consumerAddress']; // 購入者のアドレス

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 請求書番号を発行(+現在の販売価格を記録)
		try {
			$this->transaction_service->beginTransaction();

			// invoiceを発行
			$issued_invoice_dto = $this->issue_invoice->handle( $post_id, $chain_id_value, $token_address_value, $consumer_address_value );
			// クロール済みブロック番号を初期化
			$this->init_crawled_block_number->handle( $chain_id_value );

			$this->transaction_service->commit();
		} catch ( \Throwable $e ) {
			$this->transaction_service->rollback();
			throw $e;
		}

		return array(
			'invoiceIdHex'    => $issued_invoice_dto->id_hex,
			'nonce'           => $issued_invoice_dto->nonce,
			'serverMessage'   => $issued_invoice_dto->server_message,
			'serverSignature' => $issued_invoice_dto->server_signature,
			'paymentAmount'   => $issued_invoice_dto->payment_amount,
		);
	}
}
