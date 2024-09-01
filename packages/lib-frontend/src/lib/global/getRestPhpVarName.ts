// import Constants from '@yamaneyuta/serendipity-constants/frontend/lib/constants.json';
const Constants = require( '@yamaneyuta/serendipity-constants/frontend/lib/constants.json' );

export const getRestPhpVarName = (): string => {
	return Constants.phpVarName.rest;
};
