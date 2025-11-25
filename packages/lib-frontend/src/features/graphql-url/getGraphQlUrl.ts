import { HttpUrl } from '@serendipity/lib-value-object';
import { Config } from '../../constant/Config';
import { getApiRoot } from '../php-var/api-root/getApiRoot';

/** GraphQLのエンドポイントを取得します */
export const getGraphQlUrl = (): HttpUrl | null => {
	const apiRoot = getApiRoot();

	if ( apiRoot === null ) {
		return null;
	} else {
		return HttpUrl.from( apiRoot.value.replace( /\/+$/, '' ) + '/' + Config.GRAPHQL_ENDPOINT_PATH );
	}
};
