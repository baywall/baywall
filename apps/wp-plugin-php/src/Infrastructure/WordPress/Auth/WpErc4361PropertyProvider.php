<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Auth;

use Baywall\Core\Application\Port\Erc4361PropertyProvider;
use Baywall\Core\Application\ValueObject\Erc4361Domain;
use Baywall\Core\Application\ValueObject\Erc4361Statement;
use Baywall\Core\Application\ValueObject\Erc4361Uri;
use Baywall\Core\Constant\Config;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;

class WpErc4361PropertyProvider implements Erc4361PropertyProvider {
	public function __construct( private readonly WordPressPropertyProvider $wordpress_property_provider ) {}

	public function domain(): Erc4361Domain {
		$home_url = $this->wordpress_property_provider->homeUrl();
		$host     = parse_url( $home_url, PHP_URL_HOST );
		if ( ! is_string( $host ) || '' === $host ) {
			throw new \RuntimeException( "[31C5E485] Failed to parse host from home URL: {$home_url}" );
		}
		return Erc4361Domain::from( $host );
	}

	public function statement(): ?Erc4361Statement {
		return Erc4361Statement::from( Config::ERC4361_STATEMENT );
	}

	public function uri(): Erc4361Uri {
		$home_url = $this->wordpress_property_provider->homeUrl();
		return Erc4361Uri::from( $home_url );
	}
}
