import { useState, useEffect } from 'react';
import { WpRestApi } from '../classes/WpRestApi';

export const useWpRestApi = (): WpRestApi | undefined => {
	const [ wpApi, setWpApi ] = useState< WpRestApi | undefined >( undefined );

	useEffect( () => {
		WpRestApi.getInstance().then( ( wpApi ) => {
			setWpApi( wpApi );
		} );
	}, [] );

	return wpApi;
};
