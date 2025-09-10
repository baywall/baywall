import { useMemo } from 'react';
import { getPhpVar } from '../php-var/getPhpVar';

/**
 * PHPから出力されたJavaScript変数からREST API関連の情報を取得します。
 */
export const usePhpVar = () => {
	return useMemo( () => {
		return getPhpVar();
	}, [] );
};
