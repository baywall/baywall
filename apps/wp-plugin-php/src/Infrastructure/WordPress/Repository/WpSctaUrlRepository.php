<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Repository;

use Cornix\Serendipity\Core\Application\Repository\SctaUrlRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\SctaUrl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpOptionName;

/**
 * 「特定商取引法に基づく表記」のURLを取得または保存するクラス
 */
class WpSctaUrlRepository implements SctaUrlRepository {

	private string $option_name;

	public function __construct() {
		$this->option_name = WpOptionName::SCTA_URL;
	}

	/** 「特定商取引法に基づく表記」のURLを取得します */
	public function get(): ?SctaUrl {
		/** @var string|null  */
		$scta_url = get_option( $this->option_name, null );
		return SctaUrl::fromNullable( $scta_url );
	}

	/** 「特定商取引法に基づく表記」のURLを保存します */
	public function save( ?SctaUrl $scta_url ): void {
		if ( $scta_url === null ) {
			delete_option( $this->option_name );
		} else {
			update_option( $this->option_name, $scta_url->value() );
		}
	}
}
