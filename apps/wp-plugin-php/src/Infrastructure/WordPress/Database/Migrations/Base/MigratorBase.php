<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base;

use DI\Container;
use RuntimeException;
use Throwable;
use wpdb;

/**
 * 特定のテーブルでのマイグレーションを行う基底クラス
 */
abstract class MigratorBase {
	protected function __construct( wpdb $wpdb, string $table_name ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name;

		$this->checkVersionsOrder();
	}
	private wpdb $wpdb;
	private string $table_name;
	private Container $container;

	public function setContainer( Container $container ): void {
		$this->container = $container;
	}

	/**
	 * テーブル更新対象のバージョンと使用するクラスのマッピングを定義します。
	 *
	 * @return array<int,array{0:string,1:class-string}> キーがバージョン番号、値がクラス名の連想配列
	 */
	abstract protected function versions(): array;

	private function createMigration( string $class_name ): MigrationBase {
		$instance = $this->container->get( $class_name );
		assert( $instance instanceof MigrationBase, "[1E79EFED] Invalid class: {$class_name}" );

		$instance->initialize( $this->wpdb, $this->table_name );
		return $instance;
	}

	/**
	 * マイグレーションを実行します。
	 *
	 * @param null|string $from_version マイグレーション前にインストールされていたプラグインバージョン
	 * @return void
	 */
	final public function up( ?string $from_version, string $to_version ): void {
		$from_version = $from_version ?? '0.0.0';
		assert( version_compare( $from_version, $to_version, '<' ), "[DA8A81F2] from: {$from_version}, to: {$to_version}" );

		try {
			foreach ( $this->versions() as $data ) {
				$version    = $data[0];
				$class_name = $data[1];

				if ( version_compare( $version, $from_version, '<=' ) || version_compare( $version, $to_version, '>' ) ) {
					break; // 対象外のバージョンの場合はスキップ($from_versionは前回のマイグレーションで実行済み)
				}
				// マイグレーションを実行
				$this->createMigration( $class_name )->up();
			}
		} catch ( Throwable $e ) {
			$this->down( $version, $from_version );
			throw $e; // 元の例外を再スロー
		}
	}

	/**
	 * マイグレーションを元に戻します。from_versionからto_versionまでのマイグレーションを実行します。
	 *
	 * ※ from_versionの方が大きいことに注意してください。
	 *
	 * @param string      $from_version
	 * @param null|string $to_version
	 * @return void
	 */
	final public function down( string $from_version, ?string $to_version ): void {
		$to_version = $to_version ?? '0.0.0';
		assert( version_compare( $from_version, $to_version, '>' ), "[6633CD2C] from: {$from_version}, to: {$to_version}" );

		$versions = $this->versions();
		for ( $i = count( $versions ) - 1; $i >= 0; $i-- ) { // 逆順で処理
			$version    = $versions[ $i ][0];
			$class_name = $versions[ $i ][1];
			if ( version_compare( $version, $from_version, '>' ) || version_compare( $version, $to_version, '<=' ) ) {
				break; // 対象外のバージョンの場合はスキップ
			}

			try {
				// ロールバックを実行
				$this->createMigration( $class_name )->down();
			} catch ( Throwable $e ) {
				error_log( "[7D3F30CA] Migration down failed for version '{$version}': {$e}" );
				// ここで再スローはしない
			}
		}
	}

	/**
	 * versions()で定義されたバージョンの順序が正しいかをチェックします。
	 */
	private function checkVersionsOrder(): void {
		$versions     = $this->versions();
		$last_version = '0.0.0';

		foreach ( $versions as $data ) {
			$current_version = $data[0];
			if ( ! version_compare( $last_version, $current_version, '<' ) ) {
				throw new RuntimeException( "[DE446372] Migration versions must be in ascending order: '{$current_version}', '{$last_version}'" );
			}
			$last_version = $current_version;
		}
	}
}
