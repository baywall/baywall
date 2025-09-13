import fs from 'node:fs';
import path from 'node:path';
import { ModuleInfos } from 'license-checker';

export const exportLicenseMeta = async ( packages: ModuleInfos, start: string, output: string, metaFile: string ) => {
	// metaFileから見たoutputの相対パスを取得
	const metaFileRelative = path.relative( path.dirname( metaFile ), output ) || '.';

	// packages内のパスをmetaFileからの相対パスに変換
	const result: ModuleInfos = {};
	for ( const name of Object.keys( packages ) ) {
		// ※ 出力先はcopyLicenseFiles内の処理と一致させること
		const src = packages[ name ].licenseFile!;
		const dst = path.join( metaFileRelative, name, path.basename( src ) );

		result[ name ] = {
			...packages[ name ],
			path: undefined, // pathは不要なので削除
			licenseFile: dst,
		};
	}

	// metaFileのディレクトリが存在しない場合は作成
	const dir = path.dirname( metaFile );
	if ( ! fs.existsSync( dir ) ) {
		fs.mkdirSync( dir, { recursive: true } );
	}

	// ライセンス情報を出力
	fs.writeFileSync( metaFile, JSON.stringify( result, null, 2 ) );
};
