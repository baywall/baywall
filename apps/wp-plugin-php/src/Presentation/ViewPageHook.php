<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation;

use Cornix\Serendipity\Core\Features\Page\PhpVer;
use Cornix\Serendipity\Core\Lib\Path\ProjectFile;
use Cornix\Serendipity\Core\Repository\Name\HandleName;

class ViewPageHook {
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueViewScripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'addFilterScriptLoaderTag' ), 10, 3 );
	}

	public function enqueueViewScripts(): void {
		if ( is_admin() ) {
			return;
		}

		// ゲストユーザー(一般の訪問者)表示用の登録する際のハンドル名を取得
		$handle_name = ( new HandleName() )->viewScript();

		// アセットファイルを読み込む
		$asset_file_path = ( new ProjectFile( 'public/view/index.asset.php' ) )->toLocalPath();
		$asset_file      = include $asset_file_path;

		// スクリプトを登録
		wp_enqueue_script(
			$handle_name,
			( new ProjectFile( 'public/view/index.js' ) )->toUrl(),
			$asset_file['dependencies'],
			$asset_file['version'],
			true   // フッターに出力 ※ 6.8.2でも`script_loader_tag`による`defer`挿入が可能であることを確認したため、配列にはせず`true`のままとする
		);
		// インラインスクリプトを追加
		( new PhpVer() )->addInlineScript( $handle_name );

		// スタイルを登録
		wp_enqueue_style(
			'5bcfda3bcb3a77e70732c9e6e78195a5', // 適当なハンドル名(他で使用しない)
			( new ProjectFile( 'public/view/index.css' ) )->toUrl(),
			array(),
			$asset_file['version']
		);
	}

	public function addFilterScriptLoaderTag( string $tag, string $handle, string $src ): string {
		// 以下のサイトでは`script_loader_tag`を使った`defer`挿入ができなくなったとの記載があるが、
		// WordPress 6.8.2 での動作が確認できたため`wp_enqueue_script`側の対応はそのままとする。
		// https://note.com/hapiclo_leaves/n/n044526d7e82f

		// view用のスクリプトの場合、`defer`属性を追加する
		// ※ すでにフッターに出力する設定を`wp_enqueue_script`で行っているので効果は薄い
		if ( ( new HandleName() )->viewScript() === $handle ) {
			$result = preg_replace(
				'/<script(.*?)src=[\'"]' . preg_quote( $src, '/' ) . '[\'"](.*?)>/i',
				'<script$1src="' . esc_url( $src ) . '" defer$2>',
				$tag
			);
			return $result;
		}
		return $tag;
	}
}
