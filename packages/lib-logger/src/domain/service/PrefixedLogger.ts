import { LogLevel } from '../types/LogLevel';
import { LogPrefixProvider } from './LogPrefixProvider';
import { Logger } from './Logger';

/** ログの先頭に指定された文字列を付与してログ出力するLoggerの実装 */
export class PrefixedLogger implements Logger {
	// eslint-disable-next-line no-useless-constructor
	public constructor(
		private logPrefixProvider: LogPrefixProvider,
		private logger: Logger
	) {}

	public log( logLevel: LogLevel, ...args: any[] ): void {
		const prefix = this.logPrefixProvider.get( logLevel );
		this.logger.log( logLevel, prefix, ...args );
	}
}
