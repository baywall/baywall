import { BlockEditProps } from '@wordpress/blocks';
import { WidgetAttributes } from '../../types/WidgetAttributes';
import { useSelectedSellingNetworkCategoryId } from '../../provider/selected-selling-network-id/useSelectedSellingNetworkCategoryId';
import { useEffect } from 'react';
import { NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { useSelectedSellingSymbol } from '../../provider/selected-selling-symbol/useSelectedSellingSymbol';

export const useSyncWidgetAttributes = ( blockEditorProps: BlockEditProps< WidgetAttributes > ) => {
	// 画面の状態をAttributesから初期化
	useLoadAttributes( blockEditorProps );

	// 画面の状態をAttributesに保存
	useSaveAttributes( blockEditorProps );
};

/**
 * 読みこんだAttributesから画面の状態を初期化します
 *
 * ※ 各コンポーネントで値の初期化（自動選択）処理があるが、GraphQLの通信後に行うため、こちらの処理の方が速い。
 *    タイミングによる不整合が起きる可能性は低いと判断している。
 * @param blockEditorProps
 */
const useLoadAttributes = ( blockEditorProps: BlockEditProps< WidgetAttributes > ) => {
	const { attributes, setAttributes } = blockEditorProps;
	const { selectedSellingNetworkCategoryId, setSelectedSellingNetworkCategoryId } =
		useSelectedSellingNetworkCategoryId();
	const { selectedSellingSymbol, setSelectedSellingSymbol } = useSelectedSellingSymbol();

	// ネットワークカテゴリIDの初期化
	useEffect( () => {
		if ( selectedSellingNetworkCategoryId === undefined && attributes.sellingNetworkCategoryId !== null ) {
			setSelectedSellingNetworkCategoryId( NetworkCategoryId.from( attributes.sellingNetworkCategoryId ) );
		}
	}, [ attributes, setAttributes, selectedSellingNetworkCategoryId, setSelectedSellingNetworkCategoryId ] );

	// 販売価格の通貨シンボル初期化
	useEffect( () => {
		if ( selectedSellingSymbol === undefined && attributes.sellingSymbol !== null ) {
			setSelectedSellingSymbol( Symbol.from( attributes.sellingSymbol ) );
		}
	}, [ attributes, setAttributes, selectedSellingSymbol, setSelectedSellingSymbol ] );
};

const useSaveAttributes = ( blockEditorProps: BlockEditProps< WidgetAttributes > ) => {
	const { attributes, setAttributes } = blockEditorProps;
	const { selectedSellingNetworkCategoryId } = useSelectedSellingNetworkCategoryId();
	const { selectedSellingSymbol } = useSelectedSellingSymbol();

	// ネットワークカテゴリIDの保存
	useEffect( () => {
		if(selectedSellingNetworkCategoryId === undefined || selectedSellingSymbol === undefined) {
			// まだ初期化されていない場合は保存しない
			return;
		}

		const newAttributes: Partial< WidgetAttributes > = {
			sellingNetworkCategoryId: selectedSellingNetworkCategoryId?.value || null,
			sellingSymbol: selectedSellingSymbol?.value || null,
		};

		setAttributes(newAttributes);

	}, [ attributes, setAttributes, selectedSellingNetworkCategoryId, selectedSellingSymbol ] );
}
