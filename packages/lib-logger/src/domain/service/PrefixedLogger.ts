import { LogLevel } from '../types/LogLevel.js';
import { LogPrefixProvider } from './LogPrefixProvider.js';
import { Logger } from './Logger.js';

/** ログの先頭に指定された文字列を付与してログ出力するLoggerの実装 */
export class PrefixedLogger implements Logger {
	public constructor(
		private logPrefixProvider: LogPrefixProvider,
		private logger: Logger
	) {}

	public log(logLevel: LogLevel, ...args: any[]): void {
		const prefix = this.logPrefixProvider.get(logLevel);
		this.logger.log(logLevel, prefix, ...args);
	}
}
