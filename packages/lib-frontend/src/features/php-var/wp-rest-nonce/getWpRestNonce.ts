import { WpRestNonce } from '@serendipity/lib-value-object';
import { getPhpVar } from '../getPhpVar';

export const getWpRestNonce = (): WpRestNonce | null => {
	const phpVar = getPhpVar();

	if ( phpVar === null ) {
		return null; // HTMLにphpVarが出力されていない場合
	} else if ( phpVar.wpRestNonce === undefined || phpVar.wpRestNonce === null ) {
		return null;
	} else if ( typeof phpVar.wpRestNonce === 'string' ) {
		return WpRestNonce.from( phpVar.wpRestNonce );
	} else {
		throw new Error( `[5B3FF140] invalid wpRestNonce: ${ phpVar.wpRestNonce }, ${ typeof phpVar.wpRestNonce }` );
	}
};
