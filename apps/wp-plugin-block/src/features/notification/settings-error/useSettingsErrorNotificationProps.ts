import { BlockInitDataType, useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { SettingsErrorNotificationProps } from './SettingsErrorNotification';

export const useSettingsErrorNotificationProps = (): SettingsErrorNotificationProps => {
	return {
		isSettingsComplete: useIsSettingsComplete(),
	};
};

const useIsSettingsComplete = (): boolean | undefined => {
	return isSettingsComplete( useBlockInitDataQuery().data );
};

/**
 * 設定が完了しているかどうかを判定します
 * @param data
 */
export const isSettingsComplete = ( data: BlockInitDataType | undefined ): boolean | undefined => {
	if ( data === undefined ) {
		return undefined;
	} else {
		// 選択可能なネットワークカテゴリが存在する場合は完了とみなす
		return data.sellableNetworkCategories.length > 0;
	}
};
