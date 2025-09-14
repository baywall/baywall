<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\Entity\WidgetAttributes;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\Content;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use WP_Block_Parser_Block;

/** WordPressのGutenberg関連のサービスを提供します。 */
class GutenbergService {

	private BlockNameProvider $block_name_provider;

	public function __construct( BlockNameProvider $block_name_provider ) {
		$this->block_name_provider = $block_name_provider;
	}

	/** 投稿内容から、本プラグインで使用するウィジェットの属性情報を取得します */
	public function getWidgetAttributes( Content $post_content ): ?WidgetAttributes {
		$block = $this->getWidgetBlock( $post_content );

		if ( ! $block ) {
			return null;
		}

		$attrs = $block->attrs;

		$selling_network_category_id_value = $attrs[ Config::BLOCK_ATTR_NAME_SELLING_NETWORK_CATEGORY_ID ] ?? null;
		$selling_amount_value              = $attrs[ Config::BLOCK_ATTR_NAME_SELLING_AMOUNT ] ?? null;
		$selling_symbol_value              = $attrs[ Config::BLOCK_ATTR_NAME_SELLING_SYMBOL ] ?? null;

		return WidgetAttributes::from(
			NetworkCategoryId::fromNullable( $selling_network_category_id_value ),
			Amount::fromNullable( $selling_amount_value ),
			Symbol::fromNullable( $selling_symbol_value )
		);
	}

	/**
	 * 投稿内容をブロックに分割します。
	 *
	 * @param Content $content
	 * @return WP_Block_Parser_Block[]
	 */
	private function parseBlocks( Content $content ): array {
		$blocks = parse_blocks( $content->value() );
		return array_map(
			function ( $block ) {
				[
					'blockName'    => $name,
					'attrs'        => $attrs,
					'innerBlocks'  => $inner_blocks,
					'innerHTML'    => $inner_html,
					'innerContent' => $inner_content
				] = $block;

				return new WP_Block_Parser_Block( $name, $attrs, $inner_blocks, $inner_html, $inner_content );
			},
			$blocks
		);
	}

	/** 投稿内容から、本プラグインで使用するウィジェットのブロック情報を取得します */
	private function getWidgetBlock( Content $post_content ): ?WP_Block_Parser_Block {
		$blocks     = $this->parseBlocks( $post_content );
		$block_name = $this->block_name_provider->get(); // ウィジェットに付与されているブロック名

		// `blockName`プロパティが$block_nameと一致するブロックを取得
		$blocks = array_filter(
			$blocks,
			function ( $block ) use ( $block_name ) {
				return $block_name === $block->blockName;
			}
		);

		// ウィジェットは1投稿につき1つまでしか存在しない
		if ( 1 < count( $blocks ) ) {
			throw new \RuntimeException( '[418F06F5] Widget block must be only one in a post. ' . count( $blocks ) . ' found.' );
		}

		return array_values( $blocks )[0] ?? null;
	}
}
