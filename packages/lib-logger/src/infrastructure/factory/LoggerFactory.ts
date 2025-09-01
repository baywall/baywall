import { ConsoleLogger } from '../ConsoleLogger';
import { LevelFilteredLogger } from '../LevelFilteredLogger';
import { Logger } from '../Logger';
import { LogLevel } from '../constants/LogLevel';

export class LoggerFactory {
	/**
	 * 本プロジェクトで使用するLoggerを取得します
	 * @param logLevel
	 */
	public create( logLevel: LogLevel ): Logger {
		return new LevelFilteredLogger( new ConsoleLogger(), logLevel );
	}
}
