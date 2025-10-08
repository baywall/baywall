import { LOG_LEVEL } from '../../constant/LogLevel';
import { LogLevel } from '../types/LogLevel';
import { Logger } from './Logger';

/**
 * アプリケーションから利用されるLogger
 *
 * 各ログレベルに対応したメソッドを提供し、内部で指定されたLoggerのlogメソッドを呼び出す
 */
export class ApplicationLogger {
	// eslint-disable-next-line no-useless-constructor
	public constructor( private readonly logger: Logger ) {}

	public debug( ...args: any[] ): void {
		this.log( LOG_LEVEL.DEBUG, ...args );
	}
	public info( ...args: any[] ): void {
		this.log( LOG_LEVEL.INFO, ...args );
	}
	public warn( ...args: any[] ): void {
		this.log( LOG_LEVEL.WARN, ...args );
	}
	public error( ...args: any[] ): void {
		this.log( LOG_LEVEL.ERROR, ...args );
	}

	private log( level: LogLevel, ...args: any[] ): void {
		this.logger.log( level, ...args );
	}
}
