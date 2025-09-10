import { useMemo } from 'react';
import { Logger, LOG_LEVEL, LoggerFactory } from '@serendipity/lib-logger';

/**
 * 本プロジェクトで使用するloggerを取得します
 */
export const useLogger = (): Logger => {
	return useMemo( () => {
		// TODO: サーバーでログレベルを管理し、phpVarへ出力。
		// ここではusePhpVarからレベルを取得してcreateメソッドの引数に指定する
		return new LoggerFactory().create( LOG_LEVEL.INFO );
	}, [] );
};
