import { usePhpVar } from './_usePhpVar';

/**
 * REST APIにアクセスする際のNonceを取得します。
 */
export const useWpRestNonce = () => {
	const restPhpVar = usePhpVar();
	return restPhpVar !== null ? restPhpVar.wpRestNonce : null;
};
