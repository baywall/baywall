import axios, { AxiosRequestConfig } from 'axios';
import constants from "@yamaneyuta/serendipity-constants/dist/constants-frontend.json";
import { getRestNonce } from '../functions/getRestNonce';
import { getPostIdFromDom } from '../functions/getPostIdFromDom';
import { getPhpCommonVar } from '../functions/getPhpVar';
import { NetworkType } from '../types/NetworkType';

//	TODO: JSONのキーをCamelCaseにする。

type VersionResponse = {
	version: number;
};

type NonceResponse = {
	view_nonce: string;
	admin_nonce?: string | undefined; //	管理者が管理画面からアクセスしている時のみ返される。
	editor_nonce?: string | undefined; //	編集者が管理画面(投稿編集画面)からアクセスしている時のみ返される。
};

export type PurchasedContentResponse = {
	paid_content: string;
	crc32: string;
};

type RpcUrlsResponse = {
	value: { [ chain_id: string ]: string | null };
};

type ActiveNetworkTypeTxConfirmationsResponse = {
	value: { [ chainId: string ]: number | null };
};

type ConnectableChainsInfoResponse = {
	chainId: number;
	isSellable: boolean;
}[];

type BlockchainPayableSymbolsInfoResponse = {
	symbols: string[];
	decimals: number[];
	tokenTypes: number[];
	isPaused: boolean[];
	// blockNumber: string;
	// chainId: number;
};

type ChainSettingsPostData = {
	deployed_chain_ids: number[];
};

type ChainSettingsResponse = {
	chain_settings: {
		chain_id: number;
		rpc_url: string | null;
		site_id: string | null;
		is_active: boolean;
		payable_symbols: string[];
	}[];
};

type SeedForSiteIdResponse = {
	seed: string;
};

export type PurchaseSignatureResponse = {
	post_id: number;
	chain_id: number;
	symbol: string;
	signature_hex: string;
	ticket_id_hex: string;
	to_message: string;
	to_signature_hex: string;
	amount_hex: string;
};

export type PriceConversionResponse = {
	price_conversion_chain_id: number;
};

export type PostSettingResponse = {
	is_setting_exists: boolean;
	selling_paused: boolean;
	selling_amount_hex: string;
	selling_decimals: number;
	selling_symbol: string | null;
	affiliate_percent_amount_hex: string;
	affiliate_percent_decimals: number;
};

type ViewingInfoResponse = {
	/** 投稿ID */
	post_ID: number;

	/** 販売価格 */
	selling_amount_hex: string;
	selling_decimals: number;

	/** 販売価格の通貨 */
	selling_symbol: string | null;

	/** 各チェーンでの設定 */
	all_payable_symbols: {
		[ chain_id: string ]: string[];
	};

	/** サーバーで現在使用されている署名用アドレス */
	primary_signer_address_hex: string;

	/** サーバーでの使用されたことのある署名用アドレス */
	signer_addresses: string[];

	/** アフィリエイト報酬割合  */
	affiliate_ratio_hex: string;

	/** 投稿タイトル */
	post_title: string;

	/** 投稿サムネイル画像のURL */
	post_thumbnail_url: string | null;

	/** 有料記事の文字数 */
	paid_characters_num: number;

	/** 有料記事部分に含まれる画像の数 */
	paid_images_num: number;
};

type InstalledFontResponse = {
	fonts: {
		[ fontName: string ]: {
			normal: string;
			bold: string;
			italic: string;
			bold_italic: string;
		};
	};
};

type SalesHistory = {
	ticket_created_at_unix: number;
	chain_id: number;
	post_id: number;
	post_title: string;
	selling_amount_hex: string;
	selling_decimals: number;
	selling_symbol: string;
	payment_symbol: string;
	payment_decimals: number;
	profit_amount_hex: string;
	fee_amount_hex: string;
	affiliate_amount_hex: string;
	from_address: string;
	to_address: string;
	affiliate_address: string;
	transaction_hash_hex: string;
};
export type SalesHistoriesResponse = SalesHistory[];

