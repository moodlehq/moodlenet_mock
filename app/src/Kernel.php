<?php

// src/Kernel.php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @return array<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): array
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
        ];

        return $bundles;
    }

    protected function configureContainer(ContainerConfigurator $containerConfigurator): void
    {
        // Picks up the framework config.
        $containerConfigurator->import('../config/{packages}/*.yaml');

        // register all classes in /src/ as service
        $containerConfigurator->services()
            ->load('App\\', __DIR__.'/*')
            ->autowire()
            ->autoconfigure()
        ;

        $containerConfigurator->parameters()->set('app.mock_oauth2_client_id', '%env(MOCK_OAUTH2_CLIENT_ID)%');
        $containerConfigurator->parameters()->set('app.mock_oauth2_client_secret', '%env(MOCK_OAUTH2_CLIENT_SECRET)%');
        $containerConfigurator->parameters()->set('app.mock_oauth2_authcode', '%env(MOCK_OAUTH2_AUTHCODE)%');
        $containerConfigurator->parameters()->set('app.mock_oauth2_access_token', '%env(MOCK_OAUTH2_ACCESS_TOKEN)%');
        $containerConfigurator->parameters()->set('app.mock_oauth2_refresh_token', '%env(MOCK_OAUTH2_REFRESH_TOKEN)%');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // load the routes defined as PHP attributes
        // (use 'annotation' as the second argument if you define routes as annotations)
        $routes->import(__DIR__.'/Controller/', 'attribute');
    }
}
