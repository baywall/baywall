import { Decimals, NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { useBlockInitRawDataQuery } from '../query/useBlockInitRawDataQuery';
import { Token } from '../value-object/Token';
import { useMemo } from 'react';

/** サイトに登録されているトークンを取得します */
export const useTokens = (): Token[] | undefined => {
	const { data } = useBlockInitRawDataQuery();

	return useMemo( () => {
		if ( data === undefined ) {
			return undefined;
		}

		return data.tokens.map( ( t ) => {
			return Token.from(
				NetworkCategoryId.from( t.chain.networkCategory.id ),
				Symbol.from( t.symbol ),
				Decimals.from( t.decimals )
			);
		} );
	}, [ data ] );
};
