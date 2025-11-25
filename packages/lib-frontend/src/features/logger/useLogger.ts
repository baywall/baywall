import { useMemo } from 'react';
import { DateFormatter } from '@serendipity/lib-date-format';
import { ApplicationLogger, ConsoleLogger, PrefixedLogger, LOG_LEVEL } from '@serendipity/lib-logger';
import { Config } from '../../constant/Config';

/**
 * 本プロジェクトで使用するloggerを取得します
 */
export const useLogger = (): ApplicationLogger => {
	return useMemo( () => {
		const logLevelLabels = {
			[ LOG_LEVEL.DEBUG ]: 'DEBUG',
			[ LOG_LEVEL.INFO ]: 'INFO',
			[ LOG_LEVEL.WARN ]: 'WARN',
			[ LOG_LEVEL.ERROR ]: 'ERROR',
		};
		const dateFormatter = new DateFormatter();

		const logger = new PrefixedLogger(
			{
				get( logLevel ) {
					const logLevelString = logLevelLabels[ logLevel ] || `UNKNOWN(${ logLevel })`;
					const now = new Date();
					now.setHours( now.getHours() + 9 ); // JSTの時刻が表示されるように調整
					const timestamp = dateFormatter.format( now, Config.LOG_DATE_FORMAT );
					return `[${ timestamp }][${ logLevelString }]`; // 例: [2025-10-09 04:44:45.123][INFO]
				},
			},
			new ConsoleLogger()
		);
		// TODO: サーバーでログレベルを管理し、phpVarへ出力。
		// ここではusePhpVarからレベルを取得してcreateメソッドの引数に指定する
		return new ApplicationLogger( logger );
	}, [] );
};
