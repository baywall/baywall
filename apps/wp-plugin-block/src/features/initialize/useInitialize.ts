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
};
