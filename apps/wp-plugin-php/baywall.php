<?php
/**
 * Plugin Name:       baywall
 * Description:       You can set up a paywall for cryptocurrency or stablecoin payments.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           0.0.1
 * Author:            yamaneyuta
 * License:           Split License
 * License URI:       ./LICENSE
 * Text Domain:       baywall
 * Domain Path:       /languages
 */

// [Header Requirements](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/)

declare(strict_types=1);

use Cornix\Serendipity\Core\Infrastructure\DI\ContainerDefinitions;
use Cornix\Serendipity\Core\Presentation\Hooks\AdminPageHook;
use Cornix\Serendipity\Core\Presentation\Hooks\ContentHook;
use Cornix\Serendipity\Core\Presentation\Hooks\AppContractCrawlCronHook;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use Cornix\Serendipity\Core\Presentation\Hooks\GraphQLHook;
use Cornix\Serendipity\Core\Presentation\Hooks\PluginUpdateHook;
use Cornix\Serendipity\Core\Presentation\Hooks\PostEditHook;
use Cornix\Serendipity\Core\Presentation\Hooks\RestApiHook;
use Cornix\Serendipity\Core\Presentation\Hooks\ViewPageHook;
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
	);
	foreach ( $hook_classes as $hook_class ) {
		$container->get( $hook_class )->register();
	}
};

$main();
