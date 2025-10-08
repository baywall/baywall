import { LOG_LEVEL } from '../../constant/LogLevel';
import { LogLevel } from '../types/LogLevel';
import { UnitLogger } from './UnitLogger';

/** 指定されたログレベルでログ出力を制御するUnitLoggerの実装 */
export class LevelFilteredUnitLogger implements UnitLogger {
	private readonly logLevels = [ LOG_LEVEL.DEBUG, LOG_LEVEL.INFO, LOG_LEVEL.WARN, LOG_LEVEL.ERROR ];

	/** コンストラクタで指定されたログレベルのインデックス */
	private readonly logLevelIndex: number;

	public constructor(
		private unitLogger: UnitLogger,
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
