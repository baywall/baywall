<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Terms;

use Cornix\Serendipity\Core\Domain\ValueObject\Bytes32;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;

/**
 * 本プラグインにおける購入者向け利用規約の情報を取得するためのクラス
 * TODO: testsディレクトリへ移動
 */
class CustomerTermsProvider {

	/**
	 * このプラグインに同梱されている購入者向け利用規約のバージョンを取得します。
	 *
	 * @deprecated to be removed
	 */
	public function currentVersion(): int {
		// TODO: 購入者向け利用規約バージョン取得処理の実装
		error_log( '[230E2F1C] CustomerTerms::version() - Not implemented yet' );
		return 1;
	}

	/** 購入者向け利用規約のハッシュ値を取得します */
	public function getTextHash(): Bytes32 {
		// TODO: 必要な場合は購入者向け利用規約本文のハッシュ値を取得して返す
		// （将来的に削除されるため、現時点では仮の値を返している）
		return Bytes32::fromHex( Hex::from( '0x0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef' ) ); // 仮の値を返す
	}
}
