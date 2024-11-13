import { renderHook } from '../../jest-lib';
import { useTextDomain } from './useTextDomain';
import { useGraphQLUrl } from './useGraphQLUrl';

jest.mock( './useGraphQLUrl' );

describe( '[55E68644] useTextDomain', () => {
	/**
	 * 正常なURLの場合、テキストドメインが取得できることを確認
	 */
	it( '[A91EECB5] wp-json', async () => {
		// ARRANGE
		( useGraphQLUrl as jest.Mock ).mockReturnValue( 'http://example.com/wp-json/my-text-domain/graphql' );

		// ACT
		const { result } = renderHook( () => useTextDomain() );

		// ASSERT
		expect( result.current ).toBe( 'my-text-domain' );
	} );

	/**
	 * 正常なURLの場合、テキストドメインが取得できることを確認
	 */
	it( '[16313617] index.php', async () => {
		// ARRANGE
		( useGraphQLUrl as jest.Mock ).mockReturnValue(
			'http://example.com/index.php?rest_route=/my-text-domain/graphql'
		);

		// ACT
		const { result } = renderHook( () => useTextDomain() );

		// ASSERT
		expect( result.current ).toBe( 'my-text-domain' );
	} );

	/**
	 * GraphQLのURLが取得できなかった場合、nullが返ることを確認
	 */
	it( '[64E79395] url is null', async () => {
		// ARRANGE
		( useGraphQLUrl as jest.Mock ).mockReturnValue( null );

		// ACT
		const { result } = renderHook( () => useTextDomain() );

		// ASSERT
		expect( result.current ).toBeNull();
	} );

	/**
	 * 不正なURL(`graphql`で終わらない)の場合、例外が発生することを確認
	 */
	it( '[495DFB22] invalid url - not ends with `graphql`', async () => {
		// ARRANGE
		( useGraphQLUrl as jest.Mock ).mockReturnValue( 'http://example.com/foo/bar' );

		// ACT, ASSERT
		expect( () => renderHook( () => useTextDomain() ) ).toThrow( '[97289E73]' );
	} );

	/**
	 * 不正なURL(テキストドメインが含まれない)の場合、例外が発生することを確認
	 */
	it( '[0D304623] invalid url', async () => {
		// ARRANGE
		( useGraphQLUrl as jest.Mock ).mockReturnValue( 'http://example.com/graphql' );

		// ACT, ASSERT
		expect( () => renderHook( () => useTextDomain() ) ).toThrow( '[97289E73]' );
	} );
} );
