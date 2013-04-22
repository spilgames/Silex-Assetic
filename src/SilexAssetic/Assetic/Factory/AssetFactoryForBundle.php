<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SilexAssetic\Assetic\Factory;

use Assetic\Factory\AssetFactory as BaseAssetFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads asset formulae from the filesystem.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class AssetFactoryForBundle extends BaseAssetFactory
{
    private $kernel;

    /**
     * Constructor.
     *
     * @param Silex\Application $app The app object
     */
    public function __construct(\Silex\Application $app)
    {
        $this->kernel = $app['app_kernel'];

        parent::__construct($app['assetic.path_to_web'], $app['debug']);
    }

    /**
     * Adds support for bundle notation file and glob assets and parameter placeholders.
     *
     * FIXME: This is a naive implementation of globs in that it doesn't
     * attempt to support bundle inheritance within the glob pattern itself.
     */
    protected function parseInput($input, array $options = array())
    {
        // expand bundle notation
        if ('@' == $input[0] && false !== strpos($input, '/')) {
            // use the bundle path as this asset's root
            $bundle = substr($input, 1);
            if (false !== $pos = strpos($bundle, '/')) {
                $bundle = substr($bundle, 0, $pos);
            }
            $options['root'] = array($this->kernel->getBundle($bundle)->getPath());

            // canonicalize the input
            if (false !== $pos = strpos($input, '*')) {
                // locateResource() does not support globs so we provide a naive implementation here
                list($before, $after) = explode('*', $input, 2);
                $input = $this->kernel->locateResource($before).'*'.$after;
            } else {
                $input = $this->kernel->locateResource($input);
            }
        }
        
        return parent::parseInput($input, $options);
    }

    protected function createAssetReference($name)
    {
        if (!$this->getAssetManager()) {
            $this->setAssetManager($this->container->get('assetic.asset_manager'));
        }

        return parent::createAssetReference($name);
    }

    protected function getFilter($name)
    {
        if (!$this->getFilterManager()) {
            $this->setFilterManager($this->container->get('assetic.filter_manager'));
        }

        return parent::getFilter($name);
    }
}

