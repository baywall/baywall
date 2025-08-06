<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\PaidContent;

interface PaidContentService {
	/** 有料部分の文字数を取得します */
	public function getCharacterCount( PaidContent $paid_content ): int;

	/** 有料部分に含まれる画像数を取得します */
	public function getImageCount( PaidContent $paid_content ): int;
}
