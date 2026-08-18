<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks\Base;

/** フックの基底クラス */
abstract class HookBase {
	/** フックを登録します */
	abstract public function register(): void;
}
