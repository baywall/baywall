<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\ERC4361;

/**
 * ERC-4361のフォーマットに従ったメッセージを構築するクラス
 *
 * @see https://eips.ethereum.org/EIPS/eip-4361#message-format
 */
class Erc4361MessageBuilder {
	// ※ schemeやResources等、仕様の一部を省略しています
	public function buildMessage(
		string $domain,
		string $address,
		?string $statement,
		string $uri,
		string $version,
		string $chain_id,
		string $nonce,
		string $issued_at
	): string {
		$message = "{$domain} wants you to sign in with your Ethereum account:\n{$address}\n\n";
		if ( $statement !== null && $statement !== '' ) {
			$message .= "{$statement}\n";
		}
		$message .= "\nURI: {$uri}\nVersion: {$version}\nChain ID: {$chain_id}\nNonce: {$nonce}\nIssued At: {$issued_at}";

		return $message;
	}
}
