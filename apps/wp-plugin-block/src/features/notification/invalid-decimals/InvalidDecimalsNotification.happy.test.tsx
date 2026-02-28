import { render } from '@testing-library/react';
import { InvalidDecimalsNotification } from './InvalidDecimalsNotification';
import { ScreenNotifier } from '../../../lib/gutenberg/notification/ScreenNotifier';

describe( '[Happy] InvalidDecimalsNotification', () => {
	/**
	 * モックのScreenNotifierを作成
	 */
	const createMockScreenNotifier = (): ScreenNotifier => {
		return {
			showWarnSnackbar: jest.fn(),
			hide: jest.fn(),
		} as unknown as ScreenNotifier;
	};

	const dataSet = [
		{ isError: true, expectedShowWarn: true }, // エラーがある場合は警告表示
		{ isError: false, expectedShowWarn: false }, // エラーがない場合は非表示
	];
	dataSet.forEach( ( { isError, expectedShowWarn } ) => {
		it( `[99825D7D] isError: ${ isError }`, () => {
			// ARRANGE
			const mockScreenNotifier = createMockScreenNotifier();

			// ACT
			const { container } = render(
				<InvalidDecimalsNotification isError={ isError } screenNotifier={ mockScreenNotifier } />
			);

			// ASSERT
			// コンポーネント自体は何も描画しない
			expect( container.firstChild ).toBeNull();

			if ( expectedShowWarn ) {
				expect( mockScreenNotifier.showWarnSnackbar ).toHaveBeenCalled();
			} else {
				expect( mockScreenNotifier.showWarnSnackbar ).not.toHaveBeenCalled();
			}
		} );
	} );

	it( '[DB714EC5] unmount', () => {
		// ARRANGE
		const mockScreenNotifier = createMockScreenNotifier();

		// ACT
		const { unmount } = render(
			<InvalidDecimalsNotification isError={ true } screenNotifier={ mockScreenNotifier } />
		);
		// クリーンアップ前の状態を確認
		expect( mockScreenNotifier.showWarnSnackbar ).toHaveBeenCalled();
		// アンマウント実行
		unmount();

		// ASSERT
		// アンマウント時にhideが呼ばれることを確認
		expect( mockScreenNotifier.hide ).toHaveBeenCalled();
	} );
} );
