<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Application\ValueObject\Erc4361NonceString;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject\Erc4361NonceTableRecord;

class Erc4361NonceTable {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->erc4361Nonce();
	}

	public function get( Address $address ): ?Erc4361NonceTableRecord {
		$sql = $this->wpdb->named_prepare(
			<<<SQL
				SELECT `erc4361_nonce`, `wallet_address`, `issued_at`
				FROM `{$this->table_name}`
				WHERE `wallet_address` = :wallet_address
				LIMIT 1
			SQL,
			array( ':wallet_address' => $address->value() )
		);

		$record = $this->wpdb->get_row( $sql );

		return $record === null ? null : new Erc4361NonceTableRecord( $record );
	}

	public function save( Address $address, Erc4361NonceString $erc4361_nonce_string, UnixTimestamp $issued_at ): void {
		$sql = $this->wpdb->named_prepare(
			<<<SQL
				INSERT INTO `{$this->table_name}` (
					`wallet_address`,
					`erc4361_nonce`,
					`issued_at`
				) VALUES (
					:wallet_address,
					:erc4361_nonce,
					:issued_at
				)
				ON DUPLICATE KEY UPDATE
					`erc4361_nonce` = VALUES(`erc4361_nonce`),
					`issued_at` = VALUES(`issued_at`)
			SQL,
			array(
				':wallet_address' => $address->value(),
				':erc4361_nonce'  => $erc4361_nonce_string->value(),
				':issued_at'      => $issued_at->toMySqlValue(),
			)
		);

		$result = $this->wpdb->query( $sql );
		assert( $result === 1 || $result === 2, '[E8615BF7]' );
	}

	public function delete( Address $address ): void {
		$sql = $this->wpdb->named_prepare(
			<<<SQL
				DELETE FROM `{$this->table_name}`
				WHERE `wallet_address` = :wallet_address
			SQL,
			array( ':wallet_address' => $address->value() )
		);

		$this->wpdb->query( $sql );
	}

	public function deleteExpired( UnixTimestamp $target_time ): void {
		$sql = $this->wpdb->named_prepare(
			<<<SQL
				DELETE FROM `{$this->table_name}`
				WHERE `issued_at` < :target_time
			SQL,
			array( ':target_time' => $target_time->toMySqlValue() )
		);

		$this->wpdb->query( $sql );
	}
}
