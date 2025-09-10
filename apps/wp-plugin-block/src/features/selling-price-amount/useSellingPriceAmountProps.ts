import { useEffect, useState } from '@wordpress/element';
import { useLogger } from '@serendipity/lib-frontend';
import { Amount } from '@serendipity/lib-value-object';
import { type SellingPriceAmountProps } from './SellingPriceAmount';
import { useSellingPriceAmount } from '../../provider/selling-price-amount/useSellingPriceAmount';

export const useSellingPriceAmountProps = (): SellingPriceAmountProps => {
	const [ value, setValue ] = useState< string | undefined >( undefined ); // 画面で表示されている値

	const { sellingPriceAmount, setSellingPriceAmount } = useSellingPriceAmount();
	// 画面の値が未初期化の時、コンテキストの値で初期化する
	useEffect( () => {
		if ( value === undefined && sellingPriceAmount !== undefined ) {
			if ( sellingPriceAmount === null ) {
				// ここは通らない
				throw new Error( '[B9E7EF57] Invalid selling price amount value.' );
			} else {
				setValue( sellingPriceAmount.value );
			}
		}
	}, [ value, setValue, sellingPriceAmount, setSellingPriceAmount ] );

	return {
		value,
		onChange: useOnChange( setValue ),
	};
};

const useOnChange = (
	setValue: React.Dispatch< React.SetStateAction< string | undefined > >
): NonNullable< SellingPriceAmountProps[ 'onChange' ] > => {
	const { setSellingPriceAmount } = useSellingPriceAmount();
	const logger = useLogger();

	return ( e ) => {
		const value = e.target.value;
		setValue( value ); // コントロールに表示されている値（文字列）を更新
		try {
			setSellingPriceAmount( Amount.from( value ) ); // コンテキストに保存する値（Amount）を更新
		} catch ( err ) {
			setSellingPriceAmount( null ); // 変換できない場合はnullをセット
			logger.error( err );
		}
	};
};
