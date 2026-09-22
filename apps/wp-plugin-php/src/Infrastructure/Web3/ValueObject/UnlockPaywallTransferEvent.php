<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\ValueObject;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\BlockHash;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Domain\ValueObject\UnlockPaywallTransferType;

class UnlockPaywallTransferEvent {
	public function __construct(
		private readonly BlockNumber $block_number,
		private readonly int $log_index,
		private readonly TransactionHash $transaction_hash,
		private readonly InvoiceId $invoice_id,
		private readonly Address $server_signer_address,
		private readonly Address $from_address,
		private readonly Address $to_address,
		private readonly Address $token_address,
		private readonly Amount $amount,
		private readonly UnlockPaywallTransferType $transfer_type,
		private readonly UnixTimestamp $block_timestamp,
		private readonly BlockHash $block_hash,
		private readonly bool $removed
	) {}

	public function blockNumber(): BlockNumber {
		return $this->block_number;
	}
	public function logIndex(): int {
		return $this->log_index;
	}
	public function transactionHash(): TransactionHash {
		return $this->transaction_hash;
	}
	public function invoiceId(): InvoiceId {
		return $this->invoice_id;
	}
	public function serverSignerAddress(): Address {
		return $this->server_signer_address;
	}
	public function fromAddress(): Address {
		return $this->from_address;
	}
	public function toAddress(): Address {
		return $this->to_address;
	}
	public function tokenAddress(): Address {
		return $this->token_address;
	}
	public function amount(): Amount {
		return $this->amount;
	}
	public function transferType(): UnlockPaywallTransferType {
		return $this->transfer_type;
	}
	public function blockTimestamp(): UnixTimestamp {
		return $this->block_timestamp;
	}
	public function blockHash(): BlockHash {
		return $this->block_hash;
	}
	public function removed(): bool {
		return $this->removed;
	}
}
