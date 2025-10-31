import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { SellingPriceSymbolProvider } from './selling-price-symbol/SellingPriceSymbolProvider';
import { BlockEditPropsProvider } from './block-edit-props/BlockEditPropsProvider';
import { BlockEditProps } from '@wordpress/blocks';
import { WidgetAttributes } from '../types/WidgetAttributes';
import { SellingPriceAmountProvider } from './selling-price-amount/SellingPriceAmountProvider';
import { Provider } from 'jotai';

// アクティブになったときは再読みしない
const client = new QueryClient( {
	defaultOptions: {
		queries: {
			staleTime: Infinity,
		},
	},
} );

type GutenbergPostEditProviderProps = {
	children: React.ReactNode;
	blockEditProps: BlockEditProps< WidgetAttributes >;
};

export const GutenbergPostEditProvider: React.FC< GutenbergPostEditProviderProps > = ( {
	blockEditProps,
	children,
} ) => {
	return (
		<>
			<QueryClientProvider client={ client }>
				<Provider>
					<BlockEditPropsProvider blockEditProps={ blockEditProps }>
						{ /* ウィジェットの状態を保持 */ }
						<SellingPriceAmountProvider>
							<SellingPriceSymbolProvider>{ children }</SellingPriceSymbolProvider>
						</SellingPriceAmountProvider>
					</BlockEditPropsProvider>
				</Provider>
			</QueryClientProvider>
		</>
	);
};
