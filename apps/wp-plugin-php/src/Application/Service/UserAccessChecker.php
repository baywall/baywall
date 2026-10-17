<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Domain\Exception\AuthenticationFailedException;
use Baywall\Core\Domain\Exception\PermissionDeniedException;
use Baywall\Core\Domain\ValueObject\PostId;

class UserAccessChecker {
	public function __construct( private readonly UserAccessProvider $user_access_provider ) {}


	/**
	 * 現在のユーザーで指定した投稿を閲覧できるかをチェックし、閲覧できない場合は例外をスローします
	 *
	 * @param bool|null $is_authenticated 認証済みが確定している経路からのヒント。null の場合はログイン状態から判定します
	 */
	public function checkCanViewPost( PostId $post_id, ?bool $is_authenticated = null ): void {
		if ( ! $this->user_access_provider->canViewPost( $post_id ) ) {
			$this->throwDenied(
				$is_authenticated ?? $this->user_access_provider->isLoggedIn(),
				"[E14DA20F] You do not have permission to view this post. post_id: {$post_id}"
			);
		}
	}

	/** 現在のユーザーが管理者権限を持っているかどうかをチェックし、持っていない場合は例外をスローします */
	public function checkHasAdminRole(): void {
		if ( ! $this->user_access_provider->hasAdminRole() ) {
			$this->throwDenied( $this->user_access_provider->isLoggedIn(), '[E61EB62A] You do not have admin access.' );
		}
	}

	/** 現在のユーザーが投稿を新規作成できるかどうかをチェックし、できない場合は例外をスローします */
	public function checkCanCreatePost(): void {
		if ( ! $this->user_access_provider->canCreatePost() ) {
			$this->throwDenied( $this->user_access_provider->isLoggedIn(), '[6332E030] You do not have permission to create a post.' );
		}
	}

	/** 拒否を認証状態で振り分けてスローします（未認証 → 401、認証済みだが権限なし → 403） */
	private function throwDenied( bool $is_authenticated, string $permission_denied_message ): void {
		if ( ! $is_authenticated ) {
			throw new AuthenticationFailedException( '[3F2B6AB2] You must be logged in.' );
		}
		throw new PermissionDeniedException( $permission_denied_message );
	}
}
