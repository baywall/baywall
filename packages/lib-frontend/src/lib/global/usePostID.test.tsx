import { usePostID } from './usePostID';
import { RestPhpVarName } from '../repository/RestPhpVarName';
import { renderHook } from '../../jest-lib';
import { RestPhpVar } from '../../types/RestPhpVar';

const setGlobalVar = ( postID: number | undefined ) => {
	const varName = new RestPhpVarName().get();
	const globalVar: RestPhpVar = {
		graphqlUrl: 'https://example.com/graphql',
		wpRestNonce: 'abcde01234',
		postID,
	};
	( global as any )[ varName ] = globalVar;
};

describe( '[D78FB2CE] usePostID', () => {
	const cleanup = () => {
		// document.head.innerHTML = '';
		( global as any )[ new RestPhpVarName().get() ] = undefined;
	};
	beforeEach( cleanup );
	afterEach( cleanup );

	/**
	 * 投稿IDが存在する場合のテスト
	 */
	it( '[43FA26C6] usePostID - postID exists', () => {
		// ARRANGE
		setGlobalVar( 42 );

		// ACT
		const { result } = renderHook( () => usePostID() );

		// ASSERT
		expect( result.current ).toEqual( 42 );
	} );

	/**
	 * 投稿IDが存在しない場合のテスト
	 */
	it( '[164C4EEB] usePostID - postID does not exist', () => {
		// ARRANGE
		setGlobalVar( undefined );

		// ACT
		const { result } = renderHook( () => usePostID() );

		// ASSERT
		expect( result.current ).toBeNull();
	} );
} );
