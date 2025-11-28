<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Repository;

use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Nonce;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;

/**
 * ERC-4361で使用するNonceを永続化するリポジトリ
 */
interface Erc4361NonceRepository {
	/** 指定したアドレスのNonceを取得します */
	public function get( Address $address ): ?Erc4361Nonce;

	/** 指定したアドレスに対してNonceを保存します */
	public function save( Address $address, Erc4361Nonce $nonce ): void;

	/** 指定したアドレスのNonceを削除します */
	public function delete( Address $address ): void;

	/** 指定した日時よりも前に発行されたNonceを削除します */
	public function deleteExpired( UnixTimestamp $target_time ): void;
}
