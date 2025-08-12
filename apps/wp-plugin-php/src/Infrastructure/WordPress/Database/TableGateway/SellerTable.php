<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\SellerTableRecord;
use stdClass;

/**
 * 販売者情報を記録するテーブル
 */
class SellerTable extends TableBase {

	public function __construct( \wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->seller() );
	}

	/**
	 * テーブルに保存されている販売者一覧を取得します。
	 *
	 * @return SellerTableRecord[]
	 */
	public function all(): array {
		$sql = <<<SQL
			SELECT `seller_address`, `agreed_terms_version`, `signing_message`, `signature`
			FROM `{$this->tableName()}`
		SQL;

		$result = $this->safeGetResults( $sql );

		return array_map(
			function ( stdClass $row ) {
				// 型をテーブル定義に一致させる
				$row->seller_address       = (string) $row->seller_address;
				$row->agreed_terms_version = (int) $row->agreed_terms_version;
				$row->signing_message      = (string) $row->signing_message;
				$row->signature            = (string) $row->signature;

				return new SellerTableRecord( $row );
			},
			$result
		);
	}

	/**
	 * 販売者情報を追加します。
	 */
	public function add( Address $seller_address, TermsVersion $agreed_terms_version, SigningMessage $signing_message, Signature $signature ): void {
		$result = $this->wpdb()->insert(
			$this->tableName(),
			array(
				'seller_address'       => $seller_address->value(),
				'agreed_terms_version' => $agreed_terms_version->value(),
				'signing_message'      => $signing_message->value(),
				'signature'            => $signature->value(),
			)
		);
		if ( false === $result ) {
			throw new \Exception( '[67CC141B] Failed to add seller data.' );
		}
	}

	public function delete( Address $seller_address ): void {
		$result = $this->wpdb()->delete(
			$this->tableName(),
			array( 'seller_address' => $seller_address->value() )
		);
		if ( false === $result ) {
			throw new \Exception( '[8D9EB76D] Failed to delete seller data.' );
		}
	}
}
