const fs = require('fs');
const path = require('path');

// テスト・ストーリーはゾーン検査の対象外
const testOrStoryPath = '\\.(test|stories)\\.(ts|tsx)$';

// from と to で同一 feature かを突き合わせる必要があるため feature ごとにルールを生成する
const featureInternalRule = (name) => ({
	name: `feature-internal-of-${name}`,
	comment: `features/${name}/ の内部は自 feature 内からのみ参照でき、外部からは index.ts 経由でしか参照できない`,
	severity: 'error',
	from: { path: `^(?!src/features/${name}/)`, pathNot: testOrStoryPath },
	to: { path: `^src/features/${name}/(?!index\\.ts$)` },
});

const featureNames = fs
	.readdirSync(path.join(__dirname, 'src/features'), { withFileTypes: true })
	.filter((entry) => entry.isDirectory())
	.map((entry) => entry.name);

/** @type {import('dependency-cruiser').IConfiguration} */
module.exports = {
	forbidden: [
		{
			name: 'shared-not-to-features-or-app',
			comment: 'shared/ は features/ app/ へ依存しない',
			severity: 'error',
			from: { path: '^src/shared/', pathNot: testOrStoryPath },
			to: { path: '^src/(features|app)/' },
		},
		{
			name: 'features-not-to-app',
			comment: 'features/ は app/ へ依存しない',
			severity: 'error',
			from: { path: '^src/features/', pathNot: testOrStoryPath },
			to: { path: '^src/app/' },
		},
		...featureNames.map(featureInternalRule),
		{
			name: 'no-circular',
			comment: 'モジュール循環を禁止する',
			severity: 'error',
			from: {},
			to: { circular: true },
		},
	],
	options: {
		// src 外（node_modules・workspace パッケージ・コアモジュール）は外部依存として追跡しない
		doNotFollow: { path: '^(?!src/)' },
		tsPreCompilationDeps: false,
	},
};
