import { useWpRestNonce } from './useWpRestNonce';
import { PhpVarName } from '../repository/PhpVarName';
import { render, renderHook } from '../../jest-lib';
import { RestPhpVar } from '../../types/RestPhpVar';

const TEST_ID = 'D7BE51B9';

const Sut: React.FC = () => {
	const nonce = useWpRestNonce();
	return (
		<>
			<p data-testid={ TEST_ID }>{ String( nonce ) }</p>
		</>
	);
};

const setGlobalVar = ( graphqlUrl: string, wpRestNonce: string ) => {
	const varName = new PhpVarName().get();
	const globalVar: RestPhpVar = {
		graphqlUrl,
		wpRestNonce,
	};
	( global as any )[ varName ] = globalVar;
};

describe( '[0ABFAF3C] useWpRestNonce', () => {
	const cleanup = () => {
		// document.head.innerHTML = '';
		( global as any )[ new PhpVarName().get() ] = undefined;
	};
	beforeEach( cleanup );
	afterEach( cleanup );

	/**
	 * nonceが存在する場合のテスト
	 */
	it( '[3897B3F5] useWpRestNonce - nonce exists', () => {
		// ARRANGE
		setGlobalVar( 'https://example.com/graphql', 'abcde01234' );

		// ACT
		const { getByTestId } = render( <Sut /> );

		// ASSERT
		const nonce = getByTestId( TEST_ID ).textContent;
		expect( nonce ).toBe( 'abcde01234' );
	} );

	/**
	 * nonceが存在しない場合のテスト
	 */
	it( '[064C4858] useWpRestNonce - nonce does not exist', () => {
		// ARRANGE
		// Do nothing

		// ACT
		const { getByTestId } = render( <Sut /> );

		// ASSERT
		const nonce = getByTestId( TEST_ID ).textContent;
		expect( nonce ).toBe( 'null' ); // 値が存在しない場合はnullが返る
	} );

	/**
	 * nonceが存在しない場合のテスト(renderHookを使った場合)
	 */
	it( '[0A701BB5] useWpRestNonce - nonce does not exist', () => {
		// ARRANGE
		// Do nothing

		// ACT
		const { result } = renderHook( () => useWpRestNonce() );

		// ASSERT
		expect( result.current ).toBeNull();
	} );
} );
