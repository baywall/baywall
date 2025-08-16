import { expect } from '@jest/globals';
import { usePostId } from './usePostId';
import { render } from '../../../../../jest-lib/render';
import { usePostIdFromDom } from '../../../lib/postId/usePostIdFromDom';
import { PostIdProvider } from './PostIdProvider';

jest.mock( '../../../lib/postId/usePostIdFromDom' );

const TEST_ID = 'F905B9E0';

const Sut: React.FC = () => {
	const postId = usePostId();
	return (
		<>
			<p data-testid={ TEST_ID }>{ String( postId ) }</p>
		</>
	);
};

describe( '[AD435E36] usePostId()', () => {
	/**
	 * postIdが取得できる場合のテスト
	 */
	it( '[8F2368E8] should return postId from DOM', () => {
		// ARRANGE
		( usePostIdFromDom as jest.Mock ).mockReturnValue( 42 );

		// ACT
		const { getByTestId } = render(
			<PostIdProvider>
				<Sut />
			</PostIdProvider>
		);
		const postId = getByTestId( TEST_ID ).textContent;

		// ASSERT
		expect( postId ).toBe( '42' );
	} );

	/**
	 * postIdが取得できない場合のテスト
	 */
	it( '[9C3C29BC] should throw an error when postId does not exist', () => {
		// ARRANGE
		( usePostIdFromDom as jest.Mock ).mockReturnValue( null );

		// ACT, ASSERT
		expect( () =>
			render(
				<PostIdProvider>
					<Sut />
				</PostIdProvider>
			)
		).toThrow( '[50F2A586]' );
	} );
} );
