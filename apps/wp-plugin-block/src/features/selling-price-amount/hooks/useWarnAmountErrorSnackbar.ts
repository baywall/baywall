import { useTranslation } from 'react-i18next';
import { useWarnSnackbar } from '../../../shared/lib/wp/useWarnSnackbar';
import { useInputSellingPriceAmount } from './useInputSellingPriceAmount';

/** 入力された販売価格の数量が不正な場合に警告スナックバーを表示します */
export const useWarnAmountErrorSnackbar = (): void => {
	const { t } = useTranslation();
	const inputAmount = useInputSellingPriceAmount();

	useWarnSnackbar({
		id: '8d75df90-3d6b-44c0-a690-19d861b0431c', // 通知に使用する一意のID（適当な値）
		message: t('invalid_price_entered_message') + t('enter_the_valid_price_message'),
		// nullの時だけ不正な値が入力されたとみなす（undefinedはロード中のためエラーとしない）
		isVisible: inputAmount === null,
	});
};
