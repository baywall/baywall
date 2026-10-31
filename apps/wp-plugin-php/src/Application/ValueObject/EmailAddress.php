<?php
declare(strict_types=1);

namespace Baywall\Core\Application\ValueObject;

use Baywall\Core\Domain\ValueObject\Base\StringValueObject;

/**
 * メールアドレスを表すValueObjectクラス
 */
readonly class EmailAddress extends StringValueObject {

	/**
	 * メールアドレス形式の正規表現（出典: HTML Standard「valid e-mail address」 https://html.spec.whatwg.org/multipage/input.html#valid-e-mail-address 。local-part の文字は RFC 5322 §3.2.3 の atext）
	 *
	 * 出典からの差分は 2 点のみ。
	 * - アンカーを ^ / $ から \A / \z に置換: PCRE の $ は末尾改行の直前にマッチし admin@example.com\n を有効にして JS と判定がずれるため
	 * - ' を \' にエスケープ: PHP の文字列リテラル構文上必要なだけで、パターンの差分ではない
	 *
	 * 判定レイヤの役割（FILTER_VALIDATE_EMAIL との積集合）: quoted-string・IP リテラル・ラベル先頭末尾ハイフン等の除外はこの正規表現が、
	 * ドット位置不正・ドット無しドメイン・数値 TLD・ローカル部 65 文字以上等の除外は FILTER_VALIDATE_EMAIL 側が担う
	 */
	private const VALID_EMAIL_PATTERN = '/\A[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*\z/';

	private function __construct( string $email_value ) {
		parent::__construct( $email_value );
		self::checkValidEmailFormat( $email_value );
	}

	public static function from( string $email_value ): self {
		return new self( $email_value );
	}

	private static function checkValidEmailFormat( string $email_value ): void {
		// Application層にWordPress関数の依存を持ち込まないため、PHP純正のfilter_varで判定する。
		// filter_varはquoted-stringやIPリテラルなど実運用で送れない形式も通すため、正規表現との積集合で判定する
		$is_valid = filter_var( $email_value, FILTER_VALIDATE_EMAIL ) !== false
			&& 1 === preg_match( self::VALID_EMAIL_PATTERN, $email_value );
		if ( ! $is_valid ) {
			throw new \InvalidArgumentException( '[019A003D] Invalid email address format. ' . $email_value );
		}
	}
}
