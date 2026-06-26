import { Config } from '../../constant/Config.js';

export const useTextDomain = (): string | null | undefined => {
	return Config.TEXT_DOMAIN;
};
