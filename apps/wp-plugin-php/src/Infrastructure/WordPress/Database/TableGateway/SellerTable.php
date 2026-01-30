<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\SellerTableRecord;
use stdClass;

/**
 * 販売者情報を記録するテーブル
 */
class SellerTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->seller();
	}

	/**
	 * テーブルに保存されている販売者一覧を取得します。
	 *
	 * @return SellerTableRecord[]
	 */
	public function all(): array {
		$sql = <<<SQL
			SELECT `seller_address`, `agreed_terms_version`, `signing_message`, `signature`
			FROM `{$this->table_name}`
		SQL;

		$result = $this->wpdb->get_results( $sql );

		return array_map(
			fn( stdClass $record ) => new SellerTableRecord( $record ),
			$result
		);
	}

	/**
	 * 販売者情報を追加します。
	 */
	public function add( Address $seller_address, TermsVersion $agreed_terms_version, SigningMessage $signing_message, Signature $signature ): void {
		$result = $this->wpdb->insert(
			$this->table_name,
			array(
				'seller_address'       => $seller_address->value(),
				'agreed_terms_version' => $agreed_terms_version->value(),
				'signing_message'      => $signing_message->value(),
				'signature'            => $signature->hex()->value(),
			)
		);
		assert( $result === 1, "[67195917] Failed to insert seller data. {$result}" );
	}

	public function delete( Address $seller_address ): int {
		$result = $this->wpdb->delete(
			$this->table_name,
			array( 'seller_address' => $seller_address->value() )
		);
		return $result;
	}
}
