<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Plugin;

use Baywall\Core\Application\Service\PluginService;
use Baywall\Core\Application\Service\SupportedVersionConstraintsProvider;
use Composer\Semver\Semver;

/**
 * サポート対象バージョンかどうかを判定するサービス。
 *
 * プロバイダから受け取った制約文字列（Composer制約文字列の配列）の
 * いずれかを満たせばサポート対象（OR条件）と判定します。
 *
 * - 解釈不能なバージョン文字列（WPナイトリーの `x.y.z-src-#####` 等）は判定不能として true を返します（誤検知防止）。
 * - 制約データの異常（ファイル不在・破損・制約文字列のパース不能）は fail-closed として `\RuntimeException` を送出します。
 *
 * 本クラスはWordPress環境に依存しないため、PHPUnitの標準`TestCase`で直接テスト可能です。
 */
class WpPluginService implements PluginService {

	public function __construct( private readonly SupportedVersionConstraintsProvider $constraints_provider ) {}

	/**
	 * @inheritdoc
	 * @throws \RuntimeException 同梱JSONの異常（制約文字列のパース不能を含む）の場合
	 */
	public function isSupportCmsVersion( string $cms_version ): bool {
		return $this->isSatisfiesAnyConstraint( $cms_version, $this->constraints_provider->cmsVersionConstraints() );
	}

	/**
	 * @inheritdoc
	 * @throws \RuntimeException 同梱JSONの異常（制約文字列のパース不能を含む）の場合
	 */
	public function isSupportPhpVersion( string $php_version ): bool {
		return $this->isSatisfiesAnyConstraint( $php_version, $this->constraints_provider->phpVersionConstraints() );
	}

	/**
	 * 指定バージョンがいずれかの制約を満たすかどうかを判定します。
	 *
	 * @param string   $version     判定対象のバージョン文字列
	 * @param string[] $constraints Composer制約文字列の配列
	 * @return bool いずれかの制約を満たす場合 true
	 */
	private function isSatisfiesAnyConstraint( string $version, array $constraints ): bool {
		foreach ( $constraints as $constraint ) {
			// 制約はプロバイダで事前検証済みのため、ここで捕捉される \UnexpectedValueException は
			// バージョン正規化の失敗（解釈不能なバージョン形式）に限られる。
			// 判定不能なバージョンは誤検知防止のためサポート対象とみなす。
			try {
				if ( Semver::satisfies( $version, $constraint ) ) {
					return true;
				}
			} catch ( \UnexpectedValueException $e ) {
				return true;
			}
		}

		return false;
	}
}