/**
 * nonceを使って行う操作種別
 */
const REST_API_ACTION_TYPE = {
	VIEW: 'view',
	ADMIN: 'admin',
	EDITOR: 'editor',
} as const;
type RestApiActionType = ( typeof REST_API_ACTION_TYPE )[ keyof typeof REST_API_ACTION_TYPE ];

/**
 * WordPressのREST APIのURLを組み立てるクラス
 */
class WpApiUrlAssembler {
	public constructor( siteAddress: string, apiRootPaths: string[] ) {
		this._siteAddress = siteAddress;
		this._apiRootPaths = apiRootPaths;
	}
	private _initialized: boolean = false;
	private _siteAddress: string;
	private _apiRootPaths: string[];

	//	アクセス可能なAPIのルートパス。
	private _availableApiRootPath: string | undefined | null = undefined;
	//	サーバーから取得したAPIバージョン。
	private _apiVersion: number | undefined | null = undefined;

	public getUrl( path: string ) {
		if ( this._initialized === false ) {
			throw new Error( '{9128B06B-F916-4F12-BA98-AE136511F2CB}' );
		} else if (
			this._availableApiRootPath === null ||
			this._availableApiRootPath === undefined ||
			this._apiVersion === null ||
			this._apiVersion === undefined
		) {
			console.error( '_availableApiRootPath: ', this._availableApiRootPath, ', _apiVersion: ', this._apiVersion );
			throw new Error( '{60DF4F84-1DF4-4881-BB55-4A9EC1965A88}' );
		}

		return new URL(
			[ this._availableApiRootPath, constants.restNamespaceCore, '/v', this._apiVersion.toString(), path ].join(
				''
			),
			this._siteAddress
		).toString();
	}

	public initialized() {
		return this._initialized;
	}

	public async init() {
		if ( this._initialized ) {
			return;
		}

		//	APIバージョンを取得すると同時に、アクセス可能なAPIのルートパスを取得する。
		for ( const apiRootPath of this._apiRootPaths ) {
			//	バージョン情報を取得するためのパス
			const url = new URL(
				[ apiRootPath, constants.restNamespaceCore, '/version' ].join( '' ),
				this._siteAddress
			).toString();

			try {
				const response = await axios.get< VersionResponse >( url );
				this._apiVersion = response.data.version;
				this._availableApiRootPath = apiRootPath;
				this._initialized = true;
				break;
			} catch ( e ) {
				console.warn( e );
				continue;
			}
		}
	}
}

/**
 * WordPressで作成したAPIに対してアクセスを行うクラス
 */
export class WpRestApi {
	private constructor( siteAddress: string, apiRootPaths: string[] ) {
		this._assembler = new WpApiUrlAssembler( siteAddress, apiRootPaths );
	}
	private _assembler: WpApiUrlAssembler;

	/**
	 * REST API 使用時のユーザー識別用nonce
	 */
	private _wpRestNonce: string | undefined | null;

	/**
	 * 各API操作用のnonce
	 */
	private _actionNonce: { [ action: string ]: string | undefined } = {};

	public static async getInstance() {
		while ( WpRestApi._instance === undefined && WpRestApi._initializing ) {
			await new Promise( ( resolve ) => setTimeout( resolve, 100 ) );
		}
		if ( WpRestApi._instance === undefined ) {
			WpRestApi._initializing = true;
			const phpVar = getPhpCommonVar();
			const instance = new WpRestApi( phpVar.site_address, phpVar.api_root_paths );
			await instance.init();
			WpRestApi._instance = instance;
			WpRestApi._initializing = false;
		}
		return WpRestApi._instance;
	}
	private static _instance: WpRestApi | undefined;
	private static _initializing: boolean = false;

