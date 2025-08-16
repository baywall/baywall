import assert from 'assert';
import { useContext } from 'react';
import { PostIdContext } from './PostIdProvider';

export const usePostId = () => {
	const context = useContext( PostIdContext );
	assert( context, '[4C5A23CD] Context is not found' );

	return context;
};
