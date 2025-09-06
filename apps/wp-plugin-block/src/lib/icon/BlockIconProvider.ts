import { currencyDollar } from '@wordpress/icons';

// @wordpress/icons のアイコン一覧: https://wordpress.github.io/gutenberg/?path=/story/icons-icon--library

export class BlockIconProvider {
	public get(): React.JSX.Element {
		return currencyDollar;
	}
}
