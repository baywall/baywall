<?php
/**
 * Plugin Name:       baywall
 * Description:       You can set up a paywall for blockchain payments.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           0.0.7-alpha.1
 * Author:            yamaneyuta
 * License:           Split License
 * License URI:       ./LICENSE
 * Update URI:        https://baywall.net/
 * Text Domain:       baywall
 * Domain Path:       /languages
 */

// [Header Requirements](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/)

declare(strict_types=1);

use Baywall\Core\Infrastructure\DI\ContainerDefinitions;
use Baywall\Core\Presentation\Hooks\AdminPageHook;
use Baywall\Core\Presentation\Hooks\ContentHook;
use Baywall\Core\Presentation\Hooks\AppContractCrawlCronHook;
use Baywall\Core\Presentation\Hooks\Base\HookBase;
use Baywall\Core\Presentation\Hooks\GraphQLHook;
use Baywall\Core\Presentation\Hooks\LogCleanupCronHook;
use Baywall\Core\Presentation\Hooks\PluginUpdateCheckHook;
use Baywall\Core\Presentation\Hooks\PluginUpdateHook;
use Baywall\Core\Presentation\Hooks\PostEditHook;
use Baywall\Core\Presentation\Hooks\RestApiHook;
use Baywall\Core\Presentation\Hooks\ViewPageHook;
use DI\ContainerBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ライブラリ読み込み
require_once __DIR__ . '/vendor/autoload.php';

$main = function () {
	$containerBuilder = new ContainerBuilder();
	$containerBuilder->addDefinitions( ContainerDefinitions::getDefinitions() );
	$container = $containerBuilder->build();

	/** @var class-string<HookBase>[] $hook_classes */
	$hook_classes = array(
		PluginUpdateHook::class, // プラグインの初期化
		GraphQLHook::class,      // GraphQLのAPI登録
		RestApiHook::class,    // REST APIの登録(GraphQL以外)
		AppContractCrawlCronHook::class, // AppコントラクトをクロールするCronの登録
		AdminPageHook::class,    // 管理画面
		PostEditHook::class,     // 投稿(新規/編集)画面
		ViewPageHook::class,     // 投稿表示画面
		ContentHook::class,    // 投稿を保存または取得する時のフィルタ処理
		LogCleanupCronHook::class, // ログクリーンアップCronの登録
		PluginUpdateCheckHook::class, // プラグインの自動アップデートチェック
	);
	foreach ( $hook_classes as $hook_class ) {
		$container->get( $hook_class )->register();
	}
};

$main();
