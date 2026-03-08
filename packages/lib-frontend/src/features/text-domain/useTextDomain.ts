import { Config } from '../../constant/Config';

export const useTextDomain = (): string | null | undefined => {
	return Config.TEXT_DOMAIN;
};
