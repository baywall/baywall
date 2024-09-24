import { useMemo } from 'react';
import { getPhpVar } from './getPhpVar';

/**
 * PHPから出力されたJavaScript変数からREST API関連の情報を取得します。
 */
export const useRestPhpVar = () => {
	return useMemo( () => {
		return getPhpVar();
	}, [] );
};
