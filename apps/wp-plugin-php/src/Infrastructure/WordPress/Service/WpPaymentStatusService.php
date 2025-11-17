<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Service\PaymentStatusService;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;

class WpPaymentStatusService extends PaymentStatusService {

	private BlockNumberProvider $block_number_provider;
	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;
	private InvoiceRepository $invoice_repository;
	private ChainRepository $chain_repository;

	public function __construct( BlockNumberProvider $block_number_provider, UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository, InvoiceRepository $invoice_repository, ChainRepository $chain_repository ) {
		$this->block_number_provider                    = $block_number_provider;
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
		$this->invoice_repository                       = $invoice_repository;
		$this->chain_repository                         = $chain_repository;
	}

	/** @inheritDoc */
	public function remainConfirmations( InvoiceId $invoice_id ): ?int {
		// 購入時のトランザクションが含まれるブロック番号を取得
		$payment_block_number = $this->unlock_paywall_transfer_event_repository->getBlockNumber( $invoice_id );
		if ( $payment_block_number === null ) {
			return null;
		}

		// 請求書のチェーン情報を取得
		$chain_id      = $this->invoice_repository->get( $invoice_id )->chainId();
		$chain         = $this->chain_repository->get( $chain_id );
		$confirmations = $chain->confirmations();

		if ( ! is_int( $confirmations->value() ) ) {
			throw new \Exception( "[04160684] Not implemented {$confirmations->value()}" );
		}
		/** @var int */
		$confirmations_int_value = $confirmations->value(); // 対象のチェーンの必要確認数

		// 現在の最新ブロック番号を取得
		$current_block_number = $this->block_number_provider->getByChainId( $chain_id, BlockTag::latest() );

		// 残り待機ブロック数を計算
		$remains = ( $payment_block_number->int() + ( $confirmations_int_value - 1 ) ) - $current_block_number->int();

		return $remains > 0 ? $remains : 0;
	}
}
