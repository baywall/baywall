import { getRestPhpVarName } from './getRestPhpVarName';

type RestPhpVar = {
	wpRestNonce: string;
	graphqlUrl: string;
};

export const getRestPhpVar = (): RestPhpVar | null => {
	const varName = getRestPhpVarName();
	return ( window as any )[ varName ] ?? null;
};
