import { RestPhpVar } from '../../types/RestPhpVar';
import { RestPhpVarName } from '../repository/RestPhpVarName';

export const getRestPhpVar = (): RestPhpVar | null => {
	const varName = new RestPhpVarName().get();
	return ( window as any )[ varName ] ?? null;
};
