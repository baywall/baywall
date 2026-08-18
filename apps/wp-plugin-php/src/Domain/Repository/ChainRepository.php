<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\ValueObject\ChainId;

interface ChainRepository {

	/** 指定したチェーンIDに合致するチェーン情報を取得します。 */
	public function get( ChainId $chain_id ): ?Chain;

	/**
	 * データが存在するチェーン一覧(すべて)を取得します。
	 *
	 * @return Chain[]
	 */
	public function all(): array;

	/** チェーン情報を保存します。 */
	public function save( Chain $chain ): void;
}
