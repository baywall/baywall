<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\Entity\ServerSigner;
use Baywall\Core\Domain\ValueObject\Address;

interface ServerSignerRepository {

	/** 署名用ウォレットを取得します */
	public function get(): ServerSigner;

	/** 署名用ウォレットのアドレスを取得します（秘密鍵を読み取らないため、鍵の値に依存しない） */
	public function getAddress(): Address;
}
