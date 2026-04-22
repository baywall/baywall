<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Repository;

use Cornix\Serendipity\Core\Domain\Repository\InstallOriginUrl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\OptionGateway\Option\StringOption;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpOptionName;
use RuntimeException;

class WpInstallOriginUrl implements InstallOriginUrl {

	private StringOption $option;

	public function __construct() {
		$this->option = new StringOption( WpOptionName::INSTALL_ORIGIN_URL );
	}

	public function get(): string {
		$url = $this->option->get();

		if ( $url === null ) {
			throw new RuntimeException( '[509CFC8D] Install origin URL is not set.' );
		}

		return $url;
	}

	public function save( string $url ): void {
		$this->option->update( $url );
	}
}
