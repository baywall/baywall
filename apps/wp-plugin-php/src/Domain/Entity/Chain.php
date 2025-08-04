<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;

class Chain {
	/**
	 *
	 * @param ChainId           $chain_id
	 * @param string            $name
	 * @param NetworkCategoryId $network_category_id
	 * @param null|RpcUrl       $rpc_url
	 * @param Confirmations     $confirmations
	 * @param null|string       $block_explorer_url
	 */
	protected function __construct( ChainId $chain_id, string $name, NetworkCategoryId $network_category_id, ?RpcUrl $rpc_url, Confirmations $confirmations, ?string $block_explorer_url ) {
		$this->id                  = $chain_id;
		$this->name                = $name;
		$this->network_category_id = $network_category_id;
		$this->rpc_url             = $rpc_url;
		$this->confirmations       = $confirmations;
		$this->block_explorer_url  = $block_explorer_url;
	}

	private ChainId $id;
	private string $name;
	private NetworkCategoryId $network_category_id;
	private ?RpcUrl $rpc_url;
	private Confirmations $confirmations;
	private ?string $block_explorer_url;

	public function id(): ChainId {
		return $this->id;
	}
	public function name(): string {
		return $this->name;
	}
	public function rpcURL(): ?RpcUrl {
		return $this->rpc_url;
	}
	public function setRpcURL( ?RpcUrl $rpc_url ): void {
		$this->rpc_url = $rpc_url;
	}
	/**
	 * @return Confirmations
	 */
	public function confirmations(): Confirmations {
		return $this->confirmations;
	}

	public function blockExplorerURL(): ?string {
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
}
