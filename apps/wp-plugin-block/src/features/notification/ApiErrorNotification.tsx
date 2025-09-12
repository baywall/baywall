import { useMemo } from '@wordpress/element';
import { NoticeList } from '@wordpress/components';
import { useLogger } from '@serendipity/lib-frontend';
import { TextProvider } from '../../lib/i18n/TextProvider';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';

type Notices = React.ComponentProps< typeof NoticeList >[ 'notices' ];

type ApiErrorNotificationProps = Omit< React.ComponentProps< typeof NoticeList >, 'notices' >;

/**
 * 初期データ取得時のAPIエラー通知
 * @param props
 */
export const ApiErrorNotification: React.FC< ApiErrorNotificationProps > = ( props ) => {
	const { isError, error } = useBlockInitDataQuery();
	const logger = useLogger();

	const notices: Notices = useMemo( () => {
		if ( isError === false ) {
			// エラーでない場合は何も表示しない
			return [];
		}

		logger.error( '[0E9ECDBB]', error );
		if ( error instanceof Error ) {
			logger.error( '[CDDFE276]', error.name );
			logger.error( '[F9C3ABC9]', error.message );
			logger.error( '[2C896DFD]', error.stack );
		}

		let message = new TextProvider().unknownErrorMessage;
		if ( error instanceof Error ) {
			message = error.message;
		} else if ( ( error as any ).toString === 'function' ) {
			message = ( error as any ).toString();
		}

		const notice: Notices[ number ] = {
			id: 'cd890884-9b16-4b09-8d8b-435b71bea425', // 適当なID
			status: 'error',
			isDismissible: false,
			content: message,
		};
		return [ notice ];
	}, [ logger, isError, error ] );

	return <NoticeList { ...props } notices={ notices } />;
};
