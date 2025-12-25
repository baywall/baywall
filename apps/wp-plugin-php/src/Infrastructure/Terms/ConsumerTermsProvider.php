<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Terms;

use Cornix\Serendipity\Core\Domain\ValueObject\Bytes32;

/**
 * 本プラグインにおける購入者向け利用規約の情報を取得するためのクラス
 *
 * @deprecated 購入者向け利用規約本文のハッシュ値はクライアント側で計算するため、このクラスは不要
 * TODO: 削除
 */
class ConsumerTermsProvider {

	/**
	 * このプラグインに同梱されている購入者向け利用規約のバージョンを取得します。
	 */
	public function currentVersion(): int {
		// TODO: 購入者向け利用規約バージョン取得処理の実装
		error_log( '[230E2F1C] ConsumerTerms::version() - Not implemented yet' );
		return 1;
	}

	/** 購入者向け利用規約のハッシュ値を取得します */
	public function getTextHash(): Bytes32 {
		// TODO: 必要な場合は購入者向け利用規約本文のハッシュ値を取得して返す
		// （将来的に削除されるため、現時点では仮の値を返している）
		return Bytes32::from( '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef' ); // 仮の値を返す
	}
}
