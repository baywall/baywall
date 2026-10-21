<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Repository;

/**
 * ログレコードを永続化するリポジトリ
 */
interface LogRepository {
	/** 指定秒数より古いログレコードを削除します */
	public function deleteOldRecords( int $expiration_seconds ): void;
}
