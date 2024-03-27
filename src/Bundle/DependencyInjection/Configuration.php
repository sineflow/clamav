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
                ->scalarNode('socket')->defaultValue(null)->end()
                ->scalarNode('host')->defaultValue(null)->end()
                ->scalarNode('port')->defaultValue(null)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
