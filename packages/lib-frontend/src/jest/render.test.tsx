import { useMemo } from 'react';
import { render } from './render';

/**
 * 例外を発生させるコンポーネント
 */
const Sut: React.FC = () => {
	useMemo( () => {
		throw new Error( '[0B3B3F02]' );
	}, [] );
	return <></>;
};

/**
 * render関数呼び出し時に例外が発生するテスト
 */
it( 'should not throw an error', () => {
	expect( () => render( <Sut /> ) ).toThrow( '[0B3B3F02]' );
} );
