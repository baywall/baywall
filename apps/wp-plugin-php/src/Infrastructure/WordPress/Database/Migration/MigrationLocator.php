<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration;

use Cornix\Serendipity\Core\Infrastructure\Util\NamespaceParser;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\PluginVersion;
use Psr\Container\ContainerInterface;

/** マイグレーション対象となるクラスインスタンス一覧を取得します */
class MigrationLocator {

	const MIGRATIONS_DIR = __DIR__ . '/Migrations/';

	private ContainerInterface $container;
	private NamespaceParser $namespace_parser;

	public function __construct( ContainerInterface $container ) {
		$this->container        = $container;
		$this->namespace_parser = $container->get( NamespaceParser::class );
	}

	/**
	 * 指定したバージョンの範囲にあるマイグレーションクラスインスタンスを取得します
	 *
	 * @param PluginVersion $gt_version このバージョンより大きいバージョンが対象
	 * @param PluginVersion $le_version このバージョン以下が対象
	 * @return MigrationBase[]
	 */
	public function get( PluginVersion $gt_version, PluginVersion $le_version ): array {

		$files = scandir( self::MIGRATIONS_DIR, SCANDIR_SORT_ASCENDING );
		assert( is_array( $files ), '[CF0D36ED] Failed to scan migrations directory' );
		// .phpで終わるファイルのみ抽出
		$php_files = array_filter( $files, fn( string $file ): bool => str_ends_with( $file, '.php' ) );
		/** @var MigrationBase[] */
		$migrations = array();

		foreach ( $php_files as $file ) {
			$file_path  = self::MIGRATIONS_DIR . $file;
			$namespace  = $this->namespace_parser->get( $file_path );
			$class_name = $namespace . '\\' . pathinfo( $file, PATHINFO_FILENAME );

			$instance = $this->container->get( $class_name );
			assert( $instance instanceof MigrationBase, "[DD850FCA] Invalid class: {$class_name}" );
			$version = PluginVersion::from( $instance->version() );
			if ( $gt_version->compareOperator( $version, '<' ) && $version->compareOperator( $le_version, '<=' ) ) {
				$migrations[] = $instance;
			}
		}

		return $migrations;
	}
}
