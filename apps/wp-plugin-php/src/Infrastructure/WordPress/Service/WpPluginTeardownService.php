<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Repository\PurgeOnUninstallRepository;
use Cornix\Serendipity\Core\Application\Service\PluginTeardownService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpOptionName;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpTableCoreName;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpTransientName;
use wpdb;

/** WordPressからプラグインが削除される際のクリーンアップ処理を行うクラス */
class WpPluginTeardownService implements PluginTeardownService {

	private wpdb $wpdb;
	private PurgeOnUninstallRepository $purge_on_uninstall_repository;

	public function __construct( wpdb $wpdb, PurgeOnUninstallRepository $purge_on_uninstall_repository ) {
		$this->wpdb                          = $wpdb;
		$this->purge_on_uninstall_repository = $purge_on_uninstall_repository;
	}

	public function teardown(): void {
		if ( ! $this->purge_on_uninstall_repository->get() ) {
			return; // データ完全削除オプションが無効な場合は何もしない
		}

		// トランジェント（キャッシュ）を削除
		$this->purgeTransients();
		// テーブルを削除
		$this->purgeTables();
		// オプションを削除
		$this->purgeOptions();
	}

	/** 本プラグインが作成したテーブルをすべて削除します */
	private function purgeTables(): void {
		$table_core_names = $this->getConstValues( WpTableCoreName::class );
		foreach ( $table_core_names as $table_core_name ) {
			if ( WpTableCoreName::PREFIX === $table_core_name ) {
				continue; // 念のためプレフィックスのみのテーブルはスキップ（IF EXISTSなので基本的に問題ない）
			}
			$this->wpdb->query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}{$table_core_name};" );
		}
	}

	/** 本プラグインが作成したオプションをすべて削除します */
	private function purgeOptions(): void {
		$option_names = $this->getConstValues( WpOptionName::class );
		foreach ( $option_names as $option_name ) {
			if ( WpOptionName::PREFIX === $option_name ) {
				continue; // 念のためプレフィックスだけのオプションはスキップ
			}
			delete_option( $option_name );
		}
	}

	/** 本プラグインが作成したトランジェント（キャッシュ）をすべて削除します */
	private function purgeTransients(): void {
		// optionsテーブルから、プレフィックスにマッチするトランジェント名をすべて取得
		$transient_names = $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT option_name FROM {$this->wpdb->options} WHERE option_name LIKE %s",
				$this->wpdb->esc_like( '_transient_' . WpTransientName::PREFIX ) . '%'
			)
		);

		foreach ( $transient_names as $transient_name ) {
			// テーブルから直接削除せず、delete_transient経由で削除する
			delete_transient( str_replace( '_transient_', '', $transient_name ) );
		}
	}

	/**
	 * 指定したクラスのpublic constで定義されている値をすべて取得します
	 *
	 * @param string $class_name
	 * @return string[]
	 */
	private function getConstValues( string $class_name ): array {
		$reflection_class = new \ReflectionClass( $class_name );
		$constants        = $reflection_class->getReflectionConstants();
		$values           = array();

		foreach ( $constants as $constant ) {
			if ( ! $constant->isPublic() ) {
				continue;
			}
			$values[] = $constant->getValue();
		}

		return $values;
	}
}
