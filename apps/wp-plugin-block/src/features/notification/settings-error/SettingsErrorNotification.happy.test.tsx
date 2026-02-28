import { render } from '@testing-library/react';
import { SettingsErrorNotification } from './SettingsErrorNotification';
import { UrlProvider } from '../../../lib/url/UrlProvider';

describe( '[Happy] SettingsErrorNotification', () => {
	/**
	 * モックのUrlProviderを作成
	 */
	const createMockUrlProvider = (): UrlProvider => {
		return {
			dashboard: {
				toString: jest.fn().mockReturnValue( 'https://example.com/dashboard' ),
			},
		} as unknown as UrlProvider;
	};

	const dataSet = [
		{ isSettingsComplete: true, expectedRender: false }, // 設定が完了していればエラー表示無し
		{ isSettingsComplete: false, expectedRender: true }, // 設定が未完了であればエラー表示あり
		{ isSettingsComplete: undefined, expectedRender: false }, // 設定が未取得の場合は表示無し
	];
	dataSet.forEach( ( { isSettingsComplete, expectedRender } ) => {
		it( `[3A4555E1] isSettingsComplete: ${ isSettingsComplete }`, () => {
			// ARRANGE
			const mockUrlProvider = createMockUrlProvider();

			// ACT
			const { container } = render(
				<SettingsErrorNotification isSettingsComplete={ isSettingsComplete } urlProvider={ mockUrlProvider } />
			);

			// ASSERT
			if ( expectedRender ) {
				expect( container.firstChild ).not.toBeNull();
			} else {
				expect( container.firstChild ).toBeNull();
			}
			expect( mockUrlProvider.dashboard.toString ).toHaveBeenCalled();
		} );
	} );
} );
