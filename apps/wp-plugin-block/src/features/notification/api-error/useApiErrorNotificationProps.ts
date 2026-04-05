import { useBlockInitRawDataQuery } from '../../../query/useBlockInitRawDataQuery';
import { ApiErrorNotificationProps } from './ApiErrorNotification';

export const useApiErrorNotificationProps = (): ApiErrorNotificationProps => {
	return {
		error: useBlockInitRawDataQuery().error,
	};
};
