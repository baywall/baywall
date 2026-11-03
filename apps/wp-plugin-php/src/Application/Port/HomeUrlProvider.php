<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

interface HomeUrlProvider {
	/** サイトのホームURL（「設定 > 一般」のサイトアドレス）を取得します */
	public function homeUrl(): string;
}
