import { useMemo } from 'react';
import Constants from '@yamaneyuta/serendipity-constants/frontend/lib/constants.json';

/**
 * PHPから出力されたJavaScript変数からREST API関連の情報を取得します。
 */
export const useRestPhpVar = () => {
	return useMemo(() => {
		return getRestPhpVar();
	}, []);
};

type RestPhpVar = {
	wpRestNonce: string;
	graphqlUrl: string;
};

const getRestPhpVar = (): RestPhpVar | null => {
	const varName = Constants.phpVarName.rest;
	return (window as any)[varName] ?? null;
};
