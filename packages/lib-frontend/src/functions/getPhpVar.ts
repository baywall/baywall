import constants from '@yamaneyuta/serendipity-constants/dist/constants-frontend.json';

export const getPhpVar = < T >( variableName: string ): T => {
	const phpVar: T | undefined = ( globalThis as any )[ variableName ];

	if ( phpVar === undefined ) {
		throw new Error( '{146ABE38-6DB9-4A34-9B9A-C639101C7C05}' );
	}

	return phpVar;
};

/**
 * PHPから出力された変数の型。
 */
type PhpCommonVarType = {
	site_address: string;
	api_root_paths: string[];
};

export const getPhpCommonVar = (): PhpCommonVarType => {
	return getPhpVar< PhpCommonVarType >( constants.phpVarName.common );
};
