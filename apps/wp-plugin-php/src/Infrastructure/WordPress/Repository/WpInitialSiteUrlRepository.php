<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Repository;

use Baywall\Core\Domain\Repository\InitialSiteUrlRepository;
use Baywall\Core\Domain\ValueObject\Url;
use Baywall\Core\Infrastructure\WordPress\Database\OptionGateway\Option\StringOption;
use Baywall\Core\Infrastructure\WordPress\Constants\WpOptionName;
use RuntimeException;

class WpInitialSiteUrlRepository implements InitialSiteUrlRepository {

	private readonly StringOption $option;

	public function __construct() {
		$this->option = new StringOption( WpOptionName::INITIAL_SITE_URL );
	}

	public function get(): Url {
		$url = $this->option->get();

		if ( $url === null ) {
			throw new RuntimeException( '[509CFC8D] Initial site URL is not set.' );
		}

		return Url::from( $url );
	}
}
