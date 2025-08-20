<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Record\Base;

use stdClass;

abstract class RecordBase {
	/**
	 * このインスタンスのプロパティをレコードの値で初期化します。
	 * ※ フィールドにアクセスできるように子クラスのフィールドはpublicまたはprotectedで定義してください。
	 *
	 * @param stdClass $record テーブルから取得したレコード
	 */
	protected function import( stdClass $record ) {
		foreach ( $record as $property => $value ) {
			assert( property_exists( $this, $property ), '[F72ED81D] Invalid property: ' . $property );
			$this->{$property} = $value;
		}
	}
}
