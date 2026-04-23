<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Repository;

use Cornix\Serendipity\Core\Application\Repository\JwtSecretKeyRepository;
use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtSecretKey;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpOptionName;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\OptionGateway\Option\ArrayOption;

class WpJwtSecretKeyRepository implements JwtSecretKeyRepository {

	private ArrayOption $option;

	public function __construct() {
		$this->option = new ArrayOption( WpOptionName::JWT_SECRET_KEY );
	}

	/** @inheritdoc */
	public function get(): JwtSecretKey {
		/** @var array|null */
		$data = $this->option->get( null );

		if ( $data === null ) {
			// データベースマイグレーション処理で初期化されるため、通常ここは通らない
			throw new \LogicException( '[DAEA60F2] JWT secret key is not set.' );
		}

		// 配列として保存したため、先頭の要素を取り出す
		return JwtSecretKey::from( $data[0] );
	}

	/** @inheritdoc */
	public function save( JwtSecretKey $jwt_secret_key ): void {
		// /wp-admin/options.php にアクセスしたときに簡単に表示されないように配列として保存（気休め）
		// もう少しセキュリティを上げる方法として`wp-config.php`に定数を定義する方法が考えられるが、運用コストが高いため未対応
		$this->option->update( array( $jwt_secret_key->value() ), false ); // autoload = false
	}

	/** JWTの共通鍵を削除します */
	public function delete(): void {
		$this->option->delete();
	}
}
