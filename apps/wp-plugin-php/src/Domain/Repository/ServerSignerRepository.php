<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\Entity\ServerSigner;

interface ServerSignerRepository {

	/** 署名用ウォレットを取得します */
	public function get(): ServerSigner;
}
