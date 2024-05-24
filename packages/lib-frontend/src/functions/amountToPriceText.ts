import { BigNumber, BigNumberish, ethers } from 'ethers';

export const amountToPriceText = (
	amount: BigNumberish,
	decimals: number
): string => {
	amount = ethers.BigNumber.from( amount );
	if ( decimals < 0 ) {
		throw new Error( '{5D9167DA-49A0-482A-9DFD-23C1688FE198}' );
	} else if ( decimals === 0 || amount.eq( BigNumber.from( 0 ) ) ) {
		//  小数点以下桁数が0の場合はそのまま文字列にして返す。
		//  amountが0の場合も、そのまま文字列にしたもの(0)を返す。
		return amount.toString();
	}

	//  小数点を入れるため、事前に先頭に0を追加したテキストを作成。
	let amountTextTmp = '0'.repeat( decimals ) + amount.toString();
	//  小数点を入れる。
	amountTextTmp =
		amountTextTmp.slice( 0, -decimals ).replace( /^0+/, '' ) +
		'.' +
		amountTextTmp.slice( -decimals ).replace( /0+$/, '' );

	//  小数点で開始している場合は0を付与。
	if ( amountTextTmp.startsWith( '.' ) ) {
		amountTextTmp = '0' + amountTextTmp;
	}
	//  小数点で終了している時は小数点を削除。
	if ( amountTextTmp.endsWith( '.' ) ) {
		amountTextTmp = amountTextTmp.slice( 0, -1 );
	}

	return amountTextTmp;
};

/**
 * 数量、小数点以下桁数、ロケール、通貨記号を指定して、価格の文字列を返す。
 * @param amount
 * @param decimals
 * @param locales
 * @param symbol
 * @returns
 * TODO: 順序入れ替え。最後にlocales
 */
export const amountToLocalizedPriceText = (
	amount: BigNumberish,
	decimals: number,
	locales: string,
	symbol: string
) => {
	const priceText = amountToPriceText( amount, decimals );

	try {
		const formattedPrice = new Intl.NumberFormat( locales, {
			style: 'currency',
			currency: symbol,
		} ).format( Number( priceText ) );

		if ( formattedPrice.startsWith( symbol ) ) {
			return priceText + ' ' + symbol;
		} else {
			return formattedPrice;
		}
	} catch ( e: any ) {
		if (
			typeof e.message === 'string' &&
			e.message.startsWith( 'Invalid currency code' )
		) {
			//	小数点の記号を取得
			const decimalSeparator =
				Intl.NumberFormat( locales ).formatToParts( 1.1 )[ 1 ].value;
			//	通貨コードが無効な場合

			//	整数部分をUSDに変換
			const integerUsdFormat = new Intl.NumberFormat( locales, {
				style: 'currency',
				currency: 'USD',
			} )
				.format( Number( priceText.split( '.' )[ 0 ] ) )
				.split( decimalSeparator )[ 0 ];
			//	USDの通貨記号を取得
			const usdSymbol = new Intl.NumberFormat( locales, {
				style: 'currency',
				currency: 'USD',
			} )
				.format( 0 )
				.replace( /\d/g, '' )
				.replace( decimalSeparator, '' )
				.trim();

			//	フォーマット済みの価格。一旦桁区切りが行われた後の整数部分のみを設定
			let formattedPrice = integerUsdFormat
				.replace( usdSymbol, '' )
				.trim();
			//	小数点以下がある場合、小数点以下を取得して後ろに追加
			if ( priceText.includes( '.' ) ) {
				//
				formattedPrice +=
					decimalSeparator + priceText.split( '.' )[ 1 ];
			}

			//	通貨記号を後ろに付与して返す
			return formattedPrice + ' ' + symbol;
		} else {
			throw e;
		}
	}
};
