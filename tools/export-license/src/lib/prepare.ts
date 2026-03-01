import fs from 'node:fs';
import path from 'node:path';

export const prepare = async () => {
	await prepareWeb3Onboard();
};

/**
 * Web3 OnboardにLICENSEファイルが同梱されていないので、GitHubから直接取得して配置する
 */
const prepareWeb3Onboard = async () => {
	const projectName = '@web3-onboard';
	if ( ! fs.existsSync( `node_modules/${ projectName }` ) ) {
		// web3-onboardパッケージをインストールしていない場合は処理抜け
		return;
	}

	const packages = [ 'core', 'injected-wallets', 'react' ];
	const licenseFileUrl = 'https://raw.githubusercontent.com/thirdweb-dev/web3-onboard/refs/heads/develop/LICENSE';
	const licenseFileName = licenseFileUrl.split( '/' ).pop();
	let licenseText: string | undefined;

	for ( const pkg of packages ) {
		const licenseFilePath = path.join( `node_modules/${ projectName }/${ pkg }/${ licenseFileName }` );

		// すでにLICENSEファイルが存在する場合はスキップ
		if ( fs.existsSync( licenseFilePath ) ) {
			continue;
		}

		// LICENSEファイルの内容が未取得の場合はGitHubから取得
		if ( licenseText === undefined ) {
			const response = await fetch( licenseFileUrl );
			if ( ! response.ok ) {
				throw new Error( '[DD58E669] Failed to fetch Web3 Onboard LICENSE file' );
			}
			licenseText = await response.text();
		}

		// LICENSEファイルを配置
		fs.writeFileSync( licenseFilePath, licenseText );
	}
};
