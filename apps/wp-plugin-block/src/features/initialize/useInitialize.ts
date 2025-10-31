import { useEffect } from '@wordpress/element';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { Amount, NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { useSelectedNetworkCategoryIdState } from '../selling-network-category/hooks/useSelectedNetworkCategoryIdState';
import { useInputSellingPriceAmountState } from '../selling-price-amount/hooks/useInputSellingPriceAmountState';
import { useSelectedSellingPriceSymbolState } from '../selling-price-symbol/hooks/useSelectedSellingPriceSymbolState';

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
	const {
		attributes: { sellingNetworkCategoryId },
	} = useBlockEditProps();
	const { data } = useBlockInitDataQuery();
	const [ , setSelectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();

	useEffect( () => {
		if ( data === undefined ) {
			return; // データ取得前は何もしない
		}

		setSelectedNetworkCategoryId( ( prev ) => {
			if ( prev !== undefined ) {
				return prev; // 初期化済みの場合は何もしない
			}

			// Attributesに値がある場合はそれを優先して設定
			if ( sellingNetworkCategoryId !== null ) {
				return NetworkCategoryId.from( sellingNetworkCategoryId );
			}
			// 販売可能なネットワークが存在する場合は先頭のIDを、存在しない場合はnullを設定
			return data.sellableNetworkCategories[ 0 ]?.id ?? null;
		} );
	}, [ sellingNetworkCategoryId, data, setSelectedNetworkCategoryId ] );
};

/** 画面で入力されている販売価格（数量）を初期化します。 */
const useInitSellingPriceAmount = () => {
	const {
		attributes: { sellingAmount },
	} = useBlockEditProps();
	const { data } = useBlockInitDataQuery();
	const [ , setSellingPriceAmount ] = useInputSellingPriceAmountState();

	useEffect( () => {
		if ( data === undefined ) {
			return; // データ取得前は何もしない
		}

		// 初期化済みの場合は何もしない
		// Attributesに値がある場合はその値を、存在しない場合は初期値として0を設定
		setSellingPriceAmount( ( prev ) => ( prev !== undefined ? prev : Amount.from( sellingAmount ?? '0' ) ) );
	}, [ sellingAmount, data, setSellingPriceAmount ] );
};

/** 画面で選択されている販売価格（通貨シンボル）を初期化します。 */
const useInitSellingPriceSymbol = () => {
	const {
		attributes: { sellingSymbol },
	} = useBlockEditProps();
	const { data } = useBlockInitDataQuery();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const [ , setSellingPriceSymbol ] = useSelectedSellingPriceSymbolState();

	useEffect( () => {
		if ( data === undefined ) {
			return; // データ取得前は何もしない
		}

		setSellingPriceSymbol( ( prev ) => {
			if ( prev !== undefined ) {
				return prev; // 初期化済みの場合は何もしない
			} else if ( selectedNetworkCategoryId === undefined ) {
				return prev; // ネットワークカテゴリIDが未初期化の場合は何もしない
			} else if ( selectedNetworkCategoryId === null ) {
				return null; // ネットワークカテゴリIDがnullの場合はnullを設定
			}

			if ( sellingSymbol !== null ) {
				return Symbol.from( sellingSymbol ); // Attributesに値がある場合はそれを優先して設定
			}

			// 先頭のシンボルを設定
			return (
				data.sellableNetworkCategories.find( ( c ) => c.id.equals( selectedNetworkCategoryId ) )
					?.sellableSymbols[ 0 ] ?? null
			);
		} );
	}, [ sellingSymbol, data, selectedNetworkCategoryId, setSellingPriceSymbol ] );
};
