import { BlockEditProps } from '@wordpress/blocks';
import { useEffect } from '@wordpress/element';
import { Amount, NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { WidgetAttributes } from '../../types/WidgetAttributes';
import { useSellingNetworkCategoryId } from '../../provider/selling-network-category-id/useSellingNetworkCategoryId';
import { useSellingPriceSymbol } from '../../provider/selling-price-symbol/useSellingPriceSymbol';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';
import { useSellingPriceAmount } from '../../provider/selling-price-amount/useSellingPriceAmount';

export const useSyncWidgetAttributes = () => {
	const blockEditorProps = useBlockEditProps();
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
	const { sellingNetworkCategoryId, setSellingNetworkCategoryId } = useSellingNetworkCategoryId();
	const { sellingPriceAmount, setSellingPriceAmount } = useSellingPriceAmount();
	const { sellingPriceSymbol, setSellingPriceSymbol } = useSellingPriceSymbol();

	// ネットワークカテゴリIDの初期化
	useEffect( () => {
		if ( sellingNetworkCategoryId === undefined && attributes.sellingNetworkCategoryId !== null ) {
			setSellingNetworkCategoryId( NetworkCategoryId.from( attributes.sellingNetworkCategoryId ) );
		}
	}, [ attributes, setAttributes, sellingNetworkCategoryId, setSellingNetworkCategoryId ] );

	// 販売価格の金額初期化
	useEffect( () => {
		if ( sellingPriceAmount === undefined ) {
			if ( attributes.sellingAmount === null ) {
				setSellingPriceAmount( Amount.from( '0' ) );
			} else {
				setSellingPriceAmount( Amount.from( attributes.sellingAmount ) );
			}
		}
	}, [ attributes, setAttributes, sellingPriceAmount, setSellingPriceAmount ] );

	// 販売価格の通貨シンボル初期化
	useEffect( () => {
		if ( sellingPriceSymbol === undefined && attributes.sellingSymbol !== null ) {
			setSellingPriceSymbol( Symbol.from( attributes.sellingSymbol ) );
		}
	}, [ attributes, setAttributes, sellingPriceSymbol, setSellingPriceSymbol ] );
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
