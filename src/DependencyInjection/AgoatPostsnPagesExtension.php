<?php

/*
 * This file is part of the Extended Articles Extension.
 *
 * Copyright (c) 2017 Arne Stappen (alias aGoat)
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PostsnPagesBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


/**
 * Adds the bundle services to the container.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class AgoatPostsnPagesExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
		// Set valid extensions paramater if not set anyway
		if (!$container->hasParameter('contao.posts.formats'))
		{
			$container->setParameter('contao.posts.formats', ['standard', 'aside', 'link', 'quote', 'status', 'image', 'gallery', 'video', 'chat']);
		}

		$loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
		
        //$loader->load('listener.yml');
        $loader->load('services.yml');		
    }
}
