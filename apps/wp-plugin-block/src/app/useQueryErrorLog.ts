import { useEffect } from 'react';
import { useLogger } from '@baywall/lib-frontend';
import { useBlockInitRawDataQuery } from '../shared/api/block-init/useBlockInitRawDataQuery';

/**
 * ブロック初期データ取得クエリのエラーをログ出力します
 *
 * クエリフック自身にログ出力を持たせない（購読インスタンス数だけ重複出力されるため）。
 */
export const useQueryErrorLog = () => {
	const logger = useLogger();
	const { error } = useBlockInitRawDataQuery();

	useEffect(() => {
		if (error) {
			logger.error('[DD08DAA7]', error);
		}
	}, [logger, error]);
};
