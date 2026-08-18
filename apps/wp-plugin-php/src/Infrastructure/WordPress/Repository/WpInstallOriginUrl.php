<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Repository;

use Baywall\Core\Domain\Repository\InstallOriginUrl;
use Baywall\Core\Infrastructure\WordPress\Database\OptionGateway\Option\StringOption;
use Baywall\Core\Infrastructure\WordPress\Constants\WpOptionName;
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
