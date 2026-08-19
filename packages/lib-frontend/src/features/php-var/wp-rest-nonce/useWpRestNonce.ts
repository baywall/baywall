import { useMemo } from 'react';
import { WpRestNonce } from '@baywall/lib-value-object';
import { getWpRestNonce } from './getWpRestNonce.js';

/** WordPressのAPIリクエスト用nonceを取得します */
export const useWpRestNonce = (): WpRestNonce | null | undefined => {
	return useMemo(() => getWpRestNonce(), []);
};
