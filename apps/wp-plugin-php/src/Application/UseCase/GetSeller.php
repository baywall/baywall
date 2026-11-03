<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Domain\Entity\Seller;
use Baywall\Core\Domain\Repository\SellerRepository;

class GetSeller {
	public function __construct( private readonly SellerRepository $seller_repository ) {}

	public function handle(): ?Seller {
		return $this->seller_repository->get();
	}
}
