<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Specification;

use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;

class ConfirmationsSpecification {

	/**
	 * トランザクションが確認済みかどうかを判定します
	 *
	 * @param BlockNumber   $tx_block_number 購入時のトランザクションが含まれるブロック番号
	 * @param BlockNumber   $current_block_number 現在のブロック番号
	 * @param Confirmations $confirmations 必要な確認数
	 */
	public function isConfirmed( BlockNumber $tx_block_number, BlockNumber $current_block_number, Confirmations $confirmations ): bool {
		assert( is_int( $confirmations->value() ), '[5B8A9B8B]' );

		// トランザクションが確認済みとなるブロック番号
		// トランザクションを含むブロックが確認数1となるため、`必要確認数-1`経過後が確認済みとなるブロック番号
		$confirmed_block_number = $tx_block_number->add( $confirmations->value() - 1 );

		return $confirmed_block_number->compare( $current_block_number ) <= 0;
	}
}
