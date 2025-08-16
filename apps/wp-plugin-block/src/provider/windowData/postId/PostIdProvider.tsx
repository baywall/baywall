import assert from 'assert';
import { createContext } from 'react';
import { usePostIdFromDom } from '../../../lib/postId/usePostIdFromDom';

type PostIdType = ReturnType< typeof _usePostId >;

export const PostIdContext = createContext< PostIdType | undefined >( undefined );

const _usePostId = (): number => {
	const postId = usePostIdFromDom();

	// 投稿編集画面ではpostIdが取得できる
	assert( postId !== null, '[50F2A586] postId is null' );

	return postId;
};

type PostIdProviderProps = {
	children: React.ReactNode;
};

export const PostIdProvider: React.FC< PostIdProviderProps > = ( { children } ) => {
	const value = _usePostId();
	return <PostIdContext.Provider value={ value }>{ children }</PostIdContext.Provider>;
};
