<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Exception;

use Baywall\Core\Domain\Exception\HttpStatus\BadRequestException;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\PostId;

/**
 * 別チェーンで同一投稿・同一購入者向けの請求書が直近に発行されている場合にスローされる例外クラス
 */
class DuplicatePurchaseException extends BadRequestException {

	public function __construct( PostId $post_id, Address $buyer_address, ChainId $chain_id ) {
		parent::__construct( "[4EBF4A51] Duplicate purchase detected on another chain. post_id: {$post_id}, buyer_address: {$buyer_address}, chain_id: {$chain_id}" );
	}
}
