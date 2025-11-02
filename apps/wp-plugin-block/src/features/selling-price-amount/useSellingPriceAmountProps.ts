import { atom, useAtom } from 'jotai';
import { useLogger } from '@serendipity/lib-frontend';
import { Amount } from '@serendipity/lib-value-object';
import { type SellingPriceAmountProps } from './SellingPriceAmount';
import { useInputSellingPriceAmountState } from './hooks/useInputSellingPriceAmountState';
import { useSavedSellingAmount } from '../widget-attributes/useSavedSellingAmount';
import { useEffect } from 'react';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';

// 画面で表示されている金額の文字列
const valueAtom = atom< string | undefined >( undefined );

export const useSellingPriceAmountProps = (): SellingPriceAmountProps => {
	useInitValue();

	return {
		value: useValue(),
		onChange: useOnChange(),
	};
};

const useInitValue = () => {
	const savedSellingAmount = useSavedSellingAmount();
	const [ value, setValue ] = useAtom( valueAtom );
	const [ , setInputSellingPriceAmount ] = useInputSellingPriceAmountState();

	useEffect( () => {
		if ( value !== undefined ) {
			return; // 既に値が設定されている場合は何もしない
		}
		const initAmount = savedSellingAmount ?? Amount.from( '0' );
		setValue( initAmount.value ); // 画面に表示されている値を設定
		setInputSellingPriceAmount( initAmount ); // コンテキストに保存する値を設定
	}, [ savedSellingAmount, value, setValue, setInputSellingPriceAmount ] );
};

const useValue = () => {
	const { data } = useBlockInitDataQuery();
	const [ value ] = useAtom( valueAtom );

	if ( data === undefined ) {
		return ''; // 他のコントロールがデータ取得まで何も表示されないので、それに合わせた制御
	}

	return value ?? '';
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
