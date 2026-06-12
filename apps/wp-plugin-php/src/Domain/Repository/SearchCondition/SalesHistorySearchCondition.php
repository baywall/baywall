<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository\SearchCondition;

use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

/**
 * 販売履歴の検索条件をカプセル化するValueObject
 *
 * InvoiceSearchCondition と同じパターンで実装。
 * 今後フィルタ項目が増えても、このクラスにプロパティを追加するだけで
 * Serviceインターフェースのシグネチャを変更せずに拡張できる。
 */
class SalesHistorySearchCondition {

	private ?InvoiceId $invoice_id = null;
	private ?int $date_from        = null;
	private ?int $date_to          = null;

	/** 請求書IDを取得します */
	public function invoiceId(): ?InvoiceId {
		return $this->invoice_id;
	}
	/** 請求書IDを設定します */
	public function setInvoiceId( ?InvoiceId $invoice_id ): self {
		$this->invoice_id = $invoice_id;
		return $this;
	}

	/**
	 * 開始日時（Unixタイムスタンプ、秒）を取得します。
	 * 指定された場合、この日時以降の販売履歴を取得します。
	 */
	public function dateFrom(): ?int {
		return $this->date_from;
	}
	/** 開始日時を設定します */
	public function setDateFrom( ?int $date_from ): self {
		$this->date_from = $date_from;
		return $this;
	}

	/**
	 * 終了日時（Unixタイムスタンプ、秒）を取得します。
	 * 指定された場合、この日時以前の販売履歴を取得します。
	 */
	public function dateTo(): ?int {
		return $this->date_to;
	}
	/** 終了日時を設定します */
	public function setDateTo( ?int $date_to ): self {
		$this->date_to = $date_to;
		return $this;
	}
}
