import { useMemo } from 'react';
import { useGraphQLUrl } from '../graphql-url/useGraphQLUrl';

export const useTextDomain = (): string | null | undefined => {
	const graphqlUrl = useGraphQLUrl();

	return useMemo( () => {
		if ( ! graphqlUrl ) {
			return graphqlUrl;
		}

		// 一旦、hrefからoriginを削除したものを取得
		const originRemoved = graphqlUrl.replace( new URL( graphqlUrl ).origin, '' );
		const paths = originRemoved.split( '/' ).filter( ( path ) => path !== '' );

		// `/[domain-text]/graphql`の形式であることを確認
		if ( paths.length < 2 || paths[ paths.length - 1 ] !== 'graphql' ) {
			throw new Error( '[97289E73] Invalid graphql url. url: ' + graphqlUrl );
		}

		// textDomainにあたる部分を返す
		return paths[ paths.length - 2 ];
	}, [ graphqlUrl ] );
};
