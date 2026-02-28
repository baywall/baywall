<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Seller;

interface SellerRepository {

	/** 販売者情報を取得します */
	public function get(): ?Seller;

	/** 販売者情報を保存します */
	public function save( Seller $seller ): void;
}
