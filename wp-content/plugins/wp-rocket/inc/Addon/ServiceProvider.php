<?php
namespace WP_Rocket\Addon;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Addon\Sucuri\Subscriber as SucuriSubscriber;
use WP_Rocket\Addon\WebP\AdminSubscriber as WebPAdminSubscriber;
use WP_Rocket\Addon\WebP\Subscriber as WebPSubscriber;
use WPMedia\Cloudflare\APIClient;
use WPMedia\Cloudflare\Cloudflare;
use WPMedia\Cloudflare\Subscriber as CloudflareSubscriber;

/**
 * Service provider for WP Rocket addons.
 *
 * @since 3.3
 * @since 3.5 - renamed and moved into this module.
 */
class ServiceProvider extends AbstractServiceProvider {

	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'sucuri_subscriber',
		'webp_subscriber',
		'webp_admin_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		// Sucuri Addon.
		$this->getContainer()->share( 'sucuri_subscriber', SucuriSubscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );

		// Cloudflare Addon.
		$this->addon_cloudflare( $options );

		$this->getContainer()->share( 'webp_admin_subscriber', WebPAdminSubscriber::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cdn_subscriber' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addTag( 'common_subscriber' );

		$this->getContainer()->share( 'webp_subscriber', WebPSubscriber::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'cdn_subscriber' ) )
			->addTag( 'common_subscriber' );
	}

	/**
	 * Adds Cloudflare Addon into the Container when the addon is enabled.
	 *
	 * @since 3.5
	 *
	 * @param Options_Data $options Instance of options.
	 */
	protected function addon_cloudflare( Options_Data $options ) {
		// If the option is not enabled, bail out. Don't load the addon.
		if ( ! (bool) $options->get( 'do_cloudflare', false ) ) {
			return;
		}

		$this->provides[] = 'cloudflare_subscriber';

		$this->getContainer()->add( 'cloudflare_api', APIClient::class )
			->addArgument( rocket_get_constant( 'WP_ROCKET_VERSION' ) );
		$this->getContainer()->add( 'cloudflare', Cloudflare::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cloudflare_api' ) );
		$this->getContainer()->share( 'cloudflare_subscriber', CloudflareSubscriber::class )
			->addArgument( $this->getContainer()->get( 'cloudflare' ) )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addTag( 'cloudflare_subscriber' );
	}
}
