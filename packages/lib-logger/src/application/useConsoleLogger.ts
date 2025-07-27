import { useMemo } from 'react';
import { ConsoleLogger } from '../infrastructure/ConsoleLogger';

export const useConsoleLogger = () => {
	return useMemo( () => new ConsoleLogger(), [] );
};
