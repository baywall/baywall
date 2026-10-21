<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\OptionRepository;

use Baywall\Core\Application\Repository\SctaUrlRepository;
use Baywall\Core\Domain\ValueObject\SctaUrl;
use Baywall\Core\Infrastructure\WordPress\Constants\WpOptionName;
use Baywall\Core\Infrastructure\WordPress\Database\OptionGateway\Option\StringOption;

/**
 * 「特定商取引法に基づく表記」のURLを取得または保存するクラス
 */
class WpSctaUrlRepository implements SctaUrlRepository {

	private readonly StringOption $option;

	public function __construct() {
		$this->option = new StringOption( WpOptionName::SCTA_URL );
	}

	/** 「特定商取引法に基づく表記」のURLを取得します */
	public function get(): ?SctaUrl {
		return SctaUrl::fromNullable( $this->option->get() );
	}

	/** 「特定商取引法に基づく表記」のURLを保存します */
	public function save( ?SctaUrl $scta_url ): void {
		if ( $scta_url === null ) {
			$this->option->delete();
		} else {
			$this->option->update( $scta_url->value() );
		}
	}
}
