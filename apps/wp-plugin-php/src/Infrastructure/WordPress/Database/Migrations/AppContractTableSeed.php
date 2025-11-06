<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\ChainIdConstants;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use wpdb;


class AppContractTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->appContract() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', AppContractTableSeed_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

abstract class AppContractTableSeedBase extends MigrationBase {
	protected function add( int $chain_id_value, string $address_value ): void {
		$chain_id = ChainId::from( $chain_id_value );
		$address  = Address::from( $address_value );
		$this->insert(
			$this->tableName(),
			array(
				'chain_id'                        => $chain_id->value(),
				'address'                         => $address->value(),
				'crawled_block_number'            => null,
				'crawled_block_number_updated_at' => null,
			)
		);
	}
}

/** @internal */
class AppContractTableSeed_0_0_1 extends AppContractTableSeedBase {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}
	private Environment $environment;

	public function up(): void {

		// TODO: メインネットのコントラクトアドレスを登録

		if ( $this->environment->isProduction() ) {
			// TODO: 販売者が動作確認用で使うテストネットのコントラクトアドレスを登録
			// 開発者以外も使うため、影響範囲が大きい
		} elseif ( $this->environment->isDevelopment() || $this->environment->isTesting() ) {
			// 開発用のコントラクトアドレスを登録

			// プライベートネット
			$this->add( ChainIdConstants::PRIVATENET1, '0x5FbDB2315678afecb367f032d93F642f64180aa3' );
			$this->add( ChainIdConstants::PRIVATENET2, '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512' );

			// テストネット
			// このコントラクトを使うのは開発者だけなので影響は限定的
			$this->add( ChainIdConstants::SEPOLIA, '0x65fA00d60343da7AB6Ff1f805eCAE452da758Fa0' );
		} else {
			throw new \RuntimeException( '[63F60D82] Unsupported environment' );
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
