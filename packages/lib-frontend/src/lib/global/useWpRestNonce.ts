import { usePhpVar } from './_usePhpVar';

/**
 * REST APIにアクセスする際のNonceを取得します。
 */
export const useWpRestNonce = () => {
	const phpVar = usePhpVar();
	return phpVar !== null ? phpVar.wpRestNonce : null;
};
