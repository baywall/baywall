import fs from 'node:fs';
import path from 'node:path';
import util from 'node:util';
import { Logger } from '../../domain/service/Logger';
import { LogLevel } from '../../domain/types/LogLevel';

/** 指定したファイルにログを出力するLogger */
export class FileLogger implements Logger {
	private readonly argsFormatter: ArgsFormatter = new ArgsFormatter();

	// eslint-disable-next-line no-useless-constructor
	public constructor( private logFilePath: string ) {}

	public log( _: LogLevel, ...args: any[] ): void {
		// ログファイルのディレクトリが存在しない場合は作成する
		const logFilePath = path.resolve( process.cwd(), this.logFilePath );
		const logDirPath = path.dirname( logFilePath );
		if ( ! fs.existsSync( logDirPath ) ) {
			fs.mkdirSync( logDirPath, { recursive: true } );
		}

		// ログをファイルに追記する
		const message = this.argsFormatter.format( ...args );
		fs.appendFileSync( logFilePath, `${ message }\n`, { encoding: 'utf-8' } );
	}
}

/** 複数の引数を1つの文字列にフォーマットするクラス */
class ArgsFormatter {
	public format( ...args: any[] ): string {
		if ( args.length === 0 ) {
			return '';
		}
		return args
			.map( ( arg ) => ( typeof arg === 'string' ? arg : util.inspect( arg, { depth: null } ) ) )
			.join( ' ' );
	}
}
