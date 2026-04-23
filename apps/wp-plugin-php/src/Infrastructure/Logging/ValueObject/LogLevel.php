<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject;

/** ログレベルを表すクラス */
class LogLevel {

	// ログレベルの定義
	// 値が大きいほど詳細なログを表す
	private const NONE  = 'none';
	private const ERROR = 'error';
	private const WARN  = 'warn';
	private const INFO  = 'info';
	private const DEBUG = 'debug';

	private static array $levels = array( self::NONE, self::ERROR, self::WARN, self::INFO, self::DEBUG );

	private function __construct( string $log_level_value ) {
		$log_level_value = strtolower( $log_level_value );
		assert( in_array( $log_level_value, self::$levels, true ), "[CFB9AB28] Invalid log level value: {$log_level_value}" );
		$this->log_level_value = $log_level_value;
	}

	private string $log_level_value;

	public function name(): string {
		return $this->log_level_value;
	}

	public static function from( string $log_level_value ): self {
		return new self( $log_level_value );
	}

	/**
	 * このインスタンスのログレベル設定に基づき、指定されたログレベルのログを出力すべきかどうかを返します。
	 *
	 * 例: このインスタンスのログレベルがINFOの場合
	 *   - DEBUG => false（出力しない）
	 *   - ERROR => true（出力する）
	 *
	 * @param LogLevel $log_level 判定対象のログレベル
	 * @return bool 指定されたログレベルを出力すべき場合はtrue、そうでなければfalse
	 */
	public function allows( LogLevel $log_level ): bool {
		// $this->levelsのインデックスを取得
		$this_index = array_search( $this->log_level_value, self::$levels, true );
		assert( $this_index !== false, "[896D928D] {$this->log_level_value}" );
		$other_index = array_search( $log_level->log_level_value, self::$levels, true );
		assert( $other_index !== false, "[6C95E96D] {$log_level->log_level_value}" );

		return $other_index <= $this_index;
	}


	public static function debug(): self {
		return new self( self::DEBUG );
	}
	public static function info(): self {
		return new self( self::INFO );
	}
	public static function warn(): self {
		return new self( self::WARN );
	}
	public static function error(): self {
		return new self( self::ERROR );
	}
	public static function none(): self {
		return new self( self::NONE );
	}

	public function equals( LogLevel $other ): bool {
		return $this->log_level_value === $other->log_level_value;
	}

	public function __toString(): string {
		return $this->name();
	}
}
