<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Infrastructure\WordPress\Content\HandleNameProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginAssetUrlProvider;
use Baywall\Core\Presentation\Hooks\Service\CurrentPaywalledPostResolver;
use Baywall\Core\Presentation\Hooks\Service\PhpVarExporter;

class ViewPageHook implements Hook {
	/**
	 * @param \Closure(): CurrentPaywalledPostResolver $current_paywalled_post_resolver
	 * @param \Closure(): HandleNameProvider           $handle_name_provider
	 * @param \Closure(): WpPluginAssetUrlProvider     $asset_url_provider
	 * @param \Closure(): PhpVarExporter               $php_var_exporter
	 */
	public function __construct(
		private readonly \Closure $current_paywalled_post_resolver,
		private readonly \Closure $handle_name_provider,
		private readonly \Closure $asset_url_provider,
		private readonly \Closure $php_var_exporter,
	) {}

	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueViewScripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'addFilterScriptLoaderTag' ), 10, 3 );
	}

	public function enqueueViewScripts(): void {
		if ( is_admin() ) {
			return;
		}

		// ペイウォールウィジェットが描画されないページではviewスクリプトを出力しない
		if ( ( $this->current_paywalled_post_resolver )()->resolve() === null ) {
			return;
		}

		$handle_name_provider = ( $this->handle_name_provider )();
		$asset_url_provider   = ( $this->asset_url_provider )();
		$php_var_exporter     = ( $this->php_var_exporter )();

		// ゲストユーザー(一般の訪問者)表示用の登録する際のハンドル名を取得
		$handle_name = $handle_name_provider->viewScript();

		// アセットファイルを読み込む
		$asset_file = include WpConfig::VIEW_ASSET_PATH;

		// スクリプトを登録
		wp_enqueue_script(
			$handle_name,
			$asset_url_provider->url( WpConfig::VIEW_JS_RELATIVE_PATH ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true   // フッターに出力 ※ 7.0.2でも`script_loader_tag`による`defer`挿入が可能であることを確認したため、配列にはせず`true`のままとする
		);

		// ウィジェットCSSのURLを生成する(キャッシュバスティングのためバージョンクエリを付与)
		// フロントエンドは本URLを Shadow DOM 内に`<link>`で注入してスタイルを適用する
		$view_css_url = add_query_arg( 'ver', $asset_file['version'], $asset_url_provider->url( WpConfig::VIEW_CSS_RELATIVE_PATH ) );

		// インラインスクリプトを追加(CSS URLをフロントエンドへ受け渡す)
		$php_var_exporter->addInlineScript( $handle_name, $view_css_url );

		// ホスト要素(ペイウォールブロックの`aside`)へのテーマの`:focus`ルール適用を抑制するインラインスタイルを出力する。
		// テーマの`:focus`ルール(例: Twenty Twenty-Fourの`:where(.wp-site-blocks *:focus)`)は、Shadow DOM内のコントロールがフォーカスを受けた際にホスト要素にもマッチし、ウィジェット全体にoutlineが描画されてしまう。
		// ホスト自体はフォーカス可能な要素ではなく、実際のフォーカス表示はShadow内のコントロール側にあるため、ホスト側のoutlineは抑制する。
		// Shadow内の`:host`ルールではライトDOM側の宣言を上書きできないため、ここで出力する。
		$host_style_handle = $handle_name_provider->viewHostStyle();
		$host_class_name   = '.' . WpConfig::PAYWALL_BLOCK_CSS_CLASS_NAME;
		wp_register_style( $host_style_handle, false, array(), $asset_file['version'] );
		wp_enqueue_style( $host_style_handle );
		wp_add_inline_style( $host_style_handle, $host_class_name . ':focus, ' . $host_class_name . ':focus-within { outline: none !important; }' );

		// ※ ウィジェット内部用CSSの`wp_enqueue_style`は廃止した。
		// CSSは Shadow DOM 内の`<link>`注入のみで適用するため、ライトDOMへの`<link>`出力は行わない。
		// 上記のインラインスタイルはホスト要素へのテーマ適用を打ち消すためのもので、ウィジェット内部のスタイルではない。
	}

	public function addFilterScriptLoaderTag( string $tag, string $handle, string $src ): string {
		// 以下のサイトでは`script_loader_tag`を使った`defer`挿入ができなくなったとの記載があるが、
		// WordPress 7.0.2 での動作が確認できたため`wp_enqueue_script`側の対応はそのままとする。
		// https://note.com/hapiclo_leaves/n/n044526d7e82f

		$handle_name_provider = ( $this->handle_name_provider )();

		// view用のスクリプトの場合、`defer`属性を追加する
		// ※ すでにフッターに出力する設定を`wp_enqueue_script`で行っているので効果は薄い
		if ( $handle_name_provider->viewScript() === $handle ) {
			return preg_replace(
				'/<script(.*?)src=[\'"]' . preg_quote( $src, '/' ) . '[\'"](.*?)>/i',
				'<script$1src="' . esc_url( $src ) . '" defer$2>',
				$tag
			) ?? throw new \RuntimeException( '[B809A845] Failed to add defer attribute.' );
		}
		return $tag;
	}
}
