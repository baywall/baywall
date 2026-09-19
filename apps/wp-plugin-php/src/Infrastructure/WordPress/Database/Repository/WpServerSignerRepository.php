<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Domain\Entity\ServerSigner;
use Baywall\Core\Domain\Repository\ServerSignerRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\PrivateKey;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\ServerSignerTable;

class WpServerSignerRepository implements ServerSignerRepository {

	private ServerSignerTable $server_signer_table;

	public function __construct( ServerSignerTable $server_signer_table ) {
		$this->server_signer_table = $server_signer_table;
	}

	/**
	 * 平文のBase64で保存された秘密鍵を復号します
	 *
	 * `private_key`の値域はCHECK制約で88文字のBase64に固定されているため、CHECK制約が執行されるDBでは
	 * `base64_decode()`は失敗しません（CHECK制約が執行されないDBへのfail-safeとして分岐を残しています）
	 */
	private function decodePlainBase64Key( string $stored_value ): PrivateKey {
		$decoded = base64_decode( $stored_value, true );
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
			$this->decodePlainBase64Key( $record->privateKeyValue() )
		);
	}
}
