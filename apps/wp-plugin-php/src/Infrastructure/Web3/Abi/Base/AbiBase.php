<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Abi\Base;

use Baywall\Core\Domain\ValueObject\Address;
use InvalidArgumentException;
use kornrunner\Keccak;
use phpseclib4\Math\BigInteger;
use stdClass;

abstract class AbiBase {
	/**
	 * 計算済みのtopicハッシュを保持
	 *
	 * @var array<string,string>
	 */
	private $topic_hash_cache = array();

	/**
	 * ABIの定義を取得します。
	 */
	abstract public function get(): array;

	/**
	 * 指定したメソッドまたはイベントのtopic(フィルタ用のハッシュ値)を取得します。
	 *
	 * keccak256("UnlockPaywall(address,address,uint256,uint256,address)") のようなハッシュ値
	 */
	protected function topicHash( string $func_or_event_name ): string {
		if ( ! array_key_exists( $func_or_event_name, $this->topic_hash_cache ) ) {
			$abi    = $this->get();
			$target = array_filter( $abi, fn( $item ) => $item['name'] === $func_or_event_name );
			assert( count( $target ) === 1, '[841A8DFC] Invalid function or event name. ' . $func_or_event_name );
			$target = array_values( $target )[0];

			$input_types = array_map( fn( $item ) => $item['type'], $target['inputs'] );

			$hash = '0x' . Keccak::hash( $func_or_event_name . '(' . implode( ',', $input_types ) . ')', 256 );

			$this->topic_hash_cache[ $func_or_event_name ] = $hash;
		}

		return $this->topic_hash_cache[ $func_or_event_name ];
	}


	/**
	 * eth_getLogsで取得したログオブジェクトのイベント名を取得します。
	 */
	private function getEventName( stdClass $log ): ?string {
		/** @var string */
		$log_topic_hash = $log->topics[0];

		// キャッシュに存在する場合はそのまま返す
		/** @var string|false */
		$result = array_search( $log_topic_hash, $this->topic_hash_cache, true );
		if ( is_string( $result ) ) {
			return $result;
		}

		// 見つからない場合は検索
		// すべてのイベント名を取得
		$event_names = array_map( fn( $item ) => $item['name'], $this->get() );
		foreach ( $event_names as $event_name ) {
			if ( $this->topicHash( $event_name ) === $log_topic_hash ) {
				return $event_name;
			}
		}

		assert( false, "[0FACDB6E] Unknown event name. log_topic_hash: {$log_topic_hash}, log: " . json_encode( $log ) );
		return null;
	}

	/**
	 * 指定したイベントのデコードされた引数を取得します。
	 *
	 * indexedな引数はtopicsから、その他の引数はdataから32バイトワード単位でデコードする。
	 * 対応型は address と uint256 のみ（他の型・動的型は未対応で例外とする）。
	 *
	 * @return array<string,mixed>|null
	 */
	protected function decodeEventParameters( stdClass $log ): ?array {
		$event_name = $this->getEventName( $log );
		if ( is_null( $event_name ) ) {
			assert( false, '[F88AADB9] Unknown event name.' );
			return null;
		}

		$abi = $this->get();

		// indexedな引数はtopicsに格納されており、その他の引数はdataに格納されている
		// まずはイベントの引数をindexedありと無しとでそれぞれ取得
		$inputs         = array_values( array_filter( $abi, fn( $item ) => $item['name'] === $event_name ) )[0]['inputs'];
		$indexed_inputs = array_values( array_filter( $inputs, fn( $item ) => $item['indexed'] ) );
		$rest_inputs    = array_values( array_filter( $inputs, fn( $item ) => ! $item['indexed'] ) );

		// トピック数は topic0 + indexed数 と一致するはず（不正なログはここで弾く）
		/** @var list<string> */
		$topics = $log->topics;
		if ( count( $topics ) !== 1 + count( $indexed_inputs ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'[51F1606E] Topics count mismatch for event %s. expected: %d, actual: %d.',
					$event_name,
					1 + count( $indexed_inputs ),
					count( $topics )
				)
			);
		}

