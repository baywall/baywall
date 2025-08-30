<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Decimals;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\ChainIdConstants;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use wpdb;


class TokenTableSeed extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->token() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', TokenTableSeed_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
abstract class TokenTableSeedBase extends MigrationBase {
	protected function add( ChainId $chain_id, string $address_value, string $symbol_value, int $decimals_value, bool $is_payable ): void {
		$address  = Address::from( $address_value );
		$symbol   = Symbol::from( $symbol_value );
		$decimals = Decimals::from( $decimals_value );

		$this->insert(
			$this->tableName(),
			array(
				'chain_id'   => $chain_id->value(),
				'address'    => $address->value(),
				'symbol'     => $symbol->value(),
				'decimals'   => $decimals->value(),
				'is_payable' => (int) $is_payable,
			)
		);
	}
}

/** @internal */
class TokenTableSeed_0_0_1 extends TokenTableSeedBase {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}
	private Environment $environment;

	public function up(): void {
		// ネイティブトークンのアドレスは0x00とする
		$zero_address = Ethers::zeroAddress()->value();

		// メインネットのネイティブトークンを登録(Ethereum Mainnetのみ支払可能として指定)
		$this->add( ChainIdConstants::ethMainnet(), $zero_address, 'ETH', 18, true );

		// テストネットのネイティブトークンを登録(Sepoliaのみ支払可能として指定)
		$this->add( ChainIdConstants::sepolia(), $zero_address, 'ETH', 18, true );
		$this->add( ChainIdConstants::soneiumMinato(), $zero_address, 'ETH', 18, false );

		// 開発モード時はプライベートネットのネイティブトークンを登録
		if ( $this->environment->isDevelopment() ) {
			$this->add( ChainIdConstants::privatenetL1(), $zero_address, 'ETH', 18, true );
			$this->add( ChainIdConstants::privatenetL2(), $zero_address, 'MATIC', 18, false );
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
