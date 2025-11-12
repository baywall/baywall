<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

/**
 * wp_options テーブルのオプションを操作するサービスクラス
 */
class WordPressOptionService {

	/**
	 * Updates the value of an option that was already added.
	 *
	 * @param string $option
	 * @param mixed  $value
	 * @param bool   $autoload
	 * @return void
	 */
	public function update( string $option, $value, bool $autoload ): void {
		// update_optionはオプションの値が更新された時にtrueを返すが、同じ値で変更が無かった場合にfalseを返す。
		// 値がデータベースに登録されているかどうかを戻り値で判定できないため戻り値は無し。
		update_option( $option, $value, $autoload );
	}

	/**
	 * 指定したオプションを削除する
	 *
	 * @param string $option
	 * @return void
	 */
	public function delete( string $option ): void {
		// delete_optionは値を削除したときにtrueを返すが、そもそも値(キー)が存在しない場合にはfalseを返す。
		// 使いづらいので、このメソッドの戻り値は無し。
		delete_option( $option );
	}

	/**
	 * Retrieves an option value based on an option name.
	 *
	 * @param string $option
	 * @param mixed  $default_value
	 * @return mixed
	 */
	public function get( string $option, $default_value = \false ) {
		return get_option( $option, $default_value );
	}
}
