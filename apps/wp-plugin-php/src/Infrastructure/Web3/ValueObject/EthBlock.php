<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use stdClass;

class EthBlock {

	private function __construct( stdClass $get_block_by_number_response ) {
		$this->response = $get_block_by_number_response;
	}

	private stdClass $response;

	public static function from( stdClass $get_block_by_number_response ): self {
		return new self( $get_block_by_number_response );
	}

	public function number(): BlockNumber {
		$block_number_hex = Hex::from( $this->response->number );
		return BlockNumber::fromHex( $block_number_hex );
	}

	public function timestamp(): UnixTimestamp {
		$timestamp_hex = Hex::from( $this->response->timestamp );
		// タイムスタンプはUNIX時間なのでhexdecで変換して問題ない
		return UnixTimestamp::from( hexdec( $timestamp_hex->value() ) );
	}
}
