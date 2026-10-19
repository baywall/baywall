import { defineConfig } from 'i18next-cli';

export default defineConfig({
	locales: ['en', 'ja'],
	extract: {
		input: 'src/**/*.{js,jsx,ts,tsx}',
		output: 'i18n/{{namespace}}.{{language}}.json',
		defaultNS: 'translation',
		keySeparator: false,
		nsSeparator: false,
		functions: ['t', '*.t'],
		transComponents: ['Trans'],
	},
});
