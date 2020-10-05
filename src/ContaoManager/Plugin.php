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

namespace Agoat\PostsnPagesBundle\ContaoManager;

use Agoat\PostsnPagesBundle\AgoatPostsnPagesBundle;
use Agoat\LanguageRelationBundle\AgoatLanguageRelationBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Dependency\DependentPluginInterface;


/**
 * Plugin for the Contao Manager.
 */
class Plugin implements BundlePluginInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getBundles(ParserInterface $parser)
	{
		return [
			BundleConfig::create(AgoatPostsnPagesBundle::class)
				->setLoadAfter([
				    ContaoCoreBundle::class,
                    AgoatLanguageRelationBundle::class
                ])
		];
	}
}
