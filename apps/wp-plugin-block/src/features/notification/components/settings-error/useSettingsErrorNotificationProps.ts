import { isSettingsComplete } from '../../utils/isSettingsComplete';
import { SettingsErrorNotificationProps } from './SettingsErrorNotification';
import { getDashboardUrl } from '../../../../shared/lib/url';
import { useBlockInitRawDataQuery } from '../../../../shared/api/block-init/useBlockInitRawDataQuery';

export const useSettingsErrorNotificationProps = (): SettingsErrorNotificationProps => {
	return {
		isSettingsComplete: isSettingsComplete(useBlockInitRawDataQuery().data),
		dashboardUrl: getDashboardUrl().toString(),
	};
};
