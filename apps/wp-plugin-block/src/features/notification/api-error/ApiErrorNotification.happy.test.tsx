import { render } from '@testing-library/react';
import { ApiErrorNotification } from './ApiErrorNotification';

describe( '[Happy] ApiErrorNotification', () => {
	const dataSet = [
		{ error: null, expectedRender: false }, // エラーがnullの場合は表示無し
		{ error: undefined, expectedRender: false }, // エラーがundefinedの場合は表示無し
		{ error: new Error( 'Test error message' ), expectedRender: true }, // Errorオブジェクトがある場合は表示あり
		{ error: 'String error', expectedRender: true }, // 文字列エラーがある場合は表示あり
	];
	dataSet.forEach( ( { error, expectedRender } ) => {
		it( `[1D7704D9] error: ${ error }`, () => {
			// ACT
			const { container } = render( <ApiErrorNotification error={ error } /> );

			// ASSERT
			if ( expectedRender ) {
				expect( container.firstChild ).not.toBeNull();
				expect( container.textContent ).toContain( String( error ) );
			} else {
				expect( container.firstChild ).toBeNull();
				expect( container.textContent ).not.toContain( String( error ) );
			}
		} );
	} );
} );
