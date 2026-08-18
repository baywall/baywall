<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Repository\ThemeSettingRepository;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Domain\Entity\WidgetAttributes;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\Content;
use Baywall\Core\Domain\ValueObject\PostId;
use Baywall\Core\Infrastructure\WordPress\ValueObject\BlockName;
use WP_Block;

/** WordPressのGutenberg関連のサービスを提供します。 */
class GutenbergService {

	private PostRepository $post_repository;
	private BlockNameProvider $block_name_provider;
	private ThemeSettingRepository $theme_setting_repository;

	public function __construct( PostRepository $post_repository, BlockNameProvider $block_name_provider, ThemeSettingRepository $theme_setting_repository ) {
		$this->post_repository          = $post_repository;
		$this->block_name_provider      = $block_name_provider;
		$this->theme_setting_repository = $theme_setting_repository;
	}

	public function getWidgetAttributes( WP_Block $block ): WidgetAttributes {
		assert( $block->name === $this->block_name_provider->get()->value(), '[8F0F589D]' );
		return WidgetAttributes::fromArray( $block->attributes );
	}

	public function createWidgetBlock( PostId $post_id ): WP_Block {
		$post              = $this->post_repository->get( $post_id );
		$widget_attributes = WidgetAttributes::from(
			$post->sellingNetworkCategoryId(),
			$post->sellingAmount(),
			$post->sellingSymbol(),
		);

		$attrs            = $widget_attributes->toArray();
		$block_name_value = $this->block_name_provider->get()->value();

		$class_name         = WpConfig::PAYWALL_BLOCK_CSS_CLASS_NAME;
		$default_class_name = 'wp-block-' . str_replace( '/', '-', $block_name_value );

		// テーマ設定がauto以外の場合のみホスト要素にテーマ属性を出力する。
		// (autoの場合は属性を付与せず、ブラウザ側でOS設定を同期解決する)
		$theme_attribute = '';
		$theme_setting   = $this->theme_setting_repository->get();
		if ( ! $theme_setting->isAuto() ) {
			$theme_attribute = ' ' . WpConfig::THEME_ATTRIBUTE_NAME . '="' . esc_attr( $theme_setting->value() ) . '"';
		}

		$html = '<aside class="' . esc_attr( $default_class_name ) . ' ' . esc_attr( $class_name ) . '"' . $theme_attribute . '></aside>';

		return new WP_Block(
			array(
				'blockName'    => $block_name_value,
				'attrs'        => $attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => $html,
				'innerContent' => array( $html ),
			),
		);
	}

	/**
	 * 指定したブロック名に一致するブロックのインデックスを返します。
	 *
	 * @param WP_Block[] $blocks
	 * @return int
	 */
	public function findBlockIndex( array $blocks, BlockName $block_name ): int {
		foreach ( $blocks as $block ) {
			if ( $block->name === $block_name->value() ) {
				return array_search( $block, $blocks );
			}
		}
		return -1;
	}

	/**
	 * HTMLコメント付きの投稿内容に変換します。(データベース保存用)
	 *
	 * ※ WP_Block::render() はコメント無しで出力されるためデータベースに格納できない
	 *
	 * @param WP_Block[] $blocks
	 */
	public function serializeBlocks( array $blocks ): Content {
		$html = '';
		foreach ( $blocks as $block ) {
			// WP_Block -> 配列に戻して serialize_block()
			$html .= serialize_block( $block->parsed_block );
		}
		return Content::from( $html );
	}

	/**
	 * 投稿内容をブロックに分割します。
	 *
	 * @param Content $content
	 * @return WP_Block[]
	 */
	public function parseBlocks( Content $content ): array {
		$blocks = parse_blocks( $content->value() );
		return array_map( fn( $b ) => new WP_Block( $b ), $blocks );
	}
}
