import { useBlockInitRawDataQuery } from '../../../../shared/api/block-init/useBlockInitRawDataQuery';
import { ApiErrorNotificationProps } from './ApiErrorNotification';

export const useApiErrorNotificationProps = (): ApiErrorNotificationProps => {
	return {
		error: useBlockInitRawDataQuery().error,
	};
};
