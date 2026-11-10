<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration;

use Baywall\Core\Infrastructure\Util\NamespaceParser;
use Baywall\Core\Application\ValueObject\PluginVersion;

/** マイグレーション対象となるクラスインスタンス一覧を取得します */
class MigrationLocator {

	const MIGRATIONS_DIR = __DIR__ . '/Migrations/';

	private readonly NamespaceParser $namespace_parser;
	private readonly \Closure $migration_factory;

	/**
	 * @param \Closure(class-string<Migration>): Migration $migration_factory クラス名からマイグレーションインスタンスを解決するファクトリ
	 */
	public function __construct( NamespaceParser $namespace_parser, \Closure $migration_factory ) {
		$this->namespace_parser  = $namespace_parser;
		$this->migration_factory = $migration_factory;
	}

	/**
	 * 指定したバージョンの範囲にあるマイグレーションクラスインスタンスを取得します
	 *
	 * @param PluginVersion $gt_version このバージョンより大きいバージョンが対象
	 * @param PluginVersion $le_version このバージョン以下が対象
	 * @return Migration[]
	 */
	public function get( PluginVersion $gt_version, PluginVersion $le_version ): array {

		$files = scandir( self::MIGRATIONS_DIR, SCANDIR_SORT_ASCENDING );
		assert( is_array( $files ), '[CF0D36ED] Failed to scan migrations directory' );
		// .phpで終わるファイルのみ抽出
		$php_files = array_filter( $files, fn( string $file ): bool => str_ends_with( $file, '.php' ) );
		/** @var Migration[] */
		$migrations = array();

		foreach ( $php_files as $file ) {
			$file_path  = self::MIGRATIONS_DIR . $file;
			$namespace  = $this->namespace_parser->get( $file_path );
			$class_name = $namespace . '\\' . pathinfo( $file, PATHINFO_FILENAME );

			if ( ! is_a( $class_name, Migration::class, true ) ) {
				throw new \RuntimeException( "[3070BE52] Migration class does not implement the Migration interface: {$class_name}" );
			}

			$instance = ( $this->migration_factory )( $class_name );
			$version  = PluginVersion::from( $instance->version() );
			if ( $gt_version->compareOperator( $version, '<' ) && $version->compareOperator( $le_version, '<=' ) ) {
				$migrations[] = $instance;
			}
		}

		return $migrations;
	}
}
