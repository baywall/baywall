<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Option;

use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

/** @deprecated */
class OptionFactory {

	/**
	 * optionsテーブルに問い合わせる時のキーを取得します。
	 */
	private function getOptionKeyName( string $raw_option_key_name ): string {
		return ( new Prefix() )->optionKeyPrefix() . $raw_option_key_name;
	}

	/**
	 * 指定されたチェーンが最初に有効になった(≒取引が開始された)ブロック番号を取得または保存するオブジェクトを取得します。
	 */
	public function activeSinceBlockNumberHex( ChainId $chain_id ): StringOption {
		return new StringOption( $this->getOptionKeyName( 'active_since_block_number_hex_' . $chain_id->value() ) );
	}

	/**
	 * 指定されたチェーン、ブロックタグで最後にクロールしたブロック番号を取得または保存するオブジェクトを取得します。
	 */
	public function crawledBlockNumberHex( ChainId $chain_id, BlockTag $block_tag ): StringOption {
		return new StringOption( $this->getOptionKeyName( "crawled_block_number_hex_{$block_tag}_{$chain_id}" ) );
	}

	/**
	 * 販売者が同意した利用規約に関する情報を保存する際のキーのプレフィックスを取得します。
	 */
	private function sellerAgreedTermsKeyPrefix(): string {
		return 'seller_agreed_terms_';
	}

	/**
	 * 販売者が同意した利用規約のバージョンを取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsVersion(): IntOption {
		$prefix = $this->sellerAgreedTermsKeyPrefix();
		return new IntOption( $this->getOptionKeyName( $prefix . 'version' ) );
	}

	/**
	 * 販売者が利用規約に同意した際の署名を取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsSignature(): StringOption {
		$prefix = $this->sellerAgreedTermsKeyPrefix();
		return new StringOption( $this->getOptionKeyName( $prefix . 'signature' ) );
	}

	/**
	 * 販売者が利用規約に同意した際のユーザーIDを取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsUserId(): IntOption {
		$prefix = $this->sellerAgreedTermsKeyPrefix();
		return new IntOption( $this->getOptionKeyName( $prefix . 'user_id' ) );
	}
}
