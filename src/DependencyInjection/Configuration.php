<?php

namespace Sineflow\ClamAV\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sineflow_clamav');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('strategy')->defaultValue('clamd_unix')->cannotBeEmpty()->end()
                ->scalarNode('socket')->defaultValue(null)->end()
                ->scalarNode('host')->defaultValue(null)->end()
                ->scalarNode('port')->defaultValue(null)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
