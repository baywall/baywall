<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks;

/** フックを登録するインターフェース */
interface Hook {
	/** フックを登録します */
	public function register(): void;
}
