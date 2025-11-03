import { Amount, Decimals } from '@serendipity/lib-value-object';
import { isValidDecimalPlaces } from '../lib/isValidDecimalPlaces';

/**
 * 入力された金額の小数点以下が最大桁数を超えてエラーの状態になっているかどうかを取得します。
 * @param inputAmount
 * @param maxDecimals
 */
export const useIsDecimalPlacesError = (
	inputAmount: Amount | null | undefined,
	maxDecimals: Decimals | null | undefined
) => {
	if ( ! inputAmount || ! maxDecimals ) {
		// 値が取得できない場合はエラーの状態でないという判定にする
		return false;
	}
	return ! isValidDecimalPlaces( inputAmount, maxDecimals );
};
