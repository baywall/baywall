<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Repository\NetworkCategoryRepository;
use Baywall\Core\Domain\ValueObject\NetworkCategory;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;

class GetNetworkCategory {
	public function __construct( private readonly NetworkCategoryRepository $network_category_repository ) {}

	public function handle( NetworkCategoryId $network_category_id ): NetworkCategory {
		$network_category = $this->network_category_repository->get( $network_category_id );
		if ( null === $network_category ) {
			throw new \InvalidArgumentException( "[492963CD] network category data is not found. network category id: {$network_category_id}" );
		}
		return $network_category;
	}
}
