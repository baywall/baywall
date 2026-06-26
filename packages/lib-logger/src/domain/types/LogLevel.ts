import { LOG_LEVEL } from '../../constant/LogLevel.js';

export type LogLevel = ( typeof LOG_LEVEL )[ keyof typeof LOG_LEVEL ];
