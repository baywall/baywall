export interface BlockNumberProps extends React.ComponentProps< 'input' > {}

const NUMERIC_INPUT_PATTERN = /^-?[0-9]*\.?[0-9]*$/;

/**
 * ブロックエディタで描画する数値入力コンポーネント
 * @param props
 */
export const BlockNumber: React.FC< BlockNumberProps > = ( props ) => {
	let { pattern, onChange, onCut, onKeyDownCapture, onPaste, style, ...rest } = props;

	// patternが指定されていない場合、数値のパターンを設定
	pattern = pattern ? pattern : NUMERIC_INPUT_PATTERN.source;

	return (
		// __experimentalNumberControl は ESLintで以下のエラーが発生するため、inputで実装
		// Usage of `__experimentalNumberControl` from `@wordpress/components` is not allowed.
		<input
			// type="number"
			type="text"
			inputMode="decimal"
			pattern={ pattern }
			onChange={ useOnChange( onChange, pattern ) }
			onCut={ useOnCut( onCut ) }
			onKeyDownCapture={ useOnKeyDownCapture( onKeyDownCapture ) }
			onPaste={ useOnPaste( onPaste, pattern ) }
			style={ { height: '40px', ...style } }
			{ ...rest }
		/>
	);
};

const useOnChange = ( onChange: BlockNumberProps[ 'onChange' ], pattern: string ): BlockNumberProps[ 'onChange' ] => {
	return ( e ) => {
		const value = e.target.value;
		// 数値のフォーマットの場合のみ、onChangeを呼び出す
		if ( new RegExp( pattern ).test( value ) ) {
			onChange?.( e );
		}
	};
};

const useOnCut = ( onCut: BlockNumberProps[ 'onCut' ] ): BlockNumberProps[ 'onCut' ] => {
	return ( e ) => {
		onCut?.( e );
		// カット処理を行ったとき、ブロックが削除されてしまうため
		// 親要素以降へのイベント伝播をキャンセルする
		e.stopPropagation();
	};
};

// `onKeyDown`では、左右の方向キーで次のコントロールにフォーカスが移る挙動になったため、`onKeyDownCapture`で制御しています。
const useOnKeyDownCapture = (
	onKeyDownCapture: BlockNumberProps[ 'onKeyDownCapture' ]
): BlockNumberProps[ 'onKeyDownCapture' ] => {
	return ( e ) => {
		if (
			/^[0-9\.]$/.test( e.key ) ||
			/^F([1-9]|1[0-2])$/.test( e.key ) ||
			'-' === e.key ||
			[ 'ArrowDown', 'ArrowUp', 'ArrowLeft', 'ArrowRight' ].includes( e.key ) ||
			[ 'Backspace', 'Delete', 'Home', 'End', 'Tab', 'Enter', 'Escape' ].includes( e.key ) ||
			( e.ctrlKey && [ 'a', 'c', 'v', 'x', 'z', 'y' ].includes( e.key.toLowerCase() ) )
		) {
			onKeyDownCapture?.( e );
			e.stopPropagation(); // 親要素以降へのイベント伝播をキャンセル
		} else {
			e.preventDefault(); // 不正なキー入力をキャンセル
		}
	};
};

const useOnPaste = ( onPaste: BlockNumberProps[ 'onPaste' ], pattern: string ): BlockNumberProps[ 'onPaste' ] => {
	return ( e ) => {
		const paste = e.clipboardData.getData( 'text' );
		// 貼り付けた値が数値のフォーマットの場合のみ、onPasteを呼び出す
		if ( new RegExp( pattern ).test( paste ) ) {
			onPaste?.( e );

			// 貼り付け処理を行ったとき、ブロックが削除されて文字列が貼り付けられてしまうため
			// 親要素以降へのイベント伝播をキャンセルする
			e.stopPropagation();
		} else {
			e.preventDefault(); // 不正な貼り付けをキャンセル
		}
	};
};
