import { LOG_LEVEL } from '../../constant/LogLevel';
import { LogLevel } from '../types/LogLevel';
import { Logger } from './Logger';

/** 指定されたログレベルでログ出力を制御するLoggerの実装 */
export class LevelFilteredLogger implements Logger {
	private readonly logLevels = [ LOG_LEVEL.DEBUG, LOG_LEVEL.INFO, LOG_LEVEL.WARN, LOG_LEVEL.ERROR ];

	/** コンストラクタで指定されたログレベルのインデックス */
	private readonly logLevelIndex: number;

	public constructor(
		private unitLogger: Logger,
		logLevel: LogLevel
	) {
		this.logLevelIndex = this.logLevels.indexOf( logLevel );
		if ( this.logLevelIndex === -1 ) {
			throw new Error( `[4169367A] Invalid log level: ${ logLevel }` );
		}
	}

	public log( logLevel: LogLevel, ...args: any[] ): void {
		const index = this.logLevels.indexOf( logLevel );
		if ( index === -1 ) {
			throw new Error( `[DBD3CCA7] Invalid log level: ${ logLevel }` );
		}

		if ( index >= this.logLevelIndex ) {
			this.unitLogger.log( logLevel, ...args );
		}
	}
}
