<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Domain;
use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Statement;
use Cornix\Serendipity\Core\Application\ValueObject\Erc4361Uri;

interface Erc4361PropertyProvider {

	public function domain(): Erc4361Domain;

	public function statement(): ?Erc4361Statement;

	public function uri(): Erc4361Uri;
}
