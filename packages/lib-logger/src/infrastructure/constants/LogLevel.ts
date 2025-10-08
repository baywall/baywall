// LOG_LEVELの値を使ってログ出力のON/OFFを制御するため数値で定義
export const LOG_LEVEL = {
	DEBUG: 1,
	INFO: 2,
	WARN: 3,
	ERROR: 4,
} as const;

export type LogLevel = ( typeof LOG_LEVEL )[ keyof typeof LOG_LEVEL ];
