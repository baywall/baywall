<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Abi;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\TransactionHash;
use Cornix\Serendipity\Core\Domain\ValueObject\UnlockPaywallTransferType;
use Cornix\Serendipity\Core\Infrastructure\Web3\Abi\Base\AbiBase;
use Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject\UnlockPaywallTransferEvent;
use phpseclib\Math\BigInteger;
use stdClass;

class AppContractAbi extends AbiBase {

	private const EVENT_NAME_UNLOCK_PAYWALL_TRANSFER = 'UnlockPaywallTransfer';

	/** @var array|null */
	private $abi_cache = null;

	public function get(): array {
		if ( $this->abi_cache === null ) {
			$this->abi_cache = AppContractAbiData::get();
		}
		return $this->abi_cache;
	}

	public function unlockPaywallTransferTopicHash(): string {
		return $this->topicHash( self::EVENT_NAME_UNLOCK_PAYWALL_TRANSFER );
	}

	public function decodeUnlockPaywallTransferEvent( stdClass $log ): UnlockPaywallTransferEvent {
		$decoded_event_params = $this->decodeEventParameters( $log );

		assert( is_string( $decoded_event_params['signer'] ), '[A18B1291] ' . var_export( $decoded_event_params['signer'], true ) );
		assert( is_string( $decoded_event_params['from'] ), '[FD9BA69A] ' . var_export( $decoded_event_params['from'], true ) );
		assert( is_string( $decoded_event_params['to'] ), '[8E6055C5] ' . var_export( $decoded_event_params['to'], true ) );
		assert( is_string( $decoded_event_params['token'] ), '[E3FD1079] ' . var_export( $decoded_event_params['token'], true ) );
		assert( $decoded_event_params['amount'] instanceof BigInteger, '[AF7DF1B0] ' . var_export( $decoded_event_params['amount'], true ) );
		assert( $decoded_event_params['invoiceId'] instanceof BigInteger, '[D454A801] ' . var_export( $decoded_event_params['invoiceId'], true ) );
		assert( $decoded_event_params['transferType'] instanceof BigInteger, '[2CC10192] ' . var_export( $decoded_event_params['transferType'], true ) );

		return new UnlockPaywallTransferEvent(
			BlockNumber::fromHex( Hex::from( $log->blockNumber ) ), // block_number
			hexdec( $log->logIndex ), // log_index
			TransactionHash::from( $log->transactionHash ), // transaction_hash
			InvoiceId::fromHex( Hex::from( '0x' . $decoded_event_params['invoiceId']->toHex() ) ), // invoice_id
			Address::from( $decoded_event_params['signer'] ), // server_signer_address
			Address::from( $decoded_event_params['from'] ), // from_address
			Address::from( $decoded_event_params['to'] ), // to_address
			Address::from( $decoded_event_params['token'] ), // token_address
			Amount::from( $decoded_event_params['amount']->toString() ), // amount
			UnlockPaywallTransferType::from( (int) ( $decoded_event_params['transferType'] )->toString() ) // transfer_type
		);
	}
}


class AppContractAbiData {
	public static function get(): array {
		$abi_json = <<<JSON
			{
				"abi": [
					{
						"anonymous": false,
						"inputs": [
							{
								"indexed": true,
								"internalType": "address",
								"name": "signer",
								"type": "address"
							},
							{
								"indexed": true,
								"internalType": "address",
								"name": "from",
								"type": "address"
							},
							{
								"indexed": true,
								"internalType": "address",
								"name": "to",
								"type": "address"
							},
							{
								"indexed": false,
								"internalType": "address",
								"name": "token",
								"type": "address"
							},
							{
								"indexed": false,
								"internalType": "uint256",
								"name": "amount",
								"type": "uint256"
							},
							{
								"indexed": false,
								"internalType": "uint256",
								"name": "invoiceId",
								"type": "uint256"
							},
							{
								"indexed": false,
								"internalType": "uint32",
								"name": "transferType",
								"type": "uint32"
							}
						],
						"name": "UnlockPaywallTransfer",
						"type": "event"
					},
					{
						"inputs": [
							{
								"internalType": "address",
								"name": "signer",
								"type": "address"
							},
							{
								"internalType": "uint256",
								"name": "postId",
								"type": "uint256"
							},
							{
								"internalType": "address",
								"name": "consumer",
								"type": "address"
							}
						],
						"name": "getPaywallStatus",
						"outputs": [
							{
								"internalType": "bool",
								"name": "isUnlocked",
								"type": "bool"
							},
							{
								"internalType": "uint256",
								"name": "invoiceId",
								"type": "uint256"
							},
							{
								"internalType": "uint256",
								"name": "unlockedBlockNumber",
								"type": "uint256"
							}
						],
						"stateMutability": "view",
						"type": "function"
					}
				]
			}
			JSON;

		return json_decode( $abi_json, true )['abi'];
	}
}
