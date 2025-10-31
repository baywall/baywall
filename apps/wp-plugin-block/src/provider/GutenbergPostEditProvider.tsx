import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { BlockEditPropsProvider } from './block-edit-props/BlockEditPropsProvider';
import { BlockEditProps } from '@wordpress/blocks';
import { WidgetAttributes } from '../types/WidgetAttributes';
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
					<BlockEditPropsProvider blockEditProps={ blockEditProps }>{ children }</BlockEditPropsProvider>
				</Provider>
			</QueryClientProvider>
		</>
	);
};
