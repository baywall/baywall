<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\OptionGateway\Option;

use Baywall\Core\Infrastructure\Util\Strings;
use Baywall\Core\Infrastructure\WordPress\Constants\WpOptionName;

class Option {
	public function __construct( string $option_key_name ) {
		assert( 0 === Strings::strpos( $option_key_name, WpOptionName::PREFIX ) );
		$this->option_key_name = $option_key_name;
	}
	private string $option_key_name;

	public function get( $default = false ) {
		return get_option( $this->option_key_name, $default );
	}

	/**
	 * 値を更新します
	 *
	 * @param mixed     $value
	 * @param null|bool $autoload
	 */
	public function update( $value, ?bool $autoload = null ): void {
		update_option( $this->option_key_name, $value, $autoload );
	}

	/**
	 * 値を削除します
	 */
	public function delete(): void {
		delete_option( $this->option_key_name );
	}
}
