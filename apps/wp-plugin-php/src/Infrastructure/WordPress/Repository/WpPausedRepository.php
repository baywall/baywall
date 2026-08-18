<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Repository;

use Baywall\Core\Domain\Repository\PausedRepository;
use Baywall\Core\Infrastructure\WordPress\Database\OptionGateway\Option\BoolOption;
use Baywall\Core\Infrastructure\WordPress\Constants\WpOptionName;

/**
 * 一時停止状態を取得または保存するクラス
 */
class WpPausedRepository implements PausedRepository {

	private BoolOption $option;

	public function __construct() {
		$this->option = new BoolOption( WpOptionName::PAUSED );
	}

	/** 一時停止状態を取得します */
	public function get(): bool {
		$paused = $this->option->get();
		return $paused ?? false;
	}

	/** 一時停止状態を保存します */
	public function save( bool $paused ): void {
		$this->option->update( $paused );
	}

	/** 一時停止状態を削除します */
	public function delete(): void {
		$this->option->delete();
	}
}
