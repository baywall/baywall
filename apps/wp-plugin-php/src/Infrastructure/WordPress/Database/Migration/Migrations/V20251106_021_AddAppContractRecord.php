<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\ChainIdConstants;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_021_AddAppContractRecord extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;
	private Environment $environment;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider, Environment $environment ) {
		$this->wpdb        = $wpdb;
		$this->table_name  = $table_name_provider->appContract();
		$this->environment = $environment;
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		if ( $this->environment->isProduction() ) {
			// TODO: 販売者が動作確認用で使うテストネットのコントラクトアドレスを登録
			// 開発者以外も使うため、影響範囲が大きい
		} elseif ( $this->environment->isDevelopment() || $this->environment->isTesting() ) {
			// 開発用のコントラクトアドレスを登録

			// プライベートネット
			$this->insert( ChainIdConstants::PRIVATENET1, '0x5FbDB2315678afecb367f032d93F642f64180aa3' );
			$this->insert( ChainIdConstants::PRIVATENET2, '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512' );

			// テストネット
			// このコントラクトを使うのは開発者だけなので影響は限定的
			$this->insert( ChainIdConstants::SEPOLIA, '0x65fA00d60343da7AB6Ff1f805eCAE452da758Fa0' );
		} else {
			throw new \RuntimeException( '[63F60D82] Unsupported environment' );
		}
	}

	public function down(): void {
		$this->wpdb->dbh->query( "TRUNCATE TABLE `{$this->table_name}`;" );
	}

	private function insert( int $chain_id_value, string $address_value ): void {
		$this->wpdb->insert(
			$this->table_name,
			array(
				'chain_id'                        => ChainId::from( $chain_id_value )->value(),
				'address'                         => Address::from( $address_value )->value(),
				'crawled_block_number'            => null,
				'crawled_block_number_updated_at' => null,
			)
		);
	}
}
