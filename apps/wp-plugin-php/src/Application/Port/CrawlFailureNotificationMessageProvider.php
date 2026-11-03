<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

use Baywall\Core\Domain\ValueObject\ChainId;

interface CrawlFailureNotificationMessageProvider {
	/** 通知メールの件名（翻訳済み） */
	public function title(): string;

	/** 通知メールの本文（チェーン名・チェーン ID・サイト URL を埋め込み済み・翻訳済み） */
	public function message( string $chain_name, ChainId $chain_id ): string;
}
