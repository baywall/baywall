import { render } from '@testing-library/react';
import { composeStories } from '@storybook/react';
import * as stories from './BlockNumber.stories';

/**
 * BlockNumber.stories.tsxのテスト
 */
describe( '[B3C23BD7] BlockNumber', () => {
	it( '[90BF53D8] Default', async () => {
		const { Default } = composeStories( stories );
		const { container } = render( <Default /> );
		await Default.play!( { canvasElement: container } );
	} );

	it( '[5A114145] Disabled', async () => {
		const { Disabled } = composeStories( stories );
		const { container } = render( <Disabled /> );
		await Disabled.play!( { canvasElement: container } );
	} );
} );
