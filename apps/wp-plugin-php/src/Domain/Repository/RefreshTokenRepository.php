<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\RefreshToken;
use Cornix\Serendipity\Core\Domain\ValueObject\RefreshTokenString;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;

interface RefreshTokenRepository {
	/** 指定したリフレッシュトークン文字列からリフレッシュトークンの情報を取得します */
	public function get( RefreshTokenString $refresh_token_string ): ?RefreshToken;

	/** リフレッシュトークン情報を追加します。 */
	public function add( RefreshToken $refresh_token ): void;

	/** リフレッシュトークン情報を更新します。 */
	public function update( RefreshToken $refresh_token ): void;

	/** 指定した日時よりも前に作成されたリフレッシュトークンを削除します */
	public function deleteByCreatedAt( UnixTimestamp $target_time ): void;
}
