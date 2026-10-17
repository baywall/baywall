<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\GraphQL;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveChain;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveChains;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveIssueAccessTokenByErc4361Signature;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveIssueErc4361Message;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveIssueInvoiceV2;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSiteUrlChangedSinceInstall;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveLogs;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveNativeToken;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveNetworkCategories;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveNetworkCategory;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveOracle;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveOracles;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolvePayableTokens;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolvePaused;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolvePluginVersion;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolvePost;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolvePurgeOnUninstall;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSalesHistories;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSalesHistoriesCount;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveChain;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveOracle;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveSeller;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveSiteSettings;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSaveToken;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSeller;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSellingContent;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSellingPrice;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveServerSigner;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveSctaUrl;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveThemeSetting;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveToken;
use Baywall\Core\Presentation\GraphQL\Resolvers\ResolveTokens;
use DI\Container;

class RootValue {


	public function __construct(
		private readonly Container $container,
		private readonly AppLogger $logger
	) {}

	/**
	 * @return array<string, mixed>
	 */
	public function get(): array {

		/** @var array<string,string> */
		$resolvers = array(
			// 非公開
			'chain'                              => ResolveChain::class,
			'oracle'                             => ResolveOracle::class,
			'networkCategory'                    => ResolveNetworkCategory::class,
			'sellingContent'                     => ResolveSellingContent::class,
			'sellingPrice'                       => ResolveSellingPrice::class,
			'token'                              => ResolveToken::class,

			// Query
			'chains'                             => ResolveChains::class,
			'networkCategories'                  => ResolveNetworkCategories::class,
			'oracles'                            => ResolveOracles::class,
			'payableTokens'                      => ResolvePayableTokens::class,
			'post'                               => ResolvePost::class,
			'salesHistories'                     => ResolveSalesHistories::class,
			'salesHistoriesCount'                => ResolveSalesHistoriesCount::class,
			'seller'                             => ResolveSeller::class,
			'serverSigner'                       => ResolveServerSigner::class,
			'tokens'                             => ResolveTokens::class,
			'nativeToken'                        => ResolveNativeToken::class,
			'paused'                             => ResolvePaused::class,
			'sctaUrl'                            => ResolveSctaUrl::class,
			'themeSetting'                       => ResolveThemeSetting::class,
			'purgeOnUninstall'                   => ResolvePurgeOnUninstall::class,
			'siteUrlChangedSinceInstall'         => ResolveSiteUrlChangedSinceInstall::class,
			'pluginVersion'                      => ResolvePluginVersion::class,
			'logs'                               => ResolveLogs::class,

			// Mutation
			'issueInvoiceV2'                     => ResolveIssueInvoiceV2::class,
			'issueErc4361Message'                => ResolveIssueErc4361Message::class,
			'issueAccessTokenByErc4361Signature' => ResolveIssueAccessTokenByErc4361Signature::class,
			'saveChain'                          => ResolveSaveChain::class,
			'saveOracle'                         => ResolveSaveOracle::class,
			'saveSeller'                         => ResolveSaveSeller::class,
			'saveSiteSettings'                   => ResolveSaveSiteSettings::class,
			'saveToken'                          => ResolveSaveToken::class,
		);

		$result = array();
		foreach ( $resolvers as $field => $resolver ) {
			$result[ $field ] = function ( array $root_value, array $args ) use ( $resolver ) {
				try {
					$resolver = $this->container->get( $resolver );
					return $resolver->handle( $root_value, $args );
				} catch ( \Throwable $e ) {
					$this->logger->error( $e );
					throw $e;
				}
			};
		}

		return $result;
	}
}
