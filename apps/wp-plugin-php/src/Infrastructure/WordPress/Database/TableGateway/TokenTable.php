<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\TokenTableRecord;
use stdClass;

/**
 * トークンの情報を記録するテーブル
 */
class TokenTable extends TableBase {

	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->token() );
	}

	/**
	 * テーブルに保存されているトークンデータ一覧を取得します。
	 *
	 * @return TokenTableRecord[]
	 */
	public function all(): array {
		$sql = <<<SQL
			SELECT `chain_id`, `address`, `symbol`, `decimals`, `is_payable`
			FROM `{$this->tableName()}`
		SQL;

		$result = $this->safeGetResults( $sql );

		return array_map(
			function ( stdClass $row ) {
				// 型をテーブル定義に一致させる
				$row->chain_id   = (int) $row->chain_id;
				$row->address    = (string) $row->address;
				$row->symbol     = (string) $row->symbol;
				$row->decimals   = (int) $row->decimals;
				$row->is_payable = (bool) $row->is_payable;

				return new TokenTableRecord( $row );
			},
			$result
		);
	}

	/**
	 * トークン情報を保存します。
	 */
	public function save( Token $token ): void {

		// データが存在する時はレコードの更新を行うが、symbol, decimalsの値は変更しない
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			(`chain_id`, `address`, `symbol`, `decimals`, `is_payable`)
			VALUES (%d, %s, %s, %d, %d)
			ON DUPLICATE KEY UPDATE
				`is_payable` = %d
		SQL;

		$sql = $this->wpdb()->prepare(
			$sql,
			$token->chainId()->value(),
			$token->address()->value(),
			$token->symbol()->value(),
			$token->decimals()->value(),
			$token->isPayable(),
			$token->isPayable(),
		);

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \Exception( '[7217F4B3] Failed to add token data.' );
		}
	}
}
