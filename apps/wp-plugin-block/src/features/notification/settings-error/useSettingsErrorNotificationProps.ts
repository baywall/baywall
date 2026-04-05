import { useMemo } from '@wordpress/element';
import { isSettingsComplete } from './lib/isSettingsComplete';
import { SettingsErrorNotificationProps } from './SettingsErrorNotification';
import { UrlProvider } from '../../../lib/url/UrlProvider';
import { useBlockInitRawDataQuery } from '../../../query/useBlockInitRawDataQuery';

export const useSettingsErrorNotificationProps = (): SettingsErrorNotificationProps => {
	return {
		isSettingsComplete: isSettingsComplete( useBlockInitRawDataQuery().data ),
		urlProvider: useMemo( () => new UrlProvider(), [] ),
	};
};
