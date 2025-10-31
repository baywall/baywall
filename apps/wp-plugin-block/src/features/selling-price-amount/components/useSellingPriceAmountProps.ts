import { atom, useAtom } from 'jotai';
import { useLogger } from '@serendipity/lib-frontend';
import { Amount } from '@serendipity/lib-value-object';
import { type SellingPriceAmountProps } from './SellingPriceAmount';
import { useInputSellingPriceAmountState } from '../hooks/useInputSellingPriceAmountState';

// 画面で表示されている金額の文字列
const valueAtom = atom< string | undefined >( undefined );

export const useSellingPriceAmountProps = (): SellingPriceAmountProps => {
	return {
		value: useValue(),
		onChange: useOnChange(),
	};
};

const useValue = () => {
	const [ value ] = useAtom( valueAtom );
	const [ inputSellingPriceAmount ] = useInputSellingPriceAmountState();

	return value !== undefined ? value : inputSellingPriceAmount?.value ?? '';
};

const useOnChange = (): NonNullable< SellingPriceAmountProps[ 'onChange' ] > => {
	const logger = useLogger();
	const [ , setValue ] = useAtom( valueAtom );
	const [ , setInputSellingPriceAmount ] = useInputSellingPriceAmountState();

	return ( e ) => {
		const value = e.target.value;
		setValue( value ); // コントロールに表示されている値（文字列）を更新
		try {
			setInputSellingPriceAmount( Amount.from( value ) ); // コンテキストに保存する値（Amount）を更新
		} catch ( err ) {
			setInputSellingPriceAmount( null ); // 変換できない場合はnullをセット
			logger.error( err );
		}
	};
};
