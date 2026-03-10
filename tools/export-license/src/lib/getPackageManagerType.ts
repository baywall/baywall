import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';

export type PackageManagerType = { isNpm: boolean; isComposer: boolean };

export const getPackageManagerType = ( start: string ): PackageManagerType => {
	if ( fs.statSync( start ).isFile() ) {
		const isNpm = path.basename( start ) === 'package.json';
		const isComposer = path.basename( start ) === 'composer.lock';
		assert(
			[ isNpm, isComposer ].filter( ( v ) => v ).length === 1,
			`[8666FE8A] Invalid argument. start: ${ start }`
		);
		return {
			isNpm,
			isComposer,
		};
	}

	const result: PackageManagerType = {
		isNpm: fs.existsSync( path.join( start, 'package.json' ) ),
		isComposer: fs.existsSync( path.join( start, 'composer.lock' ) ),
	};
	assert(
		Object.values( result ).filter( ( v ) => v ).length === 1,
		`[D4A3B7E7] Invalid argument. start: ${ start }`
	);

	return result;
};