		$result = array();
		// indexedな引数をデコードして戻り値の配列へ追加
		if ( count( $indexed_inputs ) > 0 ) {
			foreach ( $indexed_inputs as $index => $input ) {
				$word                     = $topics[ $index + 1 ]; // イベントの引数が記録されるのはインデックスが1から
				$decoded                  = $this->decodeWord( $input['type'], $word, "[9E00E279] Topics word {$index} of event {$event_name}" );
				$result[ $input['name'] ] = $this->correctDecodedValue( $input['type'], $decoded );
			}
		}
		// dataに格納されている引数をデコードして戻り値の配列へ追加
		if ( count( $rest_inputs ) > 0 ) {
			$data = $this->stripZeroPrefix( (string) $log->data );
			// 全対応型が静的型のため head/tail 分離は不要。data は64hex × 非indexed数のワード列であることだけ検証する
			if ( strlen( $data ) !== 64 * count( $rest_inputs ) ) {
				throw new InvalidArgumentException(
					sprintf(
						'[EAFD007E] Data length mismatch for event %s. expected: %d hex chars, actual: %d.',
						$event_name,
						64 * count( $rest_inputs ),
						strlen( $data )
					)
				);
			}
			foreach ( $rest_inputs as $index => $input ) {
				$word                     = substr( $data, $index * 64, 64 );
				$decoded                  = $this->decodeWord( $input['type'], $word, "[85544FEA] Data word {$index} of event {$event_name}" );
				$result[ $input['name'] ] = $this->correctDecodedValue( $input['type'], $decoded );
			}
		}

		return $result;
	}

	/**
	 * 32バイトワードをデコードします（イベントログ専用）。
	 *
	 * 戻り値は web3p と同一（address は '0x' + 40hex の文字列、uint256 は phpseclib4 BigInteger）。
	 * web3p が黙認していた不正な入力（ワード長・16進形式・未対応型）は
	 * ID 付き InvalidArgumentException で拒否する厳格実装。
	 * AbiCodec::decodeWord（eth_call 用・戻り値は10進文字列）とは役務が異なる。
	 * protected はテスト用サブクラスでの expose を許容するため。
	 *
	 * @param string $type          ABIの引数型
	 * @param string $word          32バイトワード（0x プレフィックスあり・なしのどちらも受理。大文字混在は受理）
	 * @param string $error_context 例外メッセージに付加するエラー発生箇所の説明
	 * @return mixed address は '0x' + 40hex の文字列、uint256 は BigInteger
	 */
	protected function decodeWord( string $type, string $word, string $error_context ): mixed {
		$hex = $this->stripZeroPrefix( $word );
		if ( preg_match( '/^[0-9a-fA-F]{64}$/', $hex ) !== 1 ) {
			throw new InvalidArgumentException(
				sprintf( '[FD7233AC] %s is not a 32-byte hex word. type: %s, word: %s.', $error_context, $type, $word )
			);
		}

		return match ( $type ) {
			'address' => '0x' . substr( $hex, 24, 40 ),
			'uint256' => new BigInteger( $hex, 16 ),
			default   => throw new InvalidArgumentException(
				sprintf( '[CDE7721A] %s has an unsupported type for event decoding. type: %s.', $error_context, $type )
			),
		};
	}

	/**
	 * 16進文字列の 0x プレフィックスを除去します。
	 *
	 * web3p の Utils::stripZero と同等（プレフィックス除去のみ。大文字小文字はそのまま）。
	 */
	private function stripZeroPrefix( string $hex ): string {
		return str_starts_with( $hex, '0x' ) ? substr( $hex, 2 ) : $hex;
	}

	/**
	 * web3-phpでデコードされた値を補正します。
	 */
	private function correctDecodedValue( string $type, $value ) {
		if ( $type === 'address' ) {
			return ( Address::from( $value ) )->value(); // チェックサムアドレスに変換
		}

		return $value;
	}
}
