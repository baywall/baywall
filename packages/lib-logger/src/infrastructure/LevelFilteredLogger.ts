import { Logger } from '../domain/service/Logger';
import { LOG_LEVEL } from '../constant/LogLevel';
import { LogLevel } from '../domain/types/LogLevel';

/** 指定したログレベルに応じてログを出力するLogger */
export class LevelFilteredLogger implements Logger {
	public constructor( logger: Logger, logLevel: LogLevel ) {
		this.logger = logger;
		this.logLevel = logLevel;
	}
	private logger: Logger;
	private logLevel: LogLevel;

	public debug( message?: any, ...optionalParams: any[] ): void {
		if ( this.logLevel <= LOG_LEVEL.DEBUG ) {
			this.logger.debug( message, ...optionalParams );
		}
	}
	public info( message?: any, ...optionalParams: any[] ): void {
		if ( this.logLevel <= LOG_LEVEL.INFO ) {
			this.logger.info( message, ...optionalParams );
		}
	}
	public warn( message?: any, ...optionalParams: any[] ): void {
		if ( this.logLevel <= LOG_LEVEL.WARN ) {
			this.logger.warn( message, ...optionalParams );
		}
	}
	public error( message?: any, ...optionalParams: any[] ): void {
		if ( this.logLevel <= LOG_LEVEL.ERROR ) {
			this.logger.error( message, ...optionalParams );
		}
	}
}
