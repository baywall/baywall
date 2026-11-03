<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

use Baywall\Core\Domain\ValueObject\Address;

interface SellerWalletChangeNotificationMessageProvider {
	/** 通知メールの件名（翻訳済み） */
	public function title(): string;

	/** 通知メールの本文（変更前後アドレス・サイト URL を埋め込み済み・翻訳済み） */
	public function message( Address $previous_address, Address $new_address ): string;
}
