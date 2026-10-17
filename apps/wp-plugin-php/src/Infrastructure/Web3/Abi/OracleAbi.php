<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Abi;

class OracleAbi {

	public function get(): array {
		$abi_json = <<<JSON
		{
			"abi": [
				{
					"inputs": [],
					"name": "decimals",
					"outputs": [
						{
							"internalType": "uint8",
							"name": "",
							"type": "uint8"
						}
					],
					"stateMutability": "view",
					"type": "function"
				},
				{
					"inputs": [],
					"name": "description",
					"outputs": [
						{
							"internalType": "string",
							"name": "",
							"type": "string"
						}
					],
					"stateMutability": "view",
					"type": "function"
				},
				{
					"inputs": [],
					"name": "latestRoundData",
					"outputs": [
						{
							"internalType": "uint80",
							"name": "roundId",
							"type": "uint80"
						},
						{
							"internalType": "int256",
							"name": "answer",
							"type": "int256"
						},
						{
							"internalType": "uint256",
							"name": "startedAt",
							"type": "uint256"
						},
						{
							"internalType": "uint256",
							"name": "updatedAt",
							"type": "uint256"
						},
						{
							"internalType": "uint80",
							"name": "answeredInRound",
							"type": "uint80"
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
