import { useState } from '@wordpress/element';
import { type SellingPriceAmountProps } from './SellingPriceAmount';
import { useSellingPriceAmount } from '../../provider/selling-price-amount/useSellingPriceAmount';
import { Amount } from '@serendipity/lib-value-object';

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
	const { setSellingPriceAmount } = useSellingPriceAmount();

	return ( e ) => {
		const value = e.target.value;
		setValue( value ); // コントロールに表示されている値（文字列）を更新
		setSellingPriceAmount( Amount.from( value ) ); // コンテキストに保存する値（Amount）を更新
	};
};
