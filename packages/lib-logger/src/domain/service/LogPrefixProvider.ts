import { LogLevel } from '../types/LogLevel';

/** ログの先頭に付与する文字列を取得するインターフェース */
export interface LogPrefixProvider {
	get: ( logLevel: LogLevel ) => string;
}
