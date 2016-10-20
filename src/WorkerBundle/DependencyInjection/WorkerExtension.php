<?php

namespace Alchemy\WorkerBundle\DependencyInjection;

use Alchemy\Queue\MessageQueueRegistry;
use Alchemy\QueueBundle\DependencyInjection\QueueConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WorkerExtension extends ConfigurableExtension
{

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new QueueConfiguration('alchemy_worker');
    }

    /**
     * Loads a specific configuration.
     *
     * @param array $config An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yml');

        $registry = new Definition(MessageQueueRegistry::class);
        $container->setDefinition('alchemy_queues.registry', $registry);

        $queueConfigurations = $config['queues'];

        if ($config['logger'] != '') {
            $registry->addMethodCall('setLogger', [new Reference($config['logger'])]);
        }

        foreach ($queueConfigurations as $name => $configuration) {
            $targetRegistry = $registry;

            if (isset($configuration['registry'])) {
                $targetRegistry = $container->getDefinition($configuration['registry']);
            }

            $targetRegistry->addMethodCall('bindConfiguration', [$name, $configuration]);
        }
    }
}
