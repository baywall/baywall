import { BlockEditProps } from '../shared/types/BlockEditProps';
import { useEffect } from 'react';
import { WidgetAttributes } from '../shared/types/WidgetAttributes';
import { useBlockEditProps } from '../shared/lib/block-editor/block-edit-props/useBlockEditProps';
import { useSelectedNetworkCategoryIdState } from '../features/selling-network-category';
import { useSelectedSellingPriceSymbolState } from '../features/selling-price-symbol';
import { useInputSellingPriceAmount } from '../features/selling-price-amount';

export const useSyncWidgetAttributes = () => {
	const blockEditorProps = useBlockEditProps();

	// 画面の状態をAttributesに保存
	useSaveAttributes(blockEditorProps);
};

const useSaveAttributes = (blockEditorProps: BlockEditProps<WidgetAttributes>) => {
	const { attributes, setAttributes } = blockEditorProps;
	const [selectedNetworkCategoryId] = useSelectedNetworkCategoryIdState();
	const sellingPriceAmount = useInputSellingPriceAmount();
	const [sellingPriceSymbol] = useSelectedSellingPriceSymbolState();

	// ネットワークカテゴリIDの保存
	useEffect(() => {
		if (selectedNetworkCategoryId === undefined) {
			return; // まだ初期化されていない場合は保存しない
		}

		// Attributesの値とContextの値が異なる場合のみ更新する
		if (
			attributes.sellingNetworkCategoryId !==
			(selectedNetworkCategoryId === null ? null : selectedNetworkCategoryId.value)
		) {
			setAttributes({
				...attributes,
				sellingNetworkCategoryId: selectedNetworkCategoryId === null ? null : selectedNetworkCategoryId.value,
			});
		}
	}, [attributes, setAttributes, selectedNetworkCategoryId]);

	// 販売価格の保存
	useEffect(() => {
		if (sellingPriceAmount === undefined) {
			return; // まだ初期化されていない場合は保存しない
		}

		// Attributesの値とContextの値が異なる場合のみ更新する
		if (attributes.sellingAmount !== (sellingPriceAmount === null ? null : sellingPriceAmount.value)) {
			setAttributes({
				...attributes,
				sellingAmount: sellingPriceAmount === null ? null : sellingPriceAmount.value,
			});
		}
	}, [attributes, setAttributes, sellingPriceAmount]);

	// 販売価格の通貨シンボルの保存
	useEffect(() => {
		if (sellingPriceSymbol === undefined) {
			return; // まだ初期化されていない場合は保存しない
		}

		// Attributesの値とContextの値が異なる場合のみ更新する
		if (attributes.sellingSymbol !== (sellingPriceSymbol === null ? null : sellingPriceSymbol.value)) {
			setAttributes({
				...attributes,
				sellingSymbol: sellingPriceSymbol === null ? null : sellingPriceSymbol.value,
			});
		}
	}, [attributes, setAttributes, sellingPriceSymbol]);
};
