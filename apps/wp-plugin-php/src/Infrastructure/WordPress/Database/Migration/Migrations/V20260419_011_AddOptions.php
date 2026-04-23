<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpOptionName;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Repository\WpJwtSecretKeyRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Repository\WpPausedRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpJwtSecretKeyService;

class V20260419_011_AddOptions extends MigrationBase {

	private WpPausedRepository $paused_repository;
	private WpJwtSecretKeyRepository $jwt_secret_key_repository;
	private WpJwtSecretKeyService $jwt_secret_key_service;

	public function __construct( WpPausedRepository $paused_repository, WpJwtSecretKeyRepository $jwt_secret_key_repository, WpJwtSecretKeyService $jwt_secret_key_service ) {
		$this->paused_repository         = $paused_repository;
		$this->jwt_secret_key_repository = $jwt_secret_key_repository;
		$this->jwt_secret_key_service    = $jwt_secret_key_service;
	}

	public function version(): string {
		return '0.0.2';
	}

	public function up(): void {
		// サイト一時停止状態を初期化
		$this->paused_repository->save( false );

		// インストール時のサイトURLを保持
		update_option( WpOptionName::INSTALL_ORIGIN_URL, home_url() );

		// JWTの共通鍵を初期化
		$this->jwt_secret_key_repository->save( $this->jwt_secret_key_service->generate() );
	}

	public function down(): void {
		$this->jwt_secret_key_repository->delete(); // JWTの共通鍵を削除
		delete_option( WpOptionName::INSTALL_ORIGIN_URL ); // インストール時のサイトURLを削除
		$this->paused_repository->delete(); // サイト一時停止状態を削除
	}
}
