import { useMemo } from '@wordpress/element';
import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { isSettingsComplete } from './lib/isSettingsComplete';
import { SettingsErrorNotificationProps } from './SettingsErrorNotification';
import { UrlProvider } from '../../../lib/url/UrlProvider';

export const useSettingsErrorNotificationProps = (): SettingsErrorNotificationProps => {
	return {
		isSettingsComplete: useIsSettingsComplete(),
		urlProvider: useMemo( () => new UrlProvider(), [] ),
	};
};

const useIsSettingsComplete = (): boolean | undefined => {
	return isSettingsComplete( useBlockInitDataQuery().data );
};
