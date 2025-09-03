import { useState } from '@wordpress/element';
import { type SellingPriceAmountProps } from './SellingPriceAmount';

export const useSellingPriceAmountProps = (): SellingPriceAmountProps => {
	const [ value, setValue ] = useState( '' ); // 画面で表示されている値

	return {
		value,
		onChange: useOnChange( setValue ),
	};
};

const useOnChange = (
	setValue: React.Dispatch< React.SetStateAction< string > >
): NonNullable< SellingPriceAmountProps[ 'onChange' ] > => {
	return ( e ) => {
		const value = e.target.value;
		setValue( value ); // コントロールに表示されている値（文字列）を更新
	};
};
