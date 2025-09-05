import { BlockEditProps } from '@wordpress/blocks';
import { useEffect } from '@wordpress/element';
import { WidgetAttributes } from '../../types/WidgetAttributes';
import { useSellingNetworkCategoryId } from '../../provider/selling-network-category-id/useSellingNetworkCategoryId';
import { useSellingPriceSymbol } from '../../provider/selling-price-symbol/useSellingPriceSymbol';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';
import { useSellingPriceAmount } from '../../provider/selling-price-amount/useSellingPriceAmount';

export const useSyncWidgetAttributes = () => {
	const blockEditorProps = useBlockEditProps();

	// 画面の状態をAttributesに保存
	useSaveAttributes( blockEditorProps );
};

const useSaveAttributes = ( blockEditorProps: BlockEditProps< WidgetAttributes > ) => {
	const { attributes, setAttributes } = blockEditorProps;
	const { sellingNetworkCategoryId } = useSellingNetworkCategoryId();
	const { sellingPriceAmount } = useSellingPriceAmount();
	const { sellingPriceSymbol } = useSellingPriceSymbol();

	// ネットワークカテゴリIDの保存
	useEffect( () => {
		if (
			sellingNetworkCategoryId === undefined ||
			sellingPriceAmount === undefined ||
			sellingPriceSymbol === undefined
		) {
			// まだ初期化されていない場合は保存しない
			return;
		}

		const newAttributes: Partial< WidgetAttributes > = {
			sellingNetworkCategoryId: sellingNetworkCategoryId?.value || null,
			sellingAmount: sellingPriceAmount?.value || '0',
			sellingSymbol: sellingPriceSymbol?.value || null,
		};

		setAttributes( newAttributes );
	}, [ attributes, setAttributes, sellingNetworkCategoryId, sellingPriceAmount, sellingPriceSymbol ] );
};
