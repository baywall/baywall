/* eslint-disable no-console */
import { Logger } from '../domain/service/Logger';

export class ConsoleLogger implements Logger {
	debug( message: string, ...optionalParams: unknown[] ): void {
		console.debug( `[DEBUG] ${ message }`, ...optionalParams );
	}

	info( message: string, ...optionalParams: unknown[] ): void {
		console.info( `[INFO] ${ message }`, ...optionalParams );
	}

	warn( message: string, ...optionalParams: unknown[] ): void {
		console.warn( `[WARN] ${ message }`, ...optionalParams );
	}

	error( message: string, ...optionalParams: unknown[] ): void {
		console.error( `[ERROR] ${ message }`, ...optionalParams );
	}
}
