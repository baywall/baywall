import { BlockNumber, BlockNumberProps } from './BlockNumber';

const NON_NEGATIVE_NUMERIC_INPUT_PATTERN = /^[0-9]*\.?[0-9]*$/;

export interface NonNegativeNumberProps extends BlockNumberProps {}

export const NonNegativeNumber = ( props: NonNegativeNumberProps ) => {
	return (
		<BlockNumber
			{ ...props }
			pattern={ NON_NEGATIVE_NUMERIC_INPUT_PATTERN.source }
			onKeyDownCapture={ useOnKeyDownCapture( props.onKeyDownCapture ) }
		/>
	);
};

const useOnKeyDownCapture = (
	onKeyDownCapture: NonNegativeNumberProps[ 'onKeyDownCapture' ]
): NonNegativeNumberProps[ 'onKeyDownCapture' ] => {
	return ( e ) => {
		// マイナスの入力を防止
		// ※ その他のキーはBlockNumberのuseOnKeyDownCaptureで制御
		if ( e.key === '-' ) {
			e.preventDefault(); // 入力をキャンセル
		} else {
			onKeyDownCapture?.( e );
		}
	};
};
