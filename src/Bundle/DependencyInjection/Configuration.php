<?php

namespace Sineflow\ClamAV\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sineflow_clam_av');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('strategy')->defaultValue('clamd_unix')->cannotBeEmpty()->end()
                ->scalarNode('socket')->defaultNull()->end()
                ->scalarNode('host')->defaultNull()->end()
                ->integerNode('port')->defaultNull()->end()
                ->integerNode('socket_timeout')->defaultNull()->info('Socket read/write timeout in seconds. Null means no timeout.')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