	public async init() {
		//	URLを組み立てるクラスが初期化されていない場合は初期化する。
		if ( this._assembler.initialized() === false ) {
			await this._assembler.init();
		}

		//	APIリクエスト時にユーザーを識別するためのnonceを取得する。
		//	※クッキーが使用可能な場合のみヘッダに付与するため、管理画面が対象となることを想定。
		this._wpRestNonce = getRestNonce();

		//	nonceが初期化されていない場合。
		//	(view用のnonceはだれがアクセスしても取得できるため、ここに値が設定されているかどうかで判定)
		if ( this._actionNonce[ REST_API_ACTION_TYPE.VIEW ] === undefined ) {
			const reqConfig = this._wpRestNonce ? { headers: { 'X-WP-Nonce': this._wpRestNonce } } : undefined;
			const { data } = await axios.post< NonceResponse >(
				this._assembler.getUrl( '/nonce' ),
				{ post_id: getPostIdFromDom() },
				reqConfig
			);

			this._actionNonce[ REST_API_ACTION_TYPE.VIEW ] = data.view_nonce;
			this._actionNonce[ REST_API_ACTION_TYPE.ADMIN ] = data.admin_nonce;
			this._actionNonce[ REST_API_ACTION_TYPE.EDITOR ] = data.editor_nonce;
		}
	}

	private getUrl( path: string, action: RestApiActionType | undefined ): string {
		const url = new URL( this._assembler.getUrl( path ) );
		if ( action ) {
			// `_wpnonce`はCookieが使える環境でないと`rest_cookie_invalid_nonce`エラーが発生する。
			//	Cookieが使用できない環境でもnonceを使うため、`_ajax_nonce`を指定する。
			url.searchParams.set( '_ajax_nonce', this._actionNonce[ action ]! );
		}
		return url.toString();
	}

	private getAxiosRequestConfig(): AxiosRequestConfig | undefined {
		return this._wpRestNonce ? { headers: { 'X-WP-Nonce': this._wpRestNonce } } : undefined;
	}

	private async get< T >( path: string, action: RestApiActionType | undefined ) {
		return axios.get< T >( this.getUrl( path, action ), this.getAxiosRequestConfig() );
	}

	private async post< T >( path: string, action: RestApiActionType | undefined, data?: any ) {
		return axios.post< T >( this.getUrl( path, action ), data, this.getAxiosRequestConfig() );
	}

	private async put< T >( path: string, action: RestApiActionType | undefined, data?: any ) {
		return axios.put< T >( this.getUrl( path, action ), data, this.getAxiosRequestConfig() );
	}

	/**
	 * サーバーでRPC URLに接続し、チェーンIDを取得します。
	 *
	 * @param rpcUrl RPC URL
	 */
	public async getChainId( rpcUrl: string ) {
		return (
			await this.post< { chain_id: number } >( '/chain-id', REST_API_ACTION_TYPE.ADMIN, {
				rpc_url: rpcUrl,
			} )
		).data.chain_id;
	}

	/**
	 * @deprecated
	 *
	 * @param chainId
	 */
	public async getTxConfirmationsSettings( chainId: number ) {
		return await this.get< { value: number | null; default: number } >(
			`/settings/tx-confirmations/${ chainId }`,
			REST_API_ACTION_TYPE.ADMIN
		);
	}

	/**
	 * チェーンIDに対するトランザクション承認数を取得します。
	 * チェーンIDは現在サーバーで設定されているネットワーク種別に属するものが対象。
	 */
	public async getActiveNetworkTypeTxConfirmations() {
		return await this.get< ActiveNetworkTypeTxConfirmationsResponse >(
			'/active-network-type/tx-confirmations',
			REST_API_ACTION_TYPE.ADMIN
		);
	}

	public async setTxConfirmationsSettings( chainId: number, confirmations: number | null ) {
		return await this.post( `/settings/tx-confirmations/${ chainId }`, REST_API_ACTION_TYPE.ADMIN, {
			value: confirmations,
		} );
	}

	/**
	 * 開発モードで動作しているかどうかを取得します。
	 * @return 開発モードで動作している場合はtrue
	 */
	public async getIsDevelopmentMode() {
		return ( await this.get< { value: boolean } >( '/development-mode', REST_API_ACTION_TYPE.ADMIN ) ).data.value;
	}

