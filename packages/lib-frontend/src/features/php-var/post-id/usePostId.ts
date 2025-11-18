import { PostId } from '@serendipity/lib-value-object';
import { useMemo } from 'react';
import { getPostId } from './getPostId';

/** 投稿IDを取得します */
export const usePostId = (): PostId | null | undefined => {
	return useMemo( () => getPostId(), [] );
};
