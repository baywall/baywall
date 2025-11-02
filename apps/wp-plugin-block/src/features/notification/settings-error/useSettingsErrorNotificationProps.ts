import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { isSettingsComplete } from './lib/isSettingsComplete';
import { SettingsErrorNotificationProps } from './SettingsErrorNotification';

export const useSettingsErrorNotificationProps = (): SettingsErrorNotificationProps => {
	return {
		isSettingsComplete: useIsSettingsComplete(),
	};
};

const useIsSettingsComplete = (): boolean | undefined => {
	return isSettingsComplete( useBlockInitDataQuery().data );
};
