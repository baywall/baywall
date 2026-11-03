<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\GraphQL\Resolvers;

/** GraphQL リゾルバ共通の契約 */
interface Resolver {
	/**
	 * 戻り値の型はフィールドごとに異なるため宣言しない（実装側で任意の戻り値型を宣言できる）
	 *
	 * @param array<string, mixed> $root_value `RootValue::get()`の戻り値（フィールド名 → リゾルバコールバック）
	 * @param array<string, mixed> $args       GraphQLの引数
	 * @return mixed
	 */
	public function handle( array $root_value, array $args );
}
