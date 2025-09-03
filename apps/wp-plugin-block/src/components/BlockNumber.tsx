export interface BlockNumberProps extends React.ComponentProps< 'input' > {}

/**
 * ブロックエディタで描画する数値入力コンポーネント
 * @param props
 */
export const BlockNumber: React.FC< BlockNumberProps > = ( props ) => {
	const { onChange, onCut, onKeyDownCapture, onPaste, ...rest } = props;

	return (
		// __experimentalNumberControl は ESLintで以下のエラーが発生するため、inputで実装
		// Usage of `__experimentalNumberControl` from `@wordpress/components` is not allowed.
		<input
			// type="number"
			type="text"
			inputMode="decimal"
			pattern="[0-9]*"
			onChange={ useOnChange( onChange ) }
			onCut={ useOnCut( onCut ) }
			onKeyDownCapture={ useOnKeyDownCapture( onKeyDownCapture ) }
			onPaste={ useOnPaste( onPaste ) }
			{ ...rest }
		/>
	);
};

const useOnChange = ( onChange: BlockNumberProps[ 'onChange' ] ): BlockNumberProps[ 'onChange' ] => {
	return ( e ) => {
		const value = e.target.value;
		// 数値のフォーマットの場合のみ、onChangeを呼び出す
		if ( /^-?[0-9]*\.?[0-9]*$/.test( value ) ) {
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

const useOnPaste = ( onPaste: BlockNumberProps[ 'onPaste' ] ): BlockNumberProps[ 'onPaste' ] => {
	return ( e ) => {
		onPaste?.( e );
		// 貼り付け処理を行ったとき、ブロックが削除されて文字列が貼り付けられてしまうため
		// 親要素以降へのイベント伝播をキャンセルする
		e.stopPropagation();
	};
};
