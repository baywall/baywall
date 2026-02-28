import { Amount } from '@serendipity/lib-value-object';

/**
 * 入力された金額が不正かどうかを判定します
 * @param inputAmount
 */
export const isInvalidInputAmount = ( inputAmount: Amount | null | undefined ): boolean => {
	// nullの時だけ不正な値が入力されたとみなす（undefinedはロード中のためエラーとしない）
	return inputAmount === null;
};
