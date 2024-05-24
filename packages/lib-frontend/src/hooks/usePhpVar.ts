import { useMemo } from 'react';
import { getPhpCommonVar } from '../functions/getPhpVar';

/**
 * 管理画面、フロント画面共通の変数を取得します。
 */
export const usePhpCommonVar = () => {
	return useMemo( () => {
		return getPhpCommonVar();
	}, [] );
};
