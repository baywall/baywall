import { PhpVar } from '../../types/PhpVar';
import { PhpVarNameProvider } from './PhpVarNameProvider';

export const getPhpVar = (): PhpVar | null => {
	const varName = new PhpVarNameProvider().get();
	return ( window as any )[ varName ] ?? null;
};
