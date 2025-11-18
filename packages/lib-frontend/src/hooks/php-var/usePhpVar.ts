import { useMemo } from 'react';
import { getPhpVar } from '../../lib/php-var/getPhpVar';

/**
 * PHPから出力されたJavaScript変数からREST API関連の情報を取得します。
 *
 * @deprecated
 * TODO: 削除 - usePostId等のhooksに置き換え
 */
export const usePhpVar = () => {
	return useMemo( () => {
		return getPhpVar();
	}, [] );
};
