import { LogLevel } from '../types/LogLevel';
import { Logger } from './Logger';

/** 複数のLoggerに対して一括でログ出力を行うためのクラス */
export class BatchLogger implements Logger {
	// eslint-disable-next-line no-useless-constructor
	public constructor( private readonly loggers: Logger[] ) {}

	public log( logLevel: LogLevel, ...args: any[] ): void {
		for ( const logger of this.loggers ) {
			logger.log( logLevel, ...args );
		}
	}
}
