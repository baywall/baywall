import { Amount, Decimals } from '@serendipity/lib-value-object';

/**
 * 小数点以下桁数が最大桁数以下であるかどうかを判定します
 * @param amount
 * @param maxDecimalPlaces
 */
export const isValidDecimalPlaces = ( amount: Amount, maxDecimalPlaces: Decimals ): boolean => {
	const amountDecimalsValue = amount.value.split( '.' )[ 1 ]?.length ?? 0;
	return amountDecimalsValue <= maxDecimalPlaces.value;
};
