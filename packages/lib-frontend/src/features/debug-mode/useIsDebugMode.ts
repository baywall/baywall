import { useMemo } from 'react';
import { isDebugMode } from './isDebugMode';

/** デバッグモードで動作しているかどうかを取得します */
export const useIsDebugMode = (): boolean => {
	return useMemo( () => {
		// ReactRouterやhistory.pushStateなどといった手段で
		// デバッグ用のパラメータが付与されることは無いので
		// 初回レンダリング時での評価で問題ない
		return isDebugMode();
	}, [] );
};
