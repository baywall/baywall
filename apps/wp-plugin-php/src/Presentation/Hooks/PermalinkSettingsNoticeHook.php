<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\Hooks;

use Baywall\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;

/**
 * 「設定 > パーマリンク設定」画面に、デフォルト（基本）パーマリンク利用時の通知を表示するフック。
 *
 * デフォルト（基本）パーマリンクではリフレッシュトークンCookieのpathを`/index.php`にしか設定できず、
 * Cookieのセキュリティが低下するため、パーマリンク変更を管理者へ促します。
 * 表示処理の例外で画面を壊さないよう、全例外は`\Throwable`で捕捉して素通しします。
 */
class PermalinkSettingsNoticeHook implements Hook {
	/**
	 * @param \Closure(): WordPressPropertyProvider $property_provider
	 */
	public function __construct(
		private readonly \Closure $property_provider,
	) {}

	public function register(): void {
		add_action( 'admin_notices', array( $this, 'renderAdminNotice' ) );
	}

	public function renderAdminNotice(): void {
		try {
			$screen = get_current_screen();
			if ( is_null( $screen ) || 'options-permalink' !== $screen->base ) {
				return;
			}

			$property_provider = ( $this->property_provider )();
			if ( ! $property_provider->isDefaultPermalinkStructure() ) {
				return;
			}

			echo '<div class="notice notice-info"><p>' . esc_html__( 'baywall: Changing the permalink settings improves the security of the cookies used by the baywall plugin.', 'baywall' ) . '</p></div>';
		} catch ( \Throwable $e ) {
			// Do nothing: 表示処理の失敗でパーマリンク設定画面を壊さない
		}
	}
}
