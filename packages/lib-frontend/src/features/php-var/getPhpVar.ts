import { PhpVar } from '../../types/PhpVar.js';
import { PhpVarNameProvider } from './PhpVarNameProvider.js';

export const getPhpVar = (): PhpVar | null => {
	const varName = new PhpVarNameProvider().get();
	return (window as any)[varName] ?? null;
};
