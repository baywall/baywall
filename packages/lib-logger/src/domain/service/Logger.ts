import { LogLevel } from '../types/LogLevel';

/** 指定された引数をログ出力(出力先は継承先で決定)するためのインターフェース */
export interface Logger {
	log: ( logLevel: LogLevel, ...args: any[] ) => void;
}
