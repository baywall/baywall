import { useEffect } from '@wordpress/element';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { Amount, NetworkCategoryId } from '@serendipity/lib-value-object';
import { useSelectedNetworkCategoryIdState } from '../selling-network-category/hooks/useSelectedNetworkCategoryIdState';
import { useInputSellingPriceAmountState } from '../selling-price-amount/hooks/useInputSellingPriceAmountState';

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
	useInitSellingPriceAmount(); // 販売価格（数量）の初期化
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
