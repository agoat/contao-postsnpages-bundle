<?php
/*
 * Posts'n'pages extension for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2017
 * @package    contao-postsnpages
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
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
		$loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
		
		if (!$container->hasParameter('contao.posts.formats'))
		{
			$container->setParameter('contao.posts.formats', ['standard', 'aside', 'link', 'quote', 'status', 'image', 'gallery', 'video', 'chat']);
		}

		if (!$container->hasParameter('contao.permalink.posts'))
		{
			$container->setParameter('contao.permalink.posts', '{{year}}/{{alias}}');
		}

        $loader->load('services.yml');	
    }
}
