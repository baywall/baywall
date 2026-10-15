<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Mapper;

use Baywall\Core\Domain\Entity\WidgetAttributes;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Domain\ValueObject\Symbol;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;

/** Gutenberg属性配列⇔WidgetAttributes(販売設定のドメイン形式)の変換を行います。 */
class WidgetAttributesMapper {

	public function fromArray( array $attrs ): WidgetAttributes {
		return WidgetAttributes::from(
			NetworkCategoryId::fromNullable( $attrs[ WpConfig::BLOCK_ATTR_NAME_SELLING_NETWORK_CATEGORY_ID ] ?? null ),
			Amount::fromNullable( $attrs[ WpConfig::BLOCK_ATTR_NAME_SELLING_AMOUNT ] ?? null ),
			Symbol::fromNullable( $attrs[ WpConfig::BLOCK_ATTR_NAME_SELLING_SYMBOL ] ?? null )
		);
	}

	public function toArray( WidgetAttributes $widget_attributes ): array {
		return array(
			WpConfig::BLOCK_ATTR_NAME_SELLING_NETWORK_CATEGORY_ID => $widget_attributes->sellingNetworkCategoryId()?->value(),
			WpConfig::BLOCK_ATTR_NAME_SELLING_AMOUNT => $widget_attributes->sellingAmount()?->value(),
			WpConfig::BLOCK_ATTR_NAME_SELLING_SYMBOL => $widget_attributes->sellingSymbol()?->value(),
		);
	}
}
