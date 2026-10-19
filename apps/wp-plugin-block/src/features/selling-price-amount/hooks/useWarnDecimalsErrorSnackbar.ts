import { useTranslation } from 'react-i18next';
import { useWarnSnackbar } from '../../../shared/lib/wp/useWarnSnackbar';
import { useIsDecimalsError } from './useIsDecimalsError';

/** 販売価格の小数点以下桁数が不正な場合に警告スナックバーを表示します */
export const useWarnDecimalsErrorSnackbar = (): void => {
	const { t } = useTranslation();
	const isDecimalsError = useIsDecimalsError();

	useWarnSnackbar({
		id: '8bbdd02d-67ea-4b1b-8896-a32925dfca74', // 通知に使用する一意のID（適当な値）
		message: t('too_many_decimal_places_message'),
		isVisible: isDecimalsError,
	});
};
