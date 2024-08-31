// import Constants from '@yamaneyuta/serendipity-constants/frontend/lib/constants.json';
const Constants = require( '@yamaneyuta/serendipity-constants/frontend/lib/constants.json' );

type RestPhpVar = {
	wpRestNonce: string;
	graphqlUrl: string;
};

export const getRestPhpVar = (): RestPhpVar | null => {
	const varName = Constants.phpVarName.rest;
	return ( window as any )[ varName ] ?? null;
};
