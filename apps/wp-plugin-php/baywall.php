<?php
/**
 * Plugin Name:       baywall
 * Description:       You can set up a paywall for blockchain payments.
 * Requires at least: 6.6.9
 * Requires PHP:      8.2.35
 * Version:           1.0.0-alpha.4
 * Author:            yamaneyuta
 * License:           Split License
 * License URI:       ./LICENSE
 * Update URI:        https://baywall.net/
 * Text Domain:       baywall
 * Domain Path:       /languages
 */

// [Header Requirements](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/)

declare(strict_types=1);

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Logging\ErrorLogWriter;
use Baywall\Core\Application\Repository\InstalledPluginVersionRepository;
use Baywall\Core\Application\Port\Clock;
use Baywall\Core\Application\Port\PluginDeactivationService;
use Baywall\Core\Application\Port\PluginAutoUpdateStateProvider;
use Baywall\Core\Application\Port\PluginMigrationService;
use Baywall\Core\Application\Port\PluginCompatibilityChecker;
use Baywall\Core\Application\Port\UserAccessProvider;
use Baywall\Core\Application\Service\EnsRpcUrlResolver;
use Baywall\Core\Application\Service\LockRunner;
use Baywall\Core\Application\UseCase\CrawlAllAppContract;
use Baywall\Core\Application\UseCase\CleanupOldLogs;
use Baywall\Core\Application\UseCase\GetPaidContent;
use Baywall\Core\Application\UseCase\IssueAccessTokenByInvoiceToken;
use Baywall\Core\Application\UseCase\LogoutAuth;
use Baywall\Core\Application\UseCase\RefreshAccessToken;
use Baywall\Core\Application\UseCase\SavePostContent;
use Baywall\Core\DI\ContainerDefinitions;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\Service\CookieNameProvider;
use Baywall\Core\Infrastructure\System\RuntimeEnvironmentChecker;
use Baywall\Core\Infrastructure\System\PhpExtChecker;
use Baywall\Core\Infrastructure\GraphQL\PluginSchemaProvider;
use Baywall\Core\Infrastructure\WordPress\Content\BlockNameProvider;
use Baywall\Core\Infrastructure\WordPress\Content\GutenbergService;
use Baywall\Core\Infrastructure\WordPress\Content\HandleNameProvider;
use Baywall\Core\Infrastructure\WordPress\Content\RestPropertyProvider;
use Baywall\Core\Infrastructure\WordPress\Content\SlugProvider;
use Baywall\Core\Infrastructure\WordPress\Auth\WpNonceService;
use Baywall\Core\Infrastructure\WordPress\I18n\I18nTextProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginAssetUrlProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginInfoProvider;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginPackageChecksumVerifier;
use Baywall\Core\Infrastructure\WordPress\Plugin\WpPluginUpdateChecker;
use Baywall\Core\Infrastructure\WordPress\Service\WpAppManifestFetcher;
use Baywall\Core\Infrastructure\WordPress\Service\WpEnvironment;
use Baywall\Core\Infrastructure\WordPress\Service\WpExceptionConverter;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;
use Baywall\Core\Presentation\Hooks\AdminPageHook;
use Baywall\Core\Presentation\Hooks\ContentDeleteHook;
use Baywall\Core\Presentation\Hooks\ContentHook;
use Baywall\Core\Presentation\Hooks\ContentLoadHook;
use Baywall\Core\Presentation\Hooks\ContentSaveHook;
use Baywall\Core\Presentation\Hooks\AppContractCrawlCronHook;
use Baywall\Core\Presentation\Hooks\GraphQLHook;
use Baywall\Core\Presentation\GraphQL\RootValue;
use Baywall\Core\Presentation\Hooks\I18nHook;
use Baywall\Core\Presentation\Hooks\LogCleanupCronHook;
use Baywall\Core\Presentation\Hooks\PluginRowMetaHook;
use Baywall\Core\Presentation\Hooks\PluginUpdateCheckHook;
use Baywall\Core\Presentation\Hooks\PluginMigrationHook;
use Baywall\Core\Presentation\Hooks\PostEditHook;
use Baywall\Core\Presentation\Hooks\RestApiHook;
use Baywall\Core\Presentation\Hooks\Service\CurrentPaywalledPostResolver;
use Baywall\Core\Presentation\Hooks\Service\PhpVarExporter;
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

	// テキストドメインのロード
	$i18n_hook = new I18nHook( fn() => $container->get( WpPluginInfoProvider::class ) );
	$i18n_hook->register();

	// プラグインの初期化
	$plugin_migration_hook = new PluginMigrationHook(
		fn() => $container->get( PluginMigrationService::class ),
		fn() => $container->get( LockRunner::class ),
		fn() => $container->get( InstalledPluginVersionRepository::class ),
		fn() => $container->get( AppLogService::class ),
		fn() => $container->get( WordPressPropertyProvider::class ),
		fn() => $container->get( RuntimeEnvironmentChecker::class ),
		fn() => $container->get( PhpExtChecker::class ),
		fn() => $container->get( PluginCompatibilityChecker::class ),
		fn() => $container->get( PluginDeactivationService::class ),
	);
	$plugin_migration_hook->register();

	// GraphQLのAPI登録
	$graphql_hook = new GraphQLHook(
		fn() => $container->get( RestPropertyProvider::class ),
		fn() => $container->get( AppLogService::class ),
		fn() => $container->get( WpNonceService::class ),
		fn() => $container->get( WpExceptionConverter::class ),
		fn() => $container->get( PluginSchemaProvider::class ),
		fn() => $container->get( RootValue::class ),
	);
	$graphql_hook->register();

	// REST APIの登録(GraphQL以外)
	$rest_api_hook = new RestApiHook(
		fn() => $container->get( AppLogService::class ),
		fn() => $container->get( WpNonceService::class ),
		fn() => $container->get( WpExceptionConverter::class ),
		fn() => $container->get( CookieNameProvider::class ),
		fn() => $container->get( RefreshAccessToken::class ),
		fn() => $container->get( IssueAccessTokenByInvoiceToken::class ),
		fn() => $container->get( LogoutAuth::class ),
		fn() => $container->get( GetPaidContent::class ),
	);
	$rest_api_hook->register();

	// AppコントラクトをクロールするCronの登録
	$app_contract_crawl_cron_hook = new AppContractCrawlCronHook(
		$container->get( WpPluginInfoProvider::class ),
		fn() => $container->get( LockRunner::class ),
		fn() => $container->get( Clock::class ),
		fn() => $container->get( CrawlAllAppContract::class ),
	);
	$app_contract_crawl_cron_hook->register();

	// 管理画面
	$admin_page_hook = new AdminPageHook(
		$container->get( WpPluginInfoProvider::class ),
		fn() => $container->get( I18nTextProvider::class ),
		fn() => $container->get( SlugProvider::class ),
		fn() => $container->get( HandleNameProvider::class ),
		fn() => $container->get( PhpVarExporter::class ),
		fn() => $container->get( WpPluginAssetUrlProvider::class ),
	);
	$admin_page_hook->register();

	// 投稿(新規/編集)画面
	$post_edit_hook = new PostEditHook(
		fn() => $container->get( HandleNameProvider::class ),
		fn() => $container->get( WpPluginAssetUrlProvider::class ),
		fn() => $container->get( PhpVarExporter::class ),
	);
	$post_edit_hook->register();

	// 投稿表示画面
	$view_page_hook = new ViewPageHook(
		fn() => $container->get( CurrentPaywalledPostResolver::class ),
		fn() => $container->get( HandleNameProvider::class ),
		fn() => $container->get( WpPluginAssetUrlProvider::class ),
		fn() => $container->get( PhpVarExporter::class ),
	);
	$view_page_hook->register();

	// 投稿を保存または取得する時のフィルタ処理
	$content_hook = new ContentHook(
		new ContentSaveHook(
			$container->get( GutenbergService::class ),
			$container->get( BlockNameProvider::class ),
			$container->get( PostRepository::class ),
			$container->get( SavePostContent::class ),
		),
		new ContentLoadHook(
			$container->get( UserAccessProvider::class ),
			$container->get( GutenbergService::class ),
			$container->get( PostRepository::class ),
			$container->get( BlockNameProvider::class ),
			$container->get( CurrentPaywalledPostResolver::class ),
		),
		new ContentDeleteHook(
			$container->get( SavePostContent::class ),
		),
	);
	$content_hook->register();

	// ログクリーンアップCronの登録
	$log_cleanup_cron_hook = new LogCleanupCronHook(
		$container->get( WpPluginInfoProvider::class ),
		fn() => $container->get( LockRunner::class ),
		fn() => $container->get( Clock::class ),
		fn() => $container->get( CleanupOldLogs::class ),
		$container->get( ErrorLogWriter::class ),
	);
	$log_cleanup_cron_hook->register();

	// プラグインの自動アップデートチェック
	$plugin_update_check_hook = new PluginUpdateCheckHook(
		fn() => $container->get( WpPluginPackageChecksumVerifier::class ),
		fn() => $container->get( WpEnvironment::class ),
		fn() => $container->get( EnsRpcUrlResolver::class ),
		fn() => $container->get( WpAppManifestFetcher::class ),
		fn() => $container->get( WpPluginInfoProvider::class ),
		fn() => $container->get( WordPressPropertyProvider::class ),
		fn() => $container->get( WpPluginUpdateChecker::class ),
		fn() => $container->get( AppLogService::class ),
	);
	$plugin_update_check_hook->register();

	// プラグイン一覧の注意メッセージ
	$plugin_row_meta_hook = new PluginRowMetaHook(
		fn() => $container->get( WpPluginInfoProvider::class ),
		fn() => $container->get( WpEnvironment::class ),
		fn() => $container->get( PluginAutoUpdateStateProvider::class ),
		fn() => $container->get( EnsRpcUrlResolver::class ),
	);
	$plugin_row_meta_hook->register();
};

$main();
