import { render } from '@testing-library/react';
import { AmountErrorNotification } from './AmountErrorNotification';
import { ScreenNotifier } from '../../../lib/gutenberg/notification/ScreenNotifier';

describe( '[Happy] AmountErrorNotification', () => {
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
		it( `[4F04F871] isError: ${ isError }`, () => {
			// ARRANGE
			const mockScreenNotifier = createMockScreenNotifier();

			// ACT
			const { container } = render(
				<AmountErrorNotification isError={ isError } screenNotifier={ mockScreenNotifier } />
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

	it( '[633D8858] unmount', () => {
		// ARRANGE
		const mockScreenNotifier = createMockScreenNotifier();

		// ACT
		const { unmount } = render(
			<AmountErrorNotification isError={ true } screenNotifier={ mockScreenNotifier } />
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
