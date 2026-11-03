<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Entity;

use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Domain\ValueObject\Confirmations;
use Baywall\Core\Domain\ValueObject\MaxLogsRange;
use Baywall\Core\Domain\ValueObject\RpcUrl;

class Chain {
	/**
	 *
	 * @param ChainId           $chain_id
	 * @param string            $name
	 * @param NetworkCategoryId $network_category_id
	 * @param null|RpcUrl       $rpc_url
	 * @param Confirmations     $confirmations
	 * @param MaxLogsRange      $max_logs_range
	 * @param int               $consecutive_failures
	 * @param null|string       $block_explorer_url
	 */
	public function __construct( ChainId $chain_id, string $name, NetworkCategoryId $network_category_id, ?RpcUrl $rpc_url, Confirmations $confirmations, MaxLogsRange $max_logs_range, int $consecutive_failures, ?string $block_explorer_url ) {
		$this->id                   = $chain_id;
		$this->name                 = $name;
		$this->network_category_id  = $network_category_id;
		$this->rpc_url              = $rpc_url;
		$this->confirmations        = $confirmations;
		$this->max_logs_range       = $max_logs_range;
		$this->consecutive_failures = $consecutive_failures;
		$this->block_explorer_url   = $block_explorer_url;
	}

	private readonly ChainId $id;
	private readonly string $name;
	private readonly NetworkCategoryId $network_category_id;
	private ?RpcUrl $rpc_url;
	private Confirmations $confirmations;
	private MaxLogsRange $max_logs_range;
	private int $consecutive_failures;
	private readonly ?string $block_explorer_url;

	public function id(): ChainId {
		return $this->id;
	}

	public function name(): string {
		return $this->name;
	}

	public function rpcUrl(): ?RpcUrl {
		return $this->rpc_url;
	}

	public function setRpcUrl( ?RpcUrl $rpc_url ): void {
		$this->rpc_url = $rpc_url;
	}

	/**
	 * @return Confirmations
	 */
	public function confirmations(): Confirmations {
		return $this->confirmations;
	}

	public function blockExplorerUrl(): ?string {
		return $this->block_explorer_url;
	}

	/**
	 * このチェーンの待機ブロック数を設定します
	 *
	 * @param Confirmations $confirmations
	 */
	public function setConfirmations( Confirmations $confirmations ): void {
		$this->confirmations = $confirmations;
	}

	/** このチェーンに接続可能かどうかを取得します。 */
	public function connectable(): bool {
		// RPC URLが設定されている場合は接続可能とする
		return ! is_null( $this->rpc_url );
	}

	public function networkCategoryId(): NetworkCategoryId {
		return $this->network_category_id;
	}

	/**
	 * このチェーンのログ取得ブロック範囲最大値を取得します
	 *
	 * @return MaxLogsRange
	 */
	public function maxLogsRange(): MaxLogsRange {
		return $this->max_logs_range;
	}

	/**
	 * このチェーンのログ取得ブロック範囲最大値を設定します
	 *
	 * @param MaxLogsRange $max_logs_range
	 */
	public function setMaxLogsRange( MaxLogsRange $max_logs_range ): void {
		$this->max_logs_range = $max_logs_range;
	}

	/** このチェーンのクロール連続失敗回数を取得します */
	public function consecutiveFailures(): int {
		return $this->consecutive_failures;
	}

	/** このチェーンのクロール連続失敗回数をインクリメントします */
	public function incrementConsecutiveFailures(): void {
		++$this->consecutive_failures;
	}

	/** このチェーンのクロール連続失敗回数を 0 にリセットします */
	public function resetConsecutiveFailures(): void {
		$this->consecutive_failures = 0;
	}

	/** このチェーンのクロール連続失敗回数が上限に達しているかどうかを取得します */
	public function hasReachedConsecutiveFailureLimit(): bool {
		return $this->consecutive_failures >= Config::CHAIN_CONSECUTIVE_FAILURE_LIMIT;
	}
}
