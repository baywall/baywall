<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

/** 支払い状況に関するサービスクラス */
abstract class PaymentStatusService {
	/** 指定した請求書IDの支払いがブロック待機まで完了しているかどうかを返します */
	public function isConfirmed( InvoiceId $invoice_id ): bool {
		$remain_confirmations = $this->remainConfirmations( $invoice_id );
		if ( $remain_confirmations === null ) {
			return false;
		}
		assert( $remain_confirmations >= 0, "[A5431A9E] $remain_confirmations" );
		return $remain_confirmations === 0;
	}

	/**
	 * 指定した請求書IDのトランザクションをあと何ブロック待機すればよいかを返します
	 *
	 * 請求書IDのトランザクションがブロックに含まれていない場合はnullを返します
	 */
	abstract public function remainConfirmations( InvoiceId $invoice_id ): ?int;
}
