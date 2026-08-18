<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\GraphQL;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\UseCase\GraphQL\ResolveChain;
use Baywall\Core\Application\UseCase\GraphQL\ResolveChains;
use Baywall\Core\Application\UseCase\GraphQL\ResolveIssueAccessTokenByErc4361Signature;
use Baywall\Core\Application\UseCase\GraphQL\ResolveIssueErc4361Message;
use Baywall\Core\Application\UseCase\GraphQL\ResolveIssueInvoiceV2;
use Baywall\Core\Application\UseCase\GraphQL\ResolveInstallOriginUrlChanged;
use Baywall\Core\Application\UseCase\GraphQL\ResolveLogs;
use Baywall\Core\Application\UseCase\GraphQL\ResolveNativeToken;
use Baywall\Core\Application\UseCase\GraphQL\ResolveNetworkCategories;
use Baywall\Core\Application\UseCase\GraphQL\ResolveNetworkCategory;
use Baywall\Core\Application\UseCase\GraphQL\ResolveOracle;
use Baywall\Core\Application\UseCase\GraphQL\ResolveOracles;
use Baywall\Core\Application\UseCase\GraphQL\ResolvePaused;
use Baywall\Core\Application\UseCase\GraphQL\ResolvePluginVersion;
use Baywall\Core\Application\UseCase\GraphQL\ResolvePost;
use Baywall\Core\Application\UseCase\GraphQL\ResolvePurgeOnUninstall;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSalesHistories;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSaveChain;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSaveOracle;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSaveSeller;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSaveSiteSettings;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSaveToken;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSeller;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSellingContent;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSellingPrice;
use Baywall\Core\Application\UseCase\GraphQL\ResolveServerSigner;
use Baywall\Core\Application\UseCase\GraphQL\ResolveSctaUrl;
use Baywall\Core\Application\UseCase\GraphQL\ResolveThemeSetting;
use Baywall\Core\Application\UseCase\GraphQL\ResolveToken;
use Baywall\Core\Application\UseCase\GraphQL\ResolveTokens;
use Psr\Container\ContainerInterface;

class RootValue {

	private ContainerInterface $container;
	private AppLogger $logger;

	public function __construct( ContainerInterface $container, AppLogger $logger ) {
		$this->container = $container;
		$this->logger    = $logger;
	}

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
			'post'                               => ResolvePost::class,
			'salesHistories'                     => ResolveSalesHistories::class,
			'seller'                             => ResolveSeller::class,
			'serverSigner'                       => ResolveServerSigner::class,
			'tokens'                             => ResolveTokens::class,
			'nativeToken'                        => ResolveNativeToken::class,
			'paused'                             => ResolvePaused::class,
			'sctaUrl'                            => ResolveSctaUrl::class,
			'themeSetting'                       => ResolveThemeSetting::class,
			'purgeOnUninstall'                   => ResolvePurgeOnUninstall::class,
			'installOriginUrlChanged'            => ResolveInstallOriginUrlChanged::class,
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
