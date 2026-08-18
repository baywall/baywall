<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\Erc4361PropertyProvider;
use Baywall\Core\Application\ValueObject\Erc4361Domain;
use Baywall\Core\Application\ValueObject\Erc4361Statement;
use Baywall\Core\Application\ValueObject\Erc4361Uri;
use Baywall\Core\Constant\Config;

class WpErc4361PropertyProvider implements Erc4361PropertyProvider {

	private WordPressPropertyProvider $wp_property_provider;

	public function __construct( WordPressPropertyProvider $wp_property_provider ) {
		$this->wp_property_provider = $wp_property_provider;
	}

	public function domain(): Erc4361Domain {
		$home_url = $this->wp_property_provider->homeUrl();
		return Erc4361Domain::from( parse_url( $home_url, PHP_URL_HOST ) );
	}

	public function statement(): ?Erc4361Statement {
		return Erc4361Statement::from( Config::ERC4361_STATEMENT );
	}

	public function uri(): Erc4361Uri {
		$home_url = $this->wp_property_provider->homeUrl();
		return Erc4361Uri::from( $home_url );
	}
}
