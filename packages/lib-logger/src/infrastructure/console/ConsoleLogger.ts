import { LOG_LEVEL } from '../../constant/LogLevel';
import { Logger } from '../../domain/service/Logger';
import { LogLevel } from '../../domain/types/LogLevel';

/* eslint-disable no-console */

/** コンソールにログを出力するLoggerの実装 */
export class ConsoleLogger implements Logger {
	private readonly levels = {
		[ LOG_LEVEL.DEBUG ]: 'debug',
		[ LOG_LEVEL.INFO ]: 'info',
		[ LOG_LEVEL.WARN ]: 'warn',
		[ LOG_LEVEL.ERROR ]: 'error',
	} as const;

	public log( logLevel: LogLevel, ...args: any[] ): void {
		console[ this.levels[ logLevel ] ]( ...args );
	}
}
