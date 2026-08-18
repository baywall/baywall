<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\Entity\Oracle;

interface OracleRepository {
	/**
	 * Oracle情報をすべて取得します。
	 *
	 * @return Oracle[]
	 */
	public function all(): array;

	/** Oracle情報を保存します。 */
	public function save( Oracle $oracle ): void;
}
