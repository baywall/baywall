import { useMemo } from '@wordpress/element';
import { useBlockEditProps } from '../../../provider/block-edit-props/useBlockEditProps';
import { useInvalidDecimalsNotificationProps } from '../../selling-price-amount/components/selling-price-decimals-error/useInvalidDecimalsNotificationProps';

/**
 * 保存処理をロックする必要があるかどうかを返します。
 */
export const useShouldLockEditorSaving = (): boolean => {
	const { attributes } = useBlockEditProps();
	// ブロックの属性にnullが含まれている場合、ロック(保存できないように)する
	const attributesLock = useMemo(
		() => Object.values( attributes ).some( ( value ) => value === null ),
		[ attributes ]
	);

	/** 入力された金額の小数点以下桁数でエラーが発生しているかどうかを取得 */
	const isDecimalsError = useInvalidDecimalsNotificationProps().isError;

	return attributesLock || isDecimalsError;
};
