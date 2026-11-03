<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Content;

use Baywall\Core\Domain\Entity\WidgetAttributes;
use Baywall\Core\Domain\ValueObject\PaidContent;
use WP_Block;

/** ペイウォール分割（有料部分の分離）の結果 */
readonly class PaywallSplitResult {
	/**
	 * @param WP_Block[] $replacement_blocks 本文に差し替えるブロック一覧（無料部分＋ダミーブロック）
	 */
	public function __construct(
		private array $replacement_blocks,
		private PaidContent $paid_content,
		private WidgetAttributes $widget_attributes
	) {}

	/** @return WP_Block[] */
	public function replacementBlocks(): array {
		return $this->replacement_blocks;
	}

	public function paidContent(): PaidContent {
		return $this->paid_content;
	}

	public function widgetAttributes(): WidgetAttributes {
		return $this->widget_attributes;
	}
}
