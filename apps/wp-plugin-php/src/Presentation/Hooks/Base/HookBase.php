<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\Hooks\Base;

/** フックの基底クラス */
abstract class HookBase {

	protected const HTTP_STATUS_200_OK               = 200;
	protected const HTTP_STATUS_401_UNAUTHORIZED     = 401;
	protected const HTTP_STATUS_402_PAYMENT_REQUIRED = 402;

	/** フックを登録します */
	abstract public function register(): void;
}
