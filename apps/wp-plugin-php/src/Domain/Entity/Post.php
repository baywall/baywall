<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\PaidContent;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class Post {

	public function __construct( PostId $post_id, ?PaidContent $paid_content, ?NetworkCategoryId $selling_network_category_id, ?Amount $selling_amount, ?Symbol $selling_symbol ) {
		$this->post_id                     = $post_id;
		$this->paid_content                = $paid_content;
		$this->selling_network_category_id = $selling_network_category_id;
		$this->selling_amount              = $selling_amount;
		$this->selling_symbol              = $selling_symbol;
	}

	private PostId $post_id;
	private ?PaidContent $paid_content;
	private ?NetworkCategoryId $selling_network_category_id;
	private ?Amount $selling_amount;
	private ?Symbol $selling_symbol;

	public function id(): PostId {
		return $this->post_id;
	}
	public function paidContent(): ?PaidContent {
		return $this->paid_content;
	}
	public function sellingNetworkCategoryId(): ?NetworkCategoryId {
		return $this->selling_network_category_id;
	}
	public function sellingAmount(): ?Amount {
		return $this->selling_amount;
	}
	public function sellingSymbol(): ?Symbol {
		return $this->selling_symbol;
	}
	public function sellingPrice(): ?Price {
		if ( $this->selling_amount && $this->selling_symbol ) {
			return Price::from( $this->selling_amount, $this->selling_symbol );
		} else {
			return null;
		}
	}

	public function setPaidContent( PaidContent $paid_content, ?NetworkCategoryId $selling_network_category_id, ?Amount $selling_amount, ?Symbol $selling_symbol ): void {
		$this->paid_content                = $paid_content;
		$this->selling_network_category_id = $selling_network_category_id;
		$this->selling_amount              = $selling_amount;
		$this->selling_symbol              = $selling_symbol;
	}

	public function deletePaidContent(): void {
		$this->paid_content                = null;
		$this->selling_network_category_id = null;
		$this->selling_amount              = null;
		$this->selling_symbol              = null;
	}
}
