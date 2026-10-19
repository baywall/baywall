import blockJson from '../../block.json';

declare global {
	interface Window {
		ajaxurl: string;
	}
}

/** 管理画面のURLを取得します */
const getAdminUrl = (): URL => {
	return new URL(window.ajaxurl.replace(/admin-ajax\.php$/, ''), window.location.origin);
};

/** 本プラグインのダッシュボードのURLを取得します */
export const getDashboardUrl = (): URL => {
	const settingsPageUrl = new URL('admin.php', getAdminUrl());
	settingsPageUrl.searchParams.set('page', blockJson.textdomain);
	return settingsPageUrl;
};
