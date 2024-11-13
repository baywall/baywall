import { useEffect, useState } from 'react';
import { useGraphQLUrl } from './useGraphQLUrl';

export const useTextDomain = () => {
	const [ textDomain, setTextDomain ] = useState< string | null | undefined >( undefined );
	const graphqlUrl = useGraphQLUrl();

	useEffect( () => {
		if ( graphqlUrl === undefined || graphqlUrl === null ) {
			setTextDomain( graphqlUrl );
		} else {
			// 一旦、hrefからoriginを削除したものを取得
			const originRemoved = graphqlUrl.replace( new URL( graphqlUrl ).origin, '' );
			const paths = originRemoved.split( '/' ).filter( ( path ) => path !== '' );

			// `/[domain-text]/graphql`の形式であることを確認
			if ( paths.length < 2 || paths[ paths.length - 1 ] !== 'graphql' ) {
				throw new Error( '[97289E73] Invalid graphql url. url: ' + graphqlUrl );
			}
			// textDomainにあたる部分をセット
			setTextDomain( paths[ paths.length - 2 ] );
		}
	}, [ graphqlUrl, setTextDomain ] );

	return textDomain;
};
