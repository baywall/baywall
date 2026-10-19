export class Config {
	private constructor() {}

	/**
	 * 販売価格（数量部分）入力の最大文字数
	 *
	 * 256bit符号なし整数の最大値は78桁だが、そんな値は現実的に使われないためそれよりも小さい値を指定する。
	 * 大抵のトークンは小数点以下が18桁に収まるので、その倍の桁数入力できれば十分。
	 */
	public static readonly SELLING_PRICE_AMOUNT_MAX_TEXT_LENGTH = 36;
}
