export const createRequestInit = (nonce: string): RequestInit => {
	return {
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': nonce,
		},
	} as RequestInit;
};
