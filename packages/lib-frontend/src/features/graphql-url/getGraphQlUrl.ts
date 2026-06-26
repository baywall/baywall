import { HttpUrl } from '@serendipity/lib-value-object';
import { Config } from '../../constant/Config.js';
import { getApiRoot } from '../php-var/api-root/getApiRoot.js';

/** GraphQLのエンドポイントを取得します */
export const getGraphQlUrl = (): HttpUrl | null => {
	const apiRoot = getApiRoot();

	if ( apiRoot === null ) {
		return null;
	} else {
		return HttpUrl.from( apiRoot.value.replace( /\/+$/, '' ) + '/' + Config.GRAPHQL_ENDPOINT_PATH );
	}
};
