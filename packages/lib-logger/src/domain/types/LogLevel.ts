import { LOG_LEVEL } from '../../constant/LogLevel';

export type LogLevel = ( typeof LOG_LEVEL )[ keyof typeof LOG_LEVEL ];
