<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\GraphQL;

use Cornix\Serendipity\Core\Application\Service\GraphQLService;
use GraphQL\Language\Parser;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;

class PluginSchemaProvider {
	private GraphQLService $graphql_service;

	public function __construct( GraphQLService $graphql_service ) {
		$this->graphql_service = $graphql_service;
	}

	public function get() {
		// キャッシュファイルをこのプラグインディレクトリ内に作成することで
		// プラグインアップデート時は存在しなくなり、再作成される仕組み。
		$cache_file_path = $this->graphql_service->getCacheFilePath();

		if ( $this->isCacheFileCreationNeeded() ) {
			$graphql_schema_path = $this->graphql_service->getSchemaFilePath();
			$document            = Parser::parse( file_get_contents( $graphql_schema_path ) );
			// キャッシュファイルを作成（第三引数指定なしで上書き保存）
			file_put_contents( $cache_file_path, "<?php\nreturn " . var_export( AST::toArray( $document ), true ) . ";\n" );
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
}
