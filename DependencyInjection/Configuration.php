<?php

namespace Medelse\UtmCookieBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('medelse_utm_cookie');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('name')->defaultValue('utm')->end()
                ->integerNode('lifetime')->defaultValue(604800)->min(0)->end()
                ->scalarNode('path')->defaultValue('/')->end()
                ->scalarNode('domain')->defaultValue('')->end()
                ->booleanNode('overwrite')->defaultTrue()->end()
                ->booleanNode('secure')->defaultFalse()->end()
                ->booleanNode('httponly')->defaultFalse()->end()
                ->booleanNode('auto_init')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
