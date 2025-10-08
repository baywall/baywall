import { useMemo } from 'react';
import { ApplicationLogger, ConsoleLogger } from '@serendipity/lib-logger';

/**
 * 本プロジェクトで使用するloggerを取得します
 */
export const useLogger = (): ApplicationLogger => {
	return useMemo( () => {
		// TODO: サーバーでログレベルを管理し、phpVarへ出力。
		// ここではusePhpVarからレベルを取得してcreateメソッドの引数に指定する
		return new ApplicationLogger( new ConsoleLogger() ); // 一旦コンソール出力のみ
	}, [] );
};
