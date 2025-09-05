import blockJson from '../../block.json';

declare global {
	interface Window {
		ajaxurl: string;
	}
}

/** 管理画面等のURLを取得するクラス */
export class UrlProvider {
	/** 管理画面のURLを取得します */
	private get admin(): URL {
		return new URL( window.ajaxurl.replace( /admin-ajax\.php$/, '' ), window.location.origin );
	}

	/** 本プラグインのダッシュボードのURLを取得します */
	public get dashboard(): URL {
		const settingsPageUrl = new URL( 'admin.php', this.admin );
		settingsPageUrl.searchParams.set( 'page', blockJson.textdomain );
		return settingsPageUrl;
	}
}
