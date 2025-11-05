<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\PaidContent;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\PaidContentTableRecord;

/**
 * 有料記事の情報を記録するテーブル
 */
class PaidContentTable extends TableBase {

	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->paidContent() );
	}

	/**
	 * @return null|PaidContentTableRecord
	 */
	public function select( PostId $post_id ) {
		$sql = <<<SQL
			SELECT `post_id`, `paid_content`, `selling_network_category_id`, `selling_amount`, `selling_symbol`
			FROM `{$this->tableName()}`
			WHERE `post_id` = %d
		SQL;

		$sql = $this->prepare( $sql, $post_id->value() );

		$record = $this->safeGetRow( $sql );

		return $record === null ? null : new PaidContentTableRecord( $record );
	}

	public function set( PostId $post_id, ?PaidContent $paid_content, ?NetworkCategoryId $selling_network_category_id, ?Amount $selling_amount, ?Symbol $selling_symbol ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}` (
				`post_id`,
				`paid_content`,
				`selling_network_category_id`,
				`selling_amount`,
				`selling_symbol`
			) VALUES (
				:post_id, :paid_content, :selling_network_category_id, :selling_amount, :selling_symbol
			) ON DUPLICATE KEY UPDATE
				`paid_content` = :paid_content,
				`selling_network_category_id` = :selling_network_category_id,
				`selling_amount` = :selling_amount,
				`selling_symbol` = :selling_symbol
		SQL;

		$sql = $this->namedPrepare(
			$sql,
			array(
				':post_id'                     => $post_id->value(),
				':paid_content'                => $paid_content ? $paid_content->value() : null,
				':selling_network_category_id' => $selling_network_category_id ? $selling_network_category_id->value() : null,
				':selling_amount'              => $selling_amount ? $selling_amount->value() : null,
				':selling_symbol'              => $selling_symbol ? $selling_symbol->value() : null,
			)
		);

		$result = $this->safeQuery( $sql );
		assert( $result <= 2, "[DBB26475] Failed to set paid content data. - post_id: {$post_id}, result: {$result}" );
	}

	public function delete( PostId $post_id ): void {
		$sql = <<<SQL
			DELETE FROM `{$this->tableName()}` WHERE `post_id` = %d
		SQL;

		$sql    = $this->prepare( $sql, $post_id->value() );
		$result = $this->safeQuery( $sql );
		assert( $result <= 1, "[64CF23D9] Failed to delete paid content data. - post_id: {$post_id}, result: {$result}" );
	}

	/**
	 * テーブルが存在するかどうかを取得します。
	 */
	public function exists(): bool {
		return (bool) $this->safeGetVar( "SHOW TABLES LIKE '{$this->tableName()}'" );
	}
}
