import React from 'react';
import { SelectControl } from '@wordpress/components';
import { type SelectControlProps } from '@wordpress/components/build-types/select-control/types';

export type SellingNetworkCategorySelectProps = Extract< SelectControlProps, { multiple?: false } >;

/**
 * 販売ネットワークカテゴリ選択コンポーネント
 * @param props
 */
export const SellingNetworkCategorySelect: React.FC< SellingNetworkCategorySelectProps > = ( props ) => {
	return <SelectControl { ...props } />;
};
