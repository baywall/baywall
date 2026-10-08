<?php
declare(strict_types=1);
namespace Baywall\Core\Infrastructure\GraphQL;

use Baywall\Core\Application\Service\GraphQLService;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;

class PluginSchemaProvider {

	public function __construct( private readonly GraphQLService $graphql_service ) {}

	public function get() {
		// キャッシュファイルをこのプラグインディレクトリ内に作成することで
		// プラグインアップデート時は存在しなくなり、再作成される仕組み。
		$cache_file_path = $this->graphql_service->getCacheFilePath();

		if ( $this->isCacheFileCreationNeeded() ) {
			$graphql_schema_path = $this->graphql_service->getSchemaFilePath();
			$document            = Parser::parse( file_get_contents( $graphql_schema_path ) );
			if ( ! $this->writeCacheFile( $cache_file_path, $document ) ) {
				// 書き込みに失敗してもパース結果で処理を継続する（次回リクエストで再作成が試みられる）
				error_log( '[3969B135] PluginSchemaProvider schema cache write failed: ' . $cache_file_path );
			}
		} else {
			$document = AST::fromArray( require $cache_file_path );
		}

		$schema = BuildSchema::build( $document );

		return $schema;
	}

	/**
	 * キャッシュファイルを作成(または再作成)する必要があるかどうかを判定します。
	 */
	private function isCacheFileCreationNeeded(): bool {
		$cache_file_path     = $this->graphql_service->getCacheFilePath();
		$graphql_schema_path = $this->graphql_service->getSchemaFilePath();

		if ( ! file_exists( $cache_file_path ) ) {
			// キャッシュファイルが存在しない場合は作成が必要
			return true;
		} else {
			// スキーマファイルが更新されている場合は再作成が必要
			return filemtime( $cache_file_path ) < filemtime( $graphql_schema_path );
		}
	}

	/**
	 * スキーマキャッシュファイルを一時ファイル経由で生成します。
	 *
	 * @param string       $cache_file_path キャッシュファイルパス。
	 * @param DocumentNode $document        パース済みスキーマドキュメント。
	 * @return bool 書き込みに成功した場合 true。失敗時は一時ファイルを削除して false。
	 */
	private function writeCacheFile( string $cache_file_path, DocumentNode $document ): bool {
		$contents = "<?php\nreturn " . var_export( AST::toArray( $document ), true ) . ";\n";

		// 同一ディレクトリ内の一時ファイルへ書いて差し替えることで、読み取り側に中途半端な内容を渡さない
		$tmp_file_path = $cache_file_path . '.' . bin2hex( random_bytes( 6 ) ) . '.tmp';

		// 失敗は戻り値で検知するため警告を抑制する（権限制限・ファイルロック等は事前チェックできない）
		// phpcs:ignore WordPress.PHP.NoSilencedErrors -- 失敗は戻り値で検知してフォールバックするため
		$written = @file_put_contents( $tmp_file_path, $contents );
		if ( strlen( $contents ) !== $written ) {
			@unlink( $tmp_file_path );
			return false;
		}

		if ( ! @rename( $tmp_file_path, $cache_file_path ) ) {
			@unlink( $tmp_file_path );
			return false;
		}

		return true;
	}
}
