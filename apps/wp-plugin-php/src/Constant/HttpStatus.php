<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Constant;

/** HTTPステータスコードを定義するクラス */
class HttpStatus {
	/** 200 OK */
	public const OK = 200;
	/** 400 Bad Request */
	public const BAD_REQUEST = 400;
	/** 401 Unauthorized */
	public const UNAUTHORIZED = 401;
	/** 402 Payment Required */
	public const PAYMENT_REQUIRED = 402;
	/** 403 Forbidden */
	public const FORBIDDEN = 403;
	/** 500 Internal Server Error */
	public const INTERNAL_SERVER_ERROR = 500;
}
