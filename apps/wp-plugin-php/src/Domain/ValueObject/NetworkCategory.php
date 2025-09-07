<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * ネットワークカテゴリを表すクラス
 */
final class NetworkCategory implements \Stringable {

	private function __construct( NetworkCategoryId $network_category_id, string $name ) {
		$this->id   = $network_category_id;
		$this->name = $name;
	}

	/** ネットワークカテゴリID */
	private NetworkCategoryId $id;

	/** ネットワークカテゴリ名 */
	private string $name;

	/** ネットワークカテゴリIDを数値で取得します。 */
	public function id(): NetworkCategoryId {
		return $this->id;
	}

	/** ネットワークカテゴリ名を取得します。 */
	public function name(): string {
		return $this->name;
	}

	public static function from( NetworkCategoryId $network_category_id, string $name ): self {
		return new self( $network_category_id, $name );
	}

	public function equals( self $other ): bool {
		return $this->id->equals( $other->id ); // ネットワークカテゴリIDが一致していれば同じとみなす
	}

	public function __toString(): string {
		return $this->name;
	}
}
