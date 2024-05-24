import constants from '../../includes/assets/constants.json';

export const getRestNonce = () => {
	return (
		document
			.getElementById( constants.divId.nonceId )
			?.getAttribute( 'value' ) ?? null
	);
};
