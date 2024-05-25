import constants from "@yamaneyuta/serendipity-constants/dist/constants-frontend.json";

export const getRestNonce = () => {
	return document.getElementById( constants.divId.nonceId )?.getAttribute( 'value' ) ?? null;
};
