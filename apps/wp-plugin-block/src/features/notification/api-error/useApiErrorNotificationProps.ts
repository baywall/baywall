import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { ApiErrorNotificationProps } from './ApiErrorNotification';

export const useApiErrorNotificationProps = (): ApiErrorNotificationProps => {
	return {
		error: useBlockInitDataQuery().error,
	};
};
