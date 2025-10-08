import { ConsoleLogger } from '../ConsoleLogger';
import { LevelFilteredLogger } from '../LevelFilteredLogger';
import { ApplicationLogger } from '../../domain/service/Logger';
import { LogLevel } from '../../domain/types/LogLevel';

export class LoggerFactory {
	/**
	 * 本プロジェクトで使用するLoggerを取得します
	 * @param logLevel
	 */
	public create( logLevel: LogLevel ): ApplicationLogger {
		return new LevelFilteredLogger( new ConsoleLogger(), logLevel );
	}
}
