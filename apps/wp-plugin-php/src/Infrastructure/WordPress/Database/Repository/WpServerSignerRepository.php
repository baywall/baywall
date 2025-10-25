<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\ServerSigner;
use Cornix\Serendipity\Core\Domain\Repository\ServerSignerRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\PrivateKey;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\ServerSignerTable;

class WpServerSignerRepository implements ServerSignerRepository {

	private ServerSignerTable $server_signer_table;

	public function __construct( ServerSignerTable $server_signer_table ) {
		$this->server_signer_table = $server_signer_table;
	}

	private function decodeKeyFromBase64( string $base64_key ): PrivateKey {
		$decoded = base64_decode( $base64_key, true );
		if ( $decoded === false ) {
			throw new \RuntimeException( '[3E4C4478] Failed to decode base64 private key.' );
		}
		return PrivateKey::from( $decoded );
	}

	/** 署名用ウォレットを取得します */
	public function get(): ServerSigner {
		$record = $this->server_signer_table->get();

		if ( $record === null ) {
			// プラグイン初期化時に登録済みのためここは通らない
			throw new \RuntimeException( '[1E71C0A4] server signer is not registered.' );
		}

		return new ServerSigner(
			Address::from( $record->addressValue() ),
			$this->decodeKeyFromBase64( $record->base64KeyValue() )
		);
	}
}
