<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\ServerSigner;

interface ServerSignerRepository {

	/** 署名用ウォレットを取得します */
	public function get(): ServerSigner;
}
