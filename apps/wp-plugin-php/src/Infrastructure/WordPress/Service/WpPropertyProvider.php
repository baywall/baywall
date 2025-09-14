<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

/**
 * WordPressの設定情報を取得するクラス。
 */
class WpPropertyProvider {
	/**
	 * 指定したIDの投稿が公開されているかどうかを返します。
	 *
	 * @param PostId $post_id
	 */
	public function isPublished( PostId $post_id ): bool {
		return get_post_status( $post_id->value() ) === 'publish';
	}


	/**
	 * 「設定 > パーマリンク設定」で「基本」(英語の場合は「Plain」)のパーマリンクが選択されているかどうかを取得します。
	 *
	 * @return bool
	 */
	public function isDefaultPermalink(): bool {
		return get_option( 'permalink_structure' ) === '';
	}


	/**
	 * 「設定 > 一般」の「サイトアドレス (URL)」(サイト訪問者がアクセスするURL)を返します。
	 */
	public function siteAddress(): string {
		// get_bloginfo('url') calls home_url() calls get_home_url()
		// https://wordpress.stackexchange.com/questions/16161/what-is-difference-between-get-bloginfourl-and-get-site-url
		return get_home_url();
	}
}