	public async getLogLevels() {
		return (
			await this.get< { value: { [ target: string ]: string } } >( '/log-levels', REST_API_ACTION_TYPE.ADMIN )
		).data.value;
	}

	public async setLogLevels( levels: { [ target: string ]: string } ) {
		return await this.post( '/log-levels', REST_API_ACTION_TYPE.ADMIN, {
			value: levels,
		} );
	}

	/**
	 * 現在動作中のネットワーク種別を取得します。
	 */
	public async getActiveNetworkType(): Promise< NetworkType | null > {
		return (
			await this.get< { value: NetworkType | null } >( '/settings/active-network', REST_API_ACTION_TYPE.ADMIN )
		).data.value;
	}

	/**
	 * 現在動作中のネットワーク種別を設定します。
	 *
	 * @param networkType ネットワーク種別
	 */
	public async setActiveNetworkType( networkType: NetworkType ) {
		return await this.post( '/settings/active-network', REST_API_ACTION_TYPE.ADMIN, {
			value: networkType,
		} );
	}

	/**
	 * 利用規約のURLを取得します。
	 */
	public async getTermsUrl(): Promise< string > {
		return this.getUrl( '/docs/terms', undefined );
	}

	/**
	 * サイト所有者が利用規約に同意したときの情報を取得します。
	 */
	public async getSiteOwnerAgreedTermsInfo(): Promise< {
		version: string;
		message: string;
		signature: string;
	} | null > {
		return (
			await this.get< {
				value: {
					version: string;
					message: string;
					signature: string;
				} | null;
			} >( '/site-owner-agreed-terms', REST_API_ACTION_TYPE.ADMIN )
		).data.value;
	}

	/**
	 * サイト所有者が利用規約に同意したときの情報を設定します。
	 *
	 * @param info
	 * @param info.version
	 * @param info.message
	 * @param info.signature
	 */
	public async setSiteOwnerAgreedTermsInfo( info: { version: string; message: string; signature: string } ) {
		return await this.post( '/site-owner-agreed-terms', REST_API_ACTION_TYPE.ADMIN, {
			version: info.version,
			message: info.message,
			signature: info.signature,
		} );
	}

	public async getPostSellableSymbolsInfo( postId: number ) {
		return (
			await this.get< {
				value: { symbol: string; isPaused: boolean }[];
			} >( `/post-sellable-symbols-info/${ postId }`, REST_API_ACTION_TYPE.EDITOR )
		).data.value;
	}

	public async getPayableSymbolsSetting( chainIds: number[] ) {
		return await this.get< { value: { [ chainId: string ]: string[] } } >(
			`/settings/payable-symbols/${ chainIds.join( '|' ) }`,
			REST_API_ACTION_TYPE.ADMIN
		);
	}

	/**
	 * 指定したチェーンでの支払可能な通貨シンボルの設定を取得します。
	 * @param chainId
	 * @return 支払可能な通貨シンボルの配列
	 * @deprecated
	 */
	public async getPayableSymbolsSetting_old( chainId: number ) {
		return (
			await this.get< { value: string[] } >(
				`/settings/payable-symbols/${ chainId }`,
				REST_API_ACTION_TYPE.ADMIN
			)
		).data.value;
	}

	/**
	 * スマートコントラクトで支払可能な通貨一覧の情報をブロックチェーンから取得します。
	 * ※ このサイトで設定されている支払可能な通貨とは異なることに注意。
	 *
	 * @param chainId チェーンID
	 */
	public async getBlockchainPayableSymbolsInfo( chainId: number ) {
		return (
			await this.get< BlockchainPayableSymbolsInfoResponse >(
				`/blockchain/payable-symbols-info/${ chainId }`,
				REST_API_ACTION_TYPE.ADMIN
			)
		).data;
	}

	/**
	 * 投稿IDに紐づく設定を取得します。
	 * @param postId
	 * @return	投稿IDに紐づく設定。
	 * @throws	設定が存在しない場合は404エラーが発生します。
	 */
	public async getPostSetting( postId: number ) {
		return await this.get< PostSettingResponse >( `/post-setting/${ postId }`, REST_API_ACTION_TYPE.EDITOR );
	}

