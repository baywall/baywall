import { HttpUrl } from '@serendipity/lib-value-object';
import { getPhpVar } from '../../../lib/php-var/getPhpVar';
import { useMemo } from 'react';

/** APIのルートURLを取得します */
export const useApiRoot = (): HttpUrl | null | undefined => {
	return useMemo( () => getApiRootValue(), [] );
};

const getApiRootValue = (): HttpUrl | null => {
	const phpVar = getPhpVar();

	if ( phpVar === null ) {
		return null; // HTMLにphpVarが出力されていない場合
	} else if ( phpVar.apiRoot === undefined || phpVar.apiRoot === null ) {
		return null;
	} else if ( typeof phpVar.apiRoot === 'string' ) {
		return HttpUrl.from( phpVar.apiRoot );
	} else {
		throw new Error( `[F8B7B8BA] invalid apiRoot: ${ phpVar.apiRoot }, ${ typeof phpVar.apiRoot }` );
	}
};
