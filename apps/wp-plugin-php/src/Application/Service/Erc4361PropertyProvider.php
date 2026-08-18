<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\ValueObject\Erc4361Domain;
use Baywall\Core\Application\ValueObject\Erc4361Statement;
use Baywall\Core\Application\ValueObject\Erc4361Uri;

interface Erc4361PropertyProvider {

	public function domain(): Erc4361Domain;

	public function statement(): ?Erc4361Statement;

	public function uri(): Erc4361Uri;
}
