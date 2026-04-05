import i18next from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

/*
 * WordPressの `__` を使う方法は、翻訳ファイル出力時にエラーが発生していたりしたため断念。i18nextを使う方針としている。
 *
 * - ブロックのファイルは小さいので翻訳ファイルをすべてバンドルする
 */

import en from '../../i18n/translation.en.json';
import ja from '../../i18n/translation.ja.json';

const resources = {
	en: { translation: en },
	ja: { translation: ja },
};

const languageDetector = new LanguageDetector( null, {
	// localStorageやcookieにキャッシュを保存しない
	// ※ ユーザーに言語切り替えのUIを提供しないため、キャッシュは不要
	caches: [],
} );

i18next
	.use( languageDetector )
	.use( initReactI18next )
	.init( {
		resources,
		// lng: ⇒ `LanguageDetector`を使用しているため`lng`は指定しない
		fallbackLng: 'en',
		interpolation: {
			escapeValue: false,
		},
		// 空文字列を有効な翻訳として許可するかどうか(true: 許可する, false: 許可しない)
		returnEmptyString: false,
	} );

export default i18next;
