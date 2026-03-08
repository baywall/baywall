import fs from 'node:fs';
import path from 'node:path';
import { ModuleInfos } from 'license-checker';

/**
 * ライセンスファイルをコピーします。
 * @param packages
 * @param start
 * @param output
 */
export const copyLicenseFiles = ( packages: ModuleInfos, start: string, output: string ) => {
	// コピーしたファイル一覧を格納する配列
	const copiedFiles: string[] = [];

	for ( const name of Object.keys( packages ) ) {
		const src = packages[ name ].licenseFile;
		if ( ! src ) {
			console.warn( `[0E2ACD6A] No license file: ${ name }` );
			continue;
		}

		// コピー先ライセンスファイルのパスを取得
		const dst = path.join( output, name, path.basename( src ) );

		// ディレクトリが存在しない場合は作成
		const dir = path.dirname( dst );
		if ( ! fs.existsSync( dir ) ) {
			fs.mkdirSync( dir, { recursive: true } );
		}

		// ライセンスファイルをコピー
		if ( ! fs.existsSync( dst ) ) {
			fs.copyFileSync( src, dst );
			copiedFiles.push( dst );
		}
	}

	return copiedFiles;
};
