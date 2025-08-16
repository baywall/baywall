import { expect } from '@jest/globals';
import React from 'react';
import { render } from '../../../../jest-lib/render';
import { usePostIdFromDom } from './usePostIdFromDom';

const TEST_ID = '6CEC0231';

const Sut: React.FC = () => {
	const postId = usePostIdFromDom();
	return (
		<>
			<p data-testid={ TEST_ID }>{ String( postId ) }</p>
		</>
	);
};

/**
 * `usePostIdFromDom`のテスト
 */
describe( '[BA9B42CB] usePostIdFromDom()', () => {
	const cleanup = () => {
		document.body.innerHTML = '';
	};
	beforeEach( cleanup );
	afterEach( cleanup );

	it( '[8F73E331] should return postId from DOM', () => {
		// ARRANGE
		document.body.innerHTML = '<input type="hidden" id="post_ID" name="post_ID" value="42" />';

		// ACT
		const { getByTestId } = render( <Sut /> );

		// ASSERT
		const postId = getByTestId( TEST_ID ).textContent;
		expect( postId ).toBe( '42' );
	} );

	it( '[7AF2B0AE] should return null when post_ID does not exist', () => {
		// ARRANGE

		// ACT
		const { getByTestId } = render( <Sut /> );

		// ASSERT
		const postId = getByTestId( TEST_ID ).textContent;
		expect( postId ).toBe( 'null' );
	} );
} );
