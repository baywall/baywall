<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Infrastructure\WordPress\Service\HandleNameProvider;
use Baywall\Core\Infrastructure\WordPress\Service\WpPluginInfoProvider;
use Baywall\Core\Presentation\Hooks\Base\HookBase;
use Baywall\Core\Presentation\Hooks\Service\PhpVarExporter;
use Psr\Container\ContainerInterface;

class ViewPageHook extends HookBase {

	private ContainerInterface $container;

	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueViewScripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'addFilterScriptLoaderTag' ), 10, 3 );
	}

	public function enqueueViewScripts(): void {
		if ( is_admin() ) {
			return;
		}

		/** @var HandleNameProvider */
		$handle_name_provider = $this->container->get( HandleNameProvider::class );
		/** @var WpPluginInfoProvider */
		$plugin = $this->container->get( WpPluginInfoProvider::class );
		/** @var PhpVarExporter */
		$php_var_exporter = $this->container->get( PhpVarExporter::class );

		// ゲストユーザー(一般の訪問者)表示用の登録する際のハンドル名を取得
		$handle_name = $handle_name_provider->viewScript();

		// アセットファイルを読み込む
		$asset_file = include WpConfig::VIEW_ASSET_PATH;

		// スクリプトを登録
		wp_enqueue_script(
			$handle_name,
			$plugin->toUrl( WpConfig::VIEW_JS_RELATIVE_PATH ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true   // フッターに出力 ※ 7.0.2でも`script_loader_tag`による`defer`挿入が可能であることを確認したため、配列にはせず`true`のままとする
		);

		// ウィジェットCSSのURLを生成する(キャッシュバスティングのためバージョンクエリを付与)
		// フロントエンドは本URLを Shadow DOM 内に`<link>`で注入してスタイルを適用する
		$view_css_url = add_query_arg( 'ver', $asset_file['version'], $plugin->toUrl( WpConfig::VIEW_CSS_RELATIVE_PATH ) );

		// インラインスクリプトを追加(CSS URLをフロントエンドへ受け渡す)
		$php_var_exporter->addInlineScript( $handle_name, $view_css_url );

		// ※ ウィジェット用CSSの`wp_enqueue_style`は廃止した。
		// CSSは Shadow DOM 内の`<link>`注入のみで適用するため、ライトDOMへの`<link>`出力は行わない。
	}

	public function addFilterScriptLoaderTag( string $tag, string $handle, string $src ): string {
		// 以下のサイトでは`script_loader_tag`を使った`defer`挿入ができなくなったとの記載があるが、
		// WordPress 7.0.2 での動作が確認できたため`wp_enqueue_script`側の対応はそのままとする。
		// https://note.com/hapiclo_leaves/n/n044526d7e82f

		/** @var HandleNameProvider */
		$handle_name_provider = $this->container->get( HandleNameProvider::class );

		// view用のスクリプトの場合、`defer`属性を追加する
		// ※ すでにフッターに出力する設定を`wp_enqueue_script`で行っているので効果は薄い
		if ( $handle_name_provider->viewScript() === $handle ) {
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
