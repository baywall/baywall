<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation;

use Cornix\Serendipity\Core\Features\Page\PhpVer;
use Cornix\Serendipity\Core\Lib\Path\ProjectFile;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\HandleNameProvider;

/**
 * 投稿編集画面のフック(投稿新規作成画面を含む)
 */
class PostEditHook {

	private HandleNameProvider $handle_name_provider;

	public function __construct( HandleNameProvider $handle_name_provider ) {
		$this->handle_name_provider = $handle_name_provider;
	}

	// ブロックスクリプトの出力先ディレクトリ
	private const DIST_DIR = 'build/block';

	public function register(): void {
		add_action( 'enqueue_block_assets', array( $this, 'addActionEnqueueBlockAssets' ) );
	}

	public function addActionEnqueueBlockAssets(): void {
		// `enqueue_block_assets`は、エディタ画面、フロント画面の両方で呼ばれる。
		// ここでは編集画面に限定するため、`! is_admin()`の時は処理抜け。
		if ( ! is_admin() ) {
			return;
		}

		// ブロックエディタで使用するスクリプトを登録するときのハンドル名を取得。
		$handle = $this->handle_name_provider->blockScript();

		// アセットファイルを読み込む。
		$asset_file_path = ( new ProjectFile( self::DIST_DIR . '/index.asset.php' ) )->toLocalPath();
		$asset_file      = include $asset_file_path;

		// ブロックスクリプトを登録
		wp_enqueue_script(
			$handle,
			( new ProjectFile( self::DIST_DIR . '/index.js' ) )->toUrl(),
			$asset_file['dependencies'],
			$asset_file['version'],
			true,   // フッターに出力。
		);

		// インラインスクリプトを追加
		( new PhpVer() )->addInlineScript( $handle );
	}
}
