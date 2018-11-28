<?php

namespace Medelse\UtmCookieBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MedelseUtmCookieExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('medelse_utm_cookie.utm_cookie');
        $definition->addMethodCall('setName', [$config['name']]);
        $definition->addMethodCall('setLifetime', [$config['lifetime']]);
        $definition->addMethodCall('setPath', [$config['path']]);
        $definition->addMethodCall('setDomain', [$config['domain']]);
        $definition->addMethodCall('setOverwrite', [$config['overwrite']]);
        $definition->addMethodCall('setSecure', [$config['secure']]);
        $definition->addMethodCall('setHttponly', [$config['httponly']]);
        if ($config['auto_init']) {
            $definition->addTag(
                'kernel.event_listener',
                ['event' => 'kernel.request', 'method' => 'onKernelRequest']
            );
        }
    }
}
