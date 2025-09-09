import { useEffect } from '@wordpress/element';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';
import { useSellingNetworkCategoryId } from '../../provider/selling-network-category-id/useSellingNetworkCategoryId';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { Amount, NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { useSellingPriceAmount } from '../../provider/selling-price-amount/useSellingPriceAmount';
import { useSellingPriceSymbol } from '../../provider/selling-price-symbol/useSellingPriceSymbol';

export const useInitialize = (): void => {
	useInitSellingNetworkCategoryId(); // ネットワークカテゴリIDの初期化
	useInitSellingPriceAmount(); // 販売価格（数量）の初期化
	useInitSellingPriceSymbol(); // 販売価格（通貨シンボル）の初期化
};

/** 画面で選択されているネットワークカテゴリIDを初期化します。 */
const useInitSellingNetworkCategoryId = () => {
	const { attributes } = useBlockEditProps();
	const { data } = useBlockInitDataQuery();
	const { sellingNetworkCategoryId, setSellingNetworkCategoryId } = useSellingNetworkCategoryId();

	useEffect( () => {
		if ( sellingNetworkCategoryId !== undefined ) {
			return; // 初期化済みの場合は何もしない
		} else if ( data === undefined ) {
			return; // データ取得前は何もしない
		}

		if ( attributes.sellingNetworkCategoryId !== null ) {
			// Attributesに値がある場合はそれを優先して設定
			setSellingNetworkCategoryId( NetworkCategoryId.from( attributes.sellingNetworkCategoryId ) );
			return;
		}

		if ( data.sellableNetworkCategories.length === 0 ) {
			// 販売可能なネットワークが存在しない場合はnullを設定
			setSellingNetworkCategoryId( null );
		} else {
			// 販売可能なネットワークが存在する場合は先頭のIDを設定
			setSellingNetworkCategoryId( data.sellableNetworkCategories[ 0 ].id );
		}
	}, [ attributes.sellingNetworkCategoryId, data, sellingNetworkCategoryId, setSellingNetworkCategoryId ] );
};

/** 画面で入力されている販売価格（数量）を初期化します。 */
const useInitSellingPriceAmount = () => {
	const { attributes } = useBlockEditProps();
	const { data } = useBlockInitDataQuery();
	const { sellingPriceAmount, setSellingPriceAmount } = useSellingPriceAmount();

	useEffect( () => {
		if ( sellingPriceAmount !== undefined ) {
			return; // 初期化済みの場合は何もしない
		} else if ( data === undefined ) {
			return; // データ取得前は何もしない
		}

		if ( attributes.sellingAmount !== null ) {
			// Attributesに値がある場合はそれを優先して設定
			setSellingPriceAmount( Amount.from( attributes.sellingAmount ) );
		} else {
			setSellingPriceAmount( Amount.from( '0' ) ); // デフォルトは0を設定
		}
	}, [ attributes.sellingAmount, data, sellingPriceAmount, setSellingPriceAmount ] );
};

/** 画面で選択されている販売価格（通貨シンボル）を初期化します。 */
const useInitSellingPriceSymbol = () => {
	const { attributes } = useBlockEditProps();
	const { data } = useBlockInitDataQuery();
	const { sellingNetworkCategoryId } = useSellingNetworkCategoryId();
	const { sellingPriceSymbol, setSellingPriceSymbol } = useSellingPriceSymbol();

	useEffect( () => {
		if ( sellingPriceSymbol !== undefined ) {
			return; // 初期化済みの場合は何もしない
		} else if ( data === undefined || sellingNetworkCategoryId === undefined ) {
			return; // データ取得前やネットワークカテゴリ初期化前は何もしない
		}

		if ( sellingNetworkCategoryId === null ) {
			// ネットワークカテゴリIDがnullの場合は通貨シンボルもnullを設定
			setSellingPriceSymbol( null );
			return;
		}

		if ( attributes.sellingSymbol !== null ) {
			// Attributesに値がある場合はそれを優先して設定
			setSellingPriceSymbol( Symbol.from( attributes.sellingSymbol ) );
			return;
		}

		// 現在選択されているネットワークで販売可能な通貨シンボル一覧を取得
		const filteredCurrencies = data.sellableCurrencies.filter( ( currency ) =>
			currency.networkCategoryId.equals( sellingNetworkCategoryId )
		);
		if ( filteredCurrencies.length === 0 ) {
			// 販売可能な通貨シンボルが存在しない場合はnullを設定
			setSellingPriceSymbol( null );
		} else {
			// 取得した通貨シンボル一覧の先頭のシンボルを設定
			setSellingPriceSymbol( filteredCurrencies[ 0 ].symbol );
		}
	}, [ attributes.sellingSymbol, data, sellingNetworkCategoryId, sellingPriceSymbol, setSellingPriceSymbol ] );
};
