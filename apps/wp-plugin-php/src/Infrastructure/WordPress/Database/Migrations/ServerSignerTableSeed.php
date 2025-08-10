<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Application\Service\ServerSignerService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\ServerSignerPrivateKeyRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use RuntimeException;
use wpdb;


class ServerSignerTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->serverSigner() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', ServerSignerTableSeed_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class ServerSignerTableSeed_0_0_1 extends MigrationBase {

	public function __construct( ServerSignerService $server_signer_service, ServerSignerPrivateKeyRepository $repository ) {
		$this->server_signer_service = $server_signer_service;
		$this->repository            = $repository;
	}
	private ServerSignerService $server_signer_service;
	private ServerSignerPrivateKeyRepository $repository;

	public function up(): void {
		if ( $this->repository->address() !== null ) {
			throw new RuntimeException( '[A8F10C57] Server signer table already has data. Cannot initialize.' );
		}

		$server_signer_data = $this->server_signer_service->generateServerSignerData();
		$this->repository->save(
			$server_signer_data->address(),
			$server_signer_data->privateKeyData(),
			$server_signer_data->encryptionKey(),
			$server_signer_data->encryptionIv()
		);
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
