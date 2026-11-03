<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Dto\LogDto;
use Baywall\Core\Application\UseCase\GetLogs;

class ResolveLogs implements Resolver {
	public function __construct(
		private readonly GetLogs $get_logs
	) {}

	/** @return array<string, mixed>[] */
	public function handle( array $root_value, array $args ) {
		$logs = $this->get_logs->handle();

		return array_map(
			function ( LogDto $log ) {
				return array(
					'id'        => $log->id,
					'createdAt' => $log->createdAt,
					'level'     => $log->level,
					'category'  => $log->category,
					'message'   => $log->message,
				);
			},
			$logs
		);
	}
}
