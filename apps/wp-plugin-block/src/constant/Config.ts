export class Config {
	private constructor() {} // eslint-disable-line no-useless-constructor

	// ※ PHP側と整合性を取ること
	/** ブロックの要素に指定するCSSのクラス名 */
	public static readonly BLOCK_CLASS_NAME = 'ae6cefc4-82d4-4220-840b-d74538ea7284';

	/**
	 * 販売価格（数量部分）入力の最大文字数
	 *
	 * 256bit符号なし整数の最大値は78桁だが、そんな値は現実的に使われないためそれよりも小さい値を指定する。
	 * 大抵のトークンは小数点以下が18桁に収まるので、その倍の桁数入力できれば十分。
	 */
	public static readonly SELLING_PRICE_AMOUNT_MAX_TEXT_LENGTH = 36;
}
