#!/usr/bin/env node
import { main } from '../lib/entrypoint/main';
import { prepare } from '../lib/prepare';

( async () => {
	await prepare();

	const { copiedFiles } = await main();
	for ( const file of copiedFiles ) {
		console.log( file );
	}
} )();
