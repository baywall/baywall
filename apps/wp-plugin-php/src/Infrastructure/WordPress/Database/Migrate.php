<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\AppContractTableSchema;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\AppContractTableSeed;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\ChainTableSchema;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\ChainTableSeed;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\TokenTableSchema;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\TokenTableSeed;
use DI\Container;
use Throwable;

class Migrate {
	public function __construct( Container $container ) {
		$this->container = $container;
	}
	private Container $container;

	/** @return MigratorBase[] */
	public function schema( ?string $from_version, string $to_version ): array {
		return $this->migrate( $this->schemaClasses(), $from_version, $to_version );
	}

	/** @return MigratorBase[] */
	public function seed( ?string $from_version, string $to_version ): array {
		return $this->migrate( $this->seedClasses(), $from_version, $to_version );
	}

	/**
	 * マイグレーションで使用するスキーマクラスのリストを取得します。
	 *
	 * @return class-string<MigratorBase>[]
	 */
	private function schemaClasses(): array {
		return array(
			AppContractTableSchema::class,
			ChainTableSchema::class,
			TokenTableSchema::class,
			// 他のスキーマクラスを追加する場合はここに記述
		);
	}
	private function seedClasses(): array {
		return array(
			AppContractTableSeed::class,
			ChainTableSeed::class,
			TokenTableSeed::class,
			// 他のシードクラスを追加する場合はここに記述
		);
	}


	/**
	 * 指定されたクラスを使ってマイグレーションを実行します。
	 *
	 * @param string[]    $migrator_classes
	 * @param null|string $from_version
	 * @param string      $to_version
	 * @return MigratorBase[]
	 */
	private function migrate( array $migrator_classes, ?string $from_version, string $to_version ): array {
		/** @var MigratorBase[] */
		$exec_migrators = array();
		try {
			foreach ( $migrator_classes as $migrator_class ) {
				/** @var MigratorBase */
				$migrator = $this->container->get( $migrator_class );
				$migrator->setContainer( $this->container );
				$migrator->up( $from_version, $to_version );
				array_unshift( $exec_migrators, $migrator );
			}
			return $exec_migrators; // 成功した場合は実行したマイグレーターのリストを返す
		} catch ( Throwable $e ) {
			foreach ( $exec_migrators as $migrator ) {
				$migrator->down( $to_version, $from_version );
			}
			throw $e;   // 再スロー
		}
	}

	public function run( ?string $from_version, string $to_version ) {
		assert( version_compare( $from_version ?? '0.0.0', $to_version, '<' ), "[0C5539FE] from: {$from_version}, to: {$to_version}" );

		// スキーマの更新が正常に終了後、初期値代入を実行したときにエラーとなった場合、スキーマも戻す必要があるため
		// スキーマを更新したオブジェクトのリストを取得しておく
		$exec_schema_migrators = $this->schema( $from_version, $to_version );  // テーブル構造の更新
		try {
			$this->seed( $from_version, $to_version );      // 初期値代入
		} catch ( Throwable $e ) {
			foreach ( $exec_schema_migrators as $migrator ) {
				$migrator->down( $to_version, $from_version );
			}
			throw $e;   // 再スロー
		}
	}
}
