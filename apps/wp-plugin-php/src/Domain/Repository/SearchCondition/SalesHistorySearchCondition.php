<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository\SearchCondition;

use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;

/**
 * 販売履歴の検索条件をカプセル化するValueObject
 *
 * InvoiceSearchCondition と同じパターンで実装。
 * 今後フィルタ項目が増えても、このクラスにプロパティを追加するだけで
 * Serviceインターフェースのシグネチャを変更せずに拡張できる。
 */
class SalesHistorySearchCondition {

	private ?InvoiceId $invoice_id             = null;
	private ?ChainId $chain_id                 = null;
	private ?TransactionHash $transaction_hash = null;
	private ?int $date_from                    = null;
	private ?int $date_to                      = null;
	private ?int $limit                        = null;
	private ?int $offset                       = null;

	/** 請求書IDを取得します */
	public function invoiceId(): ?InvoiceId {
		return $this->invoice_id;
	}
	/** 請求書IDを設定します */
	public function setInvoiceId( ?InvoiceId $invoice_id ): self {
		$this->invoice_id = $invoice_id;
		return $this;
	}

	/** チェーンIDを取得します */
	public function chainId(): ?ChainId {
		return $this->chain_id;
	}
	/** チェーンIDを設定します */
	public function setChainId( ?ChainId $chain_id ): self {
		$this->chain_id = $chain_id;
		return $this;
	}

	/** トランザクションハッシュを取得します */
	public function transactionHash(): ?TransactionHash {
		return $this->transaction_hash;
	}
	/** トランザクションハッシュを設定します */
	public function setTransactionHash( ?TransactionHash $transaction_hash ): self {
		$this->transaction_hash = $transaction_hash;
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

	/**
	 * 取得件数上限を取得します。null の場合は呼び出し側のデフォルトに従います。
	 */
	public function limit(): ?int {
		return $this->limit;
	}
	/** 取得件数上限を設定します */
	public function setLimit( ?int $limit ): self {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * 取得開始位置（オフセット）を取得します。null の場合はオフセットなし（先頭から）です。
	 */
	public function offset(): ?int {
		return $this->offset;
	}
	/** 取得開始位置を設定します */
	public function setOffset( ?int $offset ): self {
		$this->offset = $offset;
		return $this;
	}
}
