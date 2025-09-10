import { usePostId } from './usePostId';
import { PhpVarNameProvider } from '../lib/php-var/PhpVarNameProvider';
import { renderHook } from '../jest-lib';
import { PhpVar } from '../types/PhpVar';

const setGlobalVar = ( postId: number | null ) => {
	const varName = new PhpVarNameProvider().get();
	const globalVar: PhpVar = {
		graphqlUrl: 'https://example.com/graphql',
		wpRestNonce: 'abcde01234',
		postId,
	};
	( global as any )[ varName ] = globalVar;
};

describe( '[D78FB2CE] usePostId', () => {
	const cleanup = () => {
		// document.head.innerHTML = '';
		( global as any )[ new PhpVarNameProvider().get() ] = undefined;
	};
	beforeEach( cleanup );
	afterEach( cleanup );

	/**
	 * 投稿IDが存在する場合のテスト
	 */
	it( '[43FA26C6] usePostId - postId exists', () => {
		// ARRANGE
		setGlobalVar( 42 );

		// ACT
		const { result } = renderHook( () => usePostId() );

		// ASSERT
		expect( result.current ).toEqual( 42 );
	} );

	/**
	 * 投稿IDが存在しない場合のテスト
	 */
	it( '[164C4EEB] usePostId - postId does not exist', () => {
		// ARRANGE
		setGlobalVar( null );

		// ACT
		const { result } = renderHook( () => usePostId() );

		// ASSERT
		expect( result.current ).toBeNull();
	} );
} );
