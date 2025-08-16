import { PostIdProvider } from './postId/PostIdProvider';

type WindowDataProviderProps = {
	children: React.ReactNode;
};

export const WindowDataProvider: React.FC< WindowDataProviderProps > = ( { children } ) => {
	return <PostIdProvider>{ children }</PostIdProvider>;
};
