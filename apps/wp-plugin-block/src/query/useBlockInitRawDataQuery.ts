import { usePostSettingQuery } from '../types/gql/generated';

/** ブロック初期データの型 */
export type BlockInitRawDataType = NonNullable< ReturnType< typeof useBlockInitRawDataQuery >[ 'data' ] >;

/**
 * ブロック初期化用データの生データ取得クエリ
 */
export const useBlockInitRawDataQuery = () => {
	return usePostSettingQuery();
};
