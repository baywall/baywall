import { RestPhpVar } from '../../types/RestPhpVar';
import { getRestPhpVarName } from './getRestPhpVarName';

export const getRestPhpVar = (): RestPhpVar | null => {
	const varName = getRestPhpVarName();
	return ( window as any )[ varName ] ?? null;
};
