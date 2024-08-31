import { useWpRestNonce } from './useWpRestNonce';
import { getRestPhpVarName } from './getRestPhpVarName';
import { render } from '../../jest';

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
	const varName = getRestPhpVarName();
	( global as any )[ varName ] = {
		graphqlUrl,
		wpRestNonce,
	};
};

describe( '[0ABFAF3C] useWpRestNonce', () => {
	const cleanup = () => {
		// document.head.innerHTML = '';
		( global as any )[ getRestPhpVarName() ] = undefined;
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
} );
