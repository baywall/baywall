<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Decimals;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
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
	protected function add( int $chain_id_value, string $address_value, string $symbol_value, int $decimals_value, bool $is_payable ): void {
		$chain_id = ChainId::from( $chain_id_value );
		$address  = Address::from( $address_value );
		// ネイティブトークンのみ許可
		assert( $address->equals( Address::nativeToken() ), '[83181C3F] Native token address expected.' );
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
		// ネイティブトークンのアドレスを文字列で取得
		$native_token_address = Address::nativeToken()->value();

		// メインネットのネイティブトークンを登録
		$this->add( ChainIdConstants::ETHEREUM, $native_token_address, 'ETH', 18, false );

		// テストネットのネイティブトークンを登録
		$this->add( ChainIdConstants::SEPOLIA, $native_token_address, 'ETH', 18, false );

		// 開発モード時はプライベートネットのネイティブトークンを登録
		if ( $this->environment->isDevelopment() || $this->environment->isTesting() ) {
			$this->add( ChainIdConstants::PRIVATENET1, $native_token_address, 'ETH', 18, false );
			$this->add( ChainIdConstants::PRIVATENET2, $native_token_address, 'POL', 18, false );
		}
	}

	public function down(): void {
		$this->query( "TRUNCATE TABLE `{$this->tableName()}`;" );
	}
}