	public async setPostSetting(
		postId: number,
		sellingPaused: boolean,
		sellingAmountHex: string,
		sellingDecimals: number,
		sellingSymbol: string,
		affiliatePercentAmountHex: string,
		affiliatePercentDecimals: number
	) {
		return await this.post( `/post-setting/${ postId }`, REST_API_ACTION_TYPE.EDITOR, {
			selling_paused: sellingPaused,
			selling_amount_hex: sellingAmountHex,
			selling_decimals: sellingDecimals,
			selling_symbol: sellingSymbol,
			affiliate_percent_amount_hex: affiliatePercentAmountHex,
			affiliate_percent_decimals: affiliatePercentDecimals,
		} );
	}

	public async getViewingInfo() {
		return await this.get< ViewingInfoResponse >( '/viewing-info', REST_API_ACTION_TYPE.VIEW );
	}

	public async getTxConfirmations( chainId: number ) {
		return ( await this.get< { value: number } >( `/tx-confirmations/${ chainId }`, REST_API_ACTION_TYPE.VIEW ) )
			.data.value;
	}

	public async getPurchaseInfo( postId: number, chainId: number, symbol: string ) {
		return await this.get< PurchaseSignatureResponse >(
			`/purchase-info/${ postId }/${ chainId }/${ symbol }`,
			REST_API_ACTION_TYPE.VIEW
		);
	}

	public async getPurchasedContent( postId: number, chainId: number, message: string, signature: string ) {
		return this.post< PurchasedContentResponse >( `/purchased-content/${ postId }`, REST_API_ACTION_TYPE.VIEW, {
			message,
			signature,
			chainId,
		} );
	}

	public async getRpcUrls( networkType?: NetworkType ) {
		const path = '/rpc-urls' + ( networkType ? `/${ networkType }` : '' );

		return this.get< RpcUrlsResponse >( path, REST_API_ACTION_TYPE.ADMIN );
	}

	public async setRpcUrl( chainId: number, rpcUrl: string | null ) {
		return this.post( `/rpc-url/${ chainId.toString() }`, REST_API_ACTION_TYPE.ADMIN, {
			rpc_url: rpcUrl,
		} );
	}

	/**
	 * 指定したチェーンで支払可能な通貨を設定します。
	 * @param chainId
	 * @param payableSymbols
	 */
	public async setPayableSymbols( chainId: number, payableSymbols: string[] ) {
		return this.post( `/settings/payable-symbols/${ chainId.toString() }`, REST_API_ACTION_TYPE.ADMIN, {
			value: payableSymbols,
		} );
	}

	/**
	 * フォントをインストールします。
	 *
	 * @param fontFile フォントファイル
	 */
	public async installFont( fontFile: File ) {
		const formData = new FormData();
		formData.append( 'font_file', fontFile );
		return this.post(
			//	ここは`put`ではなく`post`
			`/install-font`,
			REST_API_ACTION_TYPE.ADMIN,
			formData
		);
	}

	/**
	 * ライブラリインストール時に同梱されていたフォント一覧を取得します。
	 */
	public async getDistInstalledFonts() {
		return this.get< InstalledFontResponse >( `/dist-installed-fonts`, REST_API_ACTION_TYPE.ADMIN );
	}

	/**
	 * ユーザーがインストールしたフォント一覧を取得します。
	 */
	public async getUserInstalledFonts() {
		return this.get< InstalledFontResponse >( `/user-installed-fonts`, REST_API_ACTION_TYPE.ADMIN );
	}

	public async getSalesHistories( startUnixTime: number | undefined, endUnixTime: number | undefined ) {
		return (
			await this.post< { value: SalesHistoriesResponse } >( `/sales-histories`, REST_API_ACTION_TYPE.ADMIN, {
				start_unix_time: startUnixTime,
				end_unix_time: endUnixTime,
			} )
		).data.value;
	}
}
