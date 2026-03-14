<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\InvoiceTokenCookieProvider;
use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Domain\Entity\InvoiceToken;
use Cornix\Serendipity\Core\Infrastructure\Cookie\Cookie;

class WpInvoiceTokenCookieProvider implements InvoiceTokenCookieProvider {

	private WordPressPropertyProvider $wp_property;

	public function __construct( WordPressPropertyProvider $wp_property ) {
		$this->wp_property = $wp_property;
	}

	public function get( InvoiceToken $invoice_token ): Cookie {
		return Cookie::create(
			WpConfig::COOKIE_NAME_INVOICE_TOKEN, // name
			$invoice_token->token()->value(), // value
			$invoice_token->expiresAt()->value(), // expires
			$this->path(),
			null, // domain: nullで発行元ホスト名が自動設定される
			$this->secure(),
			true, // httponly: trueに設定してJSからのアクセスを防止
			'Strict' // samesite
		);
	}

	/** @inheritDoc */
	public function getExpired(): Cookie {
		return Cookie::create(
			WpConfig::COOKIE_NAME_INVOICE_TOKEN, // name
			'', // value: 設定不要
			time() - 3600, // expires: 過去日時をセットしてクッキーが削除されるようにする
			$this->path(),
			null, // domain: nullで発行元ホスト名が自動設定される
			$this->secure(),
			true, // httponly: trueに設定してJSからのアクセスを防止
			'Strict' // samesite
		);
	}

	/** Cookieに書き込むリフレッシュトークンのパスを取得します */
	private function path(): string {
		$api_root_url = $this->wp_property->apiRootUrl();
		return parse_url( trailingslashit( $api_root_url ) . WpConfig::REST_NAMESPACE . '/' . WpConfig::REST_ROUTE_AUTH_TOKEN_INVOICE, PHP_URL_PATH );
	}

	private function secure(): bool {
		// ローカル環境のみ、HTTPSでなくてもクッキーを送信する
		// ※ HTTP環境のWordPressでは本プラグインは動作しないため、インストール時にチェックが必要
		return $this->wp_property->getEnvironmentType() === 'local' ? $this->wp_property->isSsl() : true;
	}
}
