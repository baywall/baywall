import React from 'react';
import { SelectControl } from '@wordpress/components';
import { type SelectControlProps } from '@wordpress/components/build-types/select-control/types';

export type SellingSymbolSelectProps = Extract< SelectControlProps, { multiple?: false } >;

/**
 * 販売価格の通貨シンボル選択コンポーネント
 * @param props
 */
export const SellingSymbolSelect: React.FC< SellingSymbolSelectProps > = ( props ) => {
	return <SelectControl { ...props } />;
};
