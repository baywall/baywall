import { BigNumber } from 'ethers';

/*
const priceTextToAmount = (
	priceText: string,
	decimals: number
): BigNumber => {
	//	パラメータチェック。
	//	この関数では、小数点の記号はピリオドのみ許可。
	if (!priceText.match(/^\d+(?:\.\d+)?$/)) {
		console.error("priceText: ", priceText);
		throw new Error("{6611FCE2-AA92-423A-9E3F-504F69F0C3E1}");
	}

	//	小数点以下を0埋めした文字列を生成。
	//	例: priceText: 1.01, decimals: 6 → 1.01000000
	priceText = [
		priceText,
		priceText.includes(".") ? "" : ".",
		"0".repeat(decimals),
	].join("");

	//	整数部分と、小数点以下をdecimalsの数だけ抽出したものを結合したものをBigNumberで返す。
	return BigNumber.from(
		priceText.split(".")[0] + priceText.split(".")[1].slice(0, decimals)
	);
};
*/

/**
 * HTMLのinput要素のvalueから、amountとdecimalsを返します。
 * @param inputValue
 * @param locales
 */
export const inputValueToAmount = ( inputValue: string, locales?: string ) => {
	if ( inputValue.length === 0 ) {
		return {
			amount: BigNumber.from( 0 ),
			decimals: 0,
		};
	}

	const decimalSeparator =
		Intl.NumberFormat( locales ).formatToParts( 1.1 )[ 1 ].value;

	return {
		amount: BigNumber.from( inputValue.replace( decimalSeparator, '' ) ), //	数量は小数点を無視してBigNumberに変換したもの。
		decimals: inputValue.split( decimalSeparator )[ 1 ]?.length ?? 0, //	小数点以下桁数は小数点で分割した後の2番目の要素の文字数。
	};
};

/**
 * ローカライズされた価格テキストをamountとdecimalsに変換します。
 * @param localizedPriceText
 * @param locales
 * @param symbol
 */
export const localizedPriceTextToAmount = (
	localizedPriceText: string,
	locales: string,
	symbol: string
) => {
	const decimalSeparator =
		Intl.NumberFormat( locales ).formatToParts( 1.1 )[ 1 ].value;
	const thousandSeparator =
		Intl.NumberFormat( locales ).formatToParts( 11111 )[ 1 ].value;

	//	通貨記号を削除
	const priceText = ( () => {
		if ( localizedPriceText.includes( symbol ) ) {
			//	通貨記号がそのまま含まれる場合
			return localizedPriceText.replace( symbol, '' ).trim();
		} else {
			//	通貨記号が変換された状態で含まれる場合
			// 通貨記号を取得
			const formattedSymbol = new Intl.NumberFormat( locales, {
				style: 'currency',
				currency: symbol,
			} )
				.format( 0 )
				.replace( /\d/g, '' )
				.replace( decimalSeparator, '' )
				.trim();
			return localizedPriceText.replace( formattedSymbol, '' ).trim();
		}
	} )();
	//	小数点以下の桁数を取得
	const decimals = priceText.split( decimalSeparator )[ 1 ]?.length ?? 0;
	//	桁区切りと小数点を削除
	const amount = priceText
		.replace( new RegExp( thousandSeparator, 'g' ), '' )
		.replace( decimalSeparator, '' );

	return {
		amount: BigNumber.from( amount ),
		decimals,
	};
};
