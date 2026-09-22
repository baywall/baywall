<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\TableGateway;

use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\ValueObject\UnixTimestamp;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Baywall\Core\Infrastructure\WordPress\Database\Record\TokenTableRecord;
use stdClass;

/**
 * トークンの情報を記録するテーブル
 */
class TokenTable {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->token();
	}

	/**
	 * テーブルに保存されているトークンデータ一覧を取得します。
	 *
	 * @return TokenTableRecord[]
	 */
	public function all(): array {
		$sql = <<<SQL
			SELECT `chain_id`, `address`, `symbol`, `decimals`, `is_payable`
			FROM `{$this->table_name}`
		SQL;

		$result = $this->wpdb->get_results( $sql );

		return array_map(
			fn( stdClass $record ) => new TokenTableRecord( $record ),
			$result
		);
	}

	/**
	 * トークン情報を保存します。
	 */
	public function save( Token $token ): void {

		// データが存在する時はレコードの更新を行うが、symbol, decimalsの値は変更しない
		$now = UnixTimestamp::now()->value();
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`chain_id`, `address`, `symbol`, `decimals`, `is_payable`, `created_at`, `updated_at`)
			VALUES (:chain_id, :address, :symbol, :decimals, :is_payable, :created_at, :updated_at)
			ON DUPLICATE KEY UPDATE
				`is_payable` = :is_payable,
				`updated_at` = :updated_at
		SQL;

		$sql = $this->wpdb->named_prepare(
			$sql,
			array(
				':chain_id'   => $token->chainId()->value(),
				':address'    => $token->address()->value(),
				':symbol'     => $token->symbol()->value(),
				':decimals'   => $token->decimals()->value(),
				':is_payable' => $token->isPayable(),
				':created_at' => $now,
				':updated_at' => $now,
			)
		);

		$this->wpdb->query( $sql );
	}
}
