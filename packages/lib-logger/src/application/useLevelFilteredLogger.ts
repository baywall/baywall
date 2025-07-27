import { useMemo } from 'react';
import { LevelFilteredLogger } from '../infrastructure/LevelFilteredLogger';
import { Logger } from '../infrastructure/Logger';
import { LogLevel } from '../infrastructure/LogLevel';

export const useLevelFilteredLogger = ( logger: Logger, logLevel: LogLevel | undefined ): Logger | undefined => {
	return useMemo( () => {
		if ( logLevel === undefined ) {
			return undefined;
		} else {
			return new LevelFilteredLogger( logger, logLevel );
		}
	}, [ logger, logLevel ] );
};
