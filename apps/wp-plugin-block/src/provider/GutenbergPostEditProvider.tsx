import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { SelectedSellingSymbolProvider } from './selected-selling-symbol/SelectedSellingSymbolProvider';
import { SelectedSellingNetworkCategoryIdProvider } from './selected-selling-network-id/SelectedSellingNetworkCategoryIdProvider';
import { BlockEditPropsProvider } from './block-edit-props/BlockEditPropsProvider';
import { BlockEditProps } from '@wordpress/blocks';
import { WidgetAttributes } from '../types/WidgetAttributes';

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
				<BlockEditPropsProvider blockEditProps={ blockEditProps }>
					{ /* ウィジェットの状態を保持 */ }
					<SelectedSellingNetworkCategoryIdProvider>
						<SelectedSellingSymbolProvider>{ children }</SelectedSellingSymbolProvider>
					</SelectedSellingNetworkCategoryIdProvider>
				</BlockEditPropsProvider>
			</QueryClientProvider>
		</>
	);
};
