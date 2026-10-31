<?php
declare(strict_types=1);
namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\Service\AdminNotificationService;
use Baywall\Core\Application\ValueObject\EmailAddress;
use WP_User_Query;

class WpAdminNotificationService implements AdminNotificationService {

	public function __construct( private readonly AppLogger $logger ) {}

	/** @inheritdoc */
	public function notify( string $subject, string $message ): void {
		// メールアドレスが管理者同士で相互に見えないよう、宛先ごとに個別送信する
		foreach ( $this->adminEmailAddresses() as $email ) {
			if ( ! wp_mail( $email->value(), $subject, $message ) ) {
				// notify()は例外を送出せずbest-effortで動作する（契約はvoidのまま）
				$this->logger->error( '[1F2C72FC] Failed to send admin notification mail to administrators.' );
			}
		}
	}

	/**
	 * 管理者（`manage_options`保持者）のメールアドレスを収集します。
	 *
	 * 不正なアドレスはEmailAddressへの変換で除外し、同一アドレスは初出順に1件へ集約します。
	 *
	 * @return list<EmailAddress>
	 */
	private function adminEmailAddresses(): array {
		$query = new WP_User_Query(
			array(
				'capability' => 'manage_options',
				'fields'     => 'user_email',
			)
		);

		$addresses = array();
		foreach ( $query->get_results() as $email ) {
			// 念のため文字列以外の値を除外する
			if ( ! is_string( $email ) ) {
				continue;
			}
			try {
				$email_address = EmailAddress::from( $email );
			} catch ( \InvalidArgumentException $e ) {
				// 除外の発生を目視で発見できるよう、不正アドレスの値（例外メッセージに含む）をログに残してから除外する
				$this->logger->error( '[7721D69B] Excluded an administrator email address: ' . $e->getMessage() );
				continue;
			}
			$addresses[ $email_address->value() ] = $email_address;
		}
		return array_values( $addresses );
	}
}
