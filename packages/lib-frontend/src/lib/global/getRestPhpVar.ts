import { RestPhpVar } from '../../types/RestPhpVar';
import { PhpVarName } from '../repository/PhpVarName';

export const getRestPhpVar = (): RestPhpVar | null => {
	const varName = new PhpVarName().get();
	return ( window as any )[ varName ] ?? null;
};
