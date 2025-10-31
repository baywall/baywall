import { useEffect } from '@wordpress/element';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';
import { useSellingNetworkCategoryId } from '../../provider/selling-network-category-id/useSellingNetworkCategoryId';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { Amount, NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { useSellingPriceAmount } from '../../provider/selling-price-amount/useSellingPriceAmount';
import { useSellingPriceSymbol } from '../../provider/selling-price-symbol/useSellingPriceSymbol';

/**
 * 初期化処理
 *
 * ※ useQueryのsuccessイベントで初期化を行った場合に
 *    以下の操作で初期化処理が実行されないため
 *    useEffectを用いて初期化処理を実施。
 *
 * - 画面でウィジェットを削除、再度追加した場合
 */
export const useInitialize = (): void => {
	useInitSellingNetworkCategoryId(); // ネットワークカテゴリIDの初期化
	useInitSellingPriceAmount(); // 販売価格（数量）の初期化
	useInitSellingPriceSymbol(); // 販売価格（通貨シンボル）の初期化
};

/** 画面で選択されているネットワークカテゴリIDを初期化します。 */
const useInitSellingNetworkCategoryId = () => {
	const { attributes } = useBlockEditProps();
	const { data } = useBlockInitDataQuery();
	const { setSellingNetworkCategoryId } = useSellingNetworkCategoryId();

	useEffect( () => {
		if ( data === undefined ) {
			return; // データ取得前は何もしない
		}

		setSellingNetworkCategoryId( ( prev ) => {
			if ( prev !== undefined ) {
				return prev; // 初期化済みの場合は何もしない
			}

			// Attributesに値がある場合はそれを優先して設定
			if ( attributes.sellingNetworkCategoryId !== null ) {
				return NetworkCategoryId.from( attributes.sellingNetworkCategoryId );
			}
			// 販売可能なネットワークが存在する場合は先頭のIDを、存在しない場合はnullを設定
			return data.sellableNetworkCategories[ 0 ]?.id ?? null;
		} );
	}, [ attributes.sellingNetworkCategoryId, data, setSellingNetworkCategoryId ] );
};

/** 画面で入力されている販売価格（数量）を初期化します。 */
const useInitSellingPriceAmount = () => {
	const { attributes } = useBlockEditProps();
	const { data } = useBlockInitDataQuery();
	const { setSellingPriceAmount } = useSellingPriceAmount();

	useEffect( () => {
		if ( data === undefined ) {
			return; // データ取得前は何もしない
		}

		// 初期化済みの場合は何もしない
		// Attributesに値がある場合はその値を、存在しない場合は初期値として0を設定
		setSellingPriceAmount( ( prev ) =>
			prev !== undefined ? prev : Amount.from( attributes.sellingAmount ?? '0' )
		);
	}, [ attributes.sellingAmount, data, setSellingPriceAmount ] );
};

/** 画面で選択されている販売価格（通貨シンボル）を初期化します。 */
const useInitSellingPriceSymbol = () => {
	const { attributes } = useBlockEditProps();
	const { data } = useBlockInitDataQuery();
	const { sellingNetworkCategoryId } = useSellingNetworkCategoryId();
	const { setSellingPriceSymbol } = useSellingPriceSymbol();

	useEffect( () => {
		if ( data === undefined || sellingNetworkCategoryId === undefined ) {
			return; // データ取得前やネットワークカテゴリ初期化前は何もしない
		}

		setSellingPriceSymbol( ( prev ) => {
			if ( prev !== undefined ) {
				return prev; // 初期化済みの場合は何もしない
			}

			if ( attributes.sellingSymbol !== null ) {
				return Symbol.from( attributes.sellingSymbol ); // Attributesに値がある場合はそれを優先して設定
			}

			// 先頭のシンボルを設定
			return data.sellableSymbols[ 0 ] ?? null;
		} );
	}, [ attributes.sellingSymbol, data, sellingNetworkCategoryId, setSellingPriceSymbol ] );
};
