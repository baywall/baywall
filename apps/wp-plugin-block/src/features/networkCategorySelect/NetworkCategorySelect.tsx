import { useCallback } from 'react';
import { NetworkCategoryId } from '@serendipity/lib-value-object';
import { BlockSelect, BlockSelectOption } from '../../components/deprecated/BlockSelect';

export interface NetworkCategorySelectProps {
	value: NetworkCategoryId | null | undefined;
	networkCategories: NetworkCategoryId[] | null | undefined;
	onChange: React.ChangeEventHandler< HTMLSelectElement >;
	disabled?: boolean;
}
export const NetworkCategorySelect: React.FC< NetworkCategorySelectProps > = ( {
	value,
	networkCategories: networkCategories,
	onChange,
	disabled,
} ) => {
	const getNetworkCategoryText = useGetNetworkCategoryTextCallback();
	return (
		<BlockSelect value={ value?.value ?? '' } onChange={ onChange } disabled={ disabled }>
			{ value === null ? <BlockSelectOption>{ 'Select a network' }</BlockSelectOption> : null }
			{ value === undefined ? <BlockSelectOption>{ 'Loading...' }</BlockSelectOption> : null }
			{ networkCategories?.map( ( nc ) => (
				<BlockSelectOption key={ nc.value } value={ nc.value }>
					{ getNetworkCategoryText( nc ) }
				</BlockSelectOption>
			) ) }
		</BlockSelect>
	);
};

const useGetNetworkCategoryTextCallback = () => {
	return useCallback( ( networkCategoryId: NetworkCategoryId ) => {
		if ( networkCategoryId.isMainnet ) {
			return 'Mainnet';
		} else if ( networkCategoryId.isTestnet ) {
			return 'Testnet';
		} else if ( networkCategoryId.isPrivatenet ) {
			return 'Privatenet';
		} else {
			throw new Error( `[91C84522] Unknown network category: ${ networkCategoryId.value }` );
		}
	}, [] );
};
