import { useMemo } from 'react';
import { getRestPhpVar } from './getRestPhpVar';

/**
 * PHPから出力されたJavaScript変数からREST API関連の情報を取得します。
 */
export const useRestPhpVar = () => {
	return useMemo(() => {
		return getRestPhpVar();
	}, []);
};
