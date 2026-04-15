import { useMemo } from 'react';
import { DateFormatter } from '@serendipity/lib-date-format';
import {
	ApplicationLogger,
	ConsoleLogger,
	PrefixedLogger,
	LevelFilteredLogger,
	LOG_LEVEL,
	Logger,
} from '@serendipity/lib-logger';
import { Config } from '../../constant/Config';
import { LogLevel } from '../../../../lib-logger/dist/cjs';

/**
 * 本プロジェクトで使用するloggerを取得します
 */
export const useLogger = (): ApplicationLogger => {
	const logLevel = useLogLevel();
	return useMemo( () => {
		const logLevelLabels = {
			[ LOG_LEVEL.DEBUG ]: 'DEBUG',
			[ LOG_LEVEL.INFO ]: 'INFO',
			[ LOG_LEVEL.WARN ]: 'WARN',
			[ LOG_LEVEL.ERROR ]: 'ERROR',
		};
		const dateFormatter = new DateFormatter();

		let logger: Logger = new PrefixedLogger(
			{
				get( level ) {
					const logLevelString = logLevelLabels[ level ] || `UNKNOWN(${ level })`;
					const now = new Date();
					now.setHours( now.getHours() + 9 ); // JSTの時刻が表示されるように調整
					const timestamp = dateFormatter.format( now, Config.LOG_DATE_FORMAT );
					return `[${ timestamp }][${ logLevelString }]`; // 例: [2025-10-09 04:44:45.123][INFO]
				},
			},
			new ConsoleLogger()
		);
		logger = new LevelFilteredLogger( logger, logLevel );
		// ログレベルをURLのクエリパラメータから取得して、LevelFilteredLoggerで制御する
		return new ApplicationLogger( logger );
	}, [ logLevel ] );
};

const useLogLevel = (): LogLevel => {
	// URLのクエリパラメータからログレベルを取得する
	// クエリパラメータに存在しない場合はConfig.DEFAULT_LOG_LEVELを使用
	// - key: `log-level`
	// - value: `debug` | `info` | `warn` | `error`

	return useMemo( () => {
		const params = new URLSearchParams( window.location.search );
		const logLevelValue = params.get( Config.LOG_LEVEL_KEY );

		if ( logLevelValue === null ) {
			return Config.DEFAULT_LOG_LEVEL;
		}

		const logLevel: LogLevel = LOG_LEVEL[ logLevelValue.toUpperCase() as keyof typeof LOG_LEVEL ];

		if ( logLevel === undefined ) {
			console.warn(
				`[43ABED04] Invalid log level in query parameter: ${ logLevelValue }. Falling back to default: ${ Config.DEFAULT_LOG_LEVEL }`
			);
			return Config.DEFAULT_LOG_LEVEL;
		}

		return logLevel;
	}, [] );
};
