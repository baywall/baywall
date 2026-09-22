<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Signature;
use Baywall\Core\Domain\ValueObject\SigningMessage;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Baywall\Core\Infrastructure\WordPress\Database\Record\SellerTableRecord;
use stdClass;

/**
 * 販売者情報を記録するテーブル
 */
class SellerTable {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

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
			SELECT `seller_address`, `signing_message`, `signature`
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
	public function add( Address $seller_address, SigningMessage $signing_message, Signature $signature ): void {
		$now    = UnixTimestamp::now()->value();
		$result = $this->wpdb->insert(
			$this->table_name,
			array(
				'seller_address'  => $seller_address->value(),
				'signing_message' => $signing_message->value(),
				'signature'       => $signature->hex()->value(),
				'created_at'      => $now,
				'updated_at'      => $now,
			),
			array( '%s', '%s', '%s', '%d', '%d' )
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
