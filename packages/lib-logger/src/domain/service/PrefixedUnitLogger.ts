import { LogLevel } from '../types/LogLevel';
import { LogPrefixProvider } from './LogPrefixProvider';
import { UnitLogger } from './UnitLogger';

/** ログの先頭に指定された文字列を付与してログ出力するUnitLoggerの実装 */
export class PrefixedUnitLogger implements UnitLogger {
	// eslint-disable-next-line no-useless-constructor
	public constructor(
		private logPrefixProvider: LogPrefixProvider,
		private unitLogger: UnitLogger
	) {}

	public log( logLevel: LogLevel, ...args: any[] ): void {
		const prefix = this.logPrefixProvider.get( logLevel );
		this.unitLogger.log( logLevel, prefix, ...args );
	}
}
