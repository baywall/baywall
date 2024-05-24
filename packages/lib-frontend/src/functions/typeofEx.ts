/**
 * nullを`null'、クラスオブジェクトをクラス名で返します。
 * その他プリミティブ型はtypeofで返します。
 * @param value
 * @returns 型名
 */
export const typeofEx = ( value: any ): string => {
	if ( value === null ) {
		return 'null';
	}

	const typeStr = typeof value;
	return typeStr === 'object' ? value.constructor.name : typeStr;
};
