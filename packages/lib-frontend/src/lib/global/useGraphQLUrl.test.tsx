import { useGraphQLUrl } from './useGraphQLUrl';
import { PhpVarName } from '../repository/PhpVarName';
import { render } from '../../jest-lib';
import { PhpVar } from '../../types/PhpVar';

const TEST_ID = '61D2F697';

const Sut: React.FC = () => {
	const graphqlUrl = useGraphQLUrl();
	return (
		<>
			<p data-testid={ TEST_ID }>{ String( graphqlUrl ) }</p>
		</>
	);
};

const setGlobalVar = ( graphqlUrl: string, wpRestNonce: string ) => {
	const varName = new PhpVarName().get();
	const globalVar: PhpVar = {
		graphqlUrl,
		wpRestNonce,
	};
	( global as any )[ varName ] = globalVar;
};

describe( '[975F9282] useGraphQLUrl', () => {
	const cleanup = () => {
		( global as any )[ new PhpVarName().get() ] = undefined;
	};
	beforeEach( cleanup );
	afterEach( cleanup );

	/**
	 * GraphQLのURLが存在する場合のテスト
	 */
	it( '[BE9A4991] useGraphQLUrl - graphqlUrl exists', () => {
		// ARRANGE
		setGlobalVar( 'https://example.com/graphql', 'abcde01234' );

		// ACT
		const { getByTestId } = render( <Sut /> );

		// ASSERT
		const graphqlUrl = getByTestId( TEST_ID ).textContent;
		expect( graphqlUrl ).toBe( 'https://example.com/graphql' );
	} );

	/**
	 * GraphQLのURLが存在しない場合のテスト
	 */
	it( '[57BEB900] useGraphQLUrl - graphqlUrl does not exist', () => {
		// ARRANGE
		// Do nothing

		// ACT
		const { getByTestId } = render( <Sut /> );

		// ASSERT
		const graphqlUrl = getByTestId( TEST_ID ).textContent;
		expect( graphqlUrl ).toBe( 'null' ); // 値が存在しない場合はnullが返る
	} );
} );
