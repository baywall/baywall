import { PhpVar } from '../../types/PhpVar';
import { PhpVarName } from '../repository/PhpVarName';

export const getPhpVar = (): PhpVar | null => {
	const varName = new PhpVarName().get();
	return ( window as any )[ varName ] ?? null;
};
