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

namespace Agoat\PostsnPagesBundle\Contao;

use \Contao\Controller as ContaoController;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ArticleListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\PageOperationListener;


/**
 * Controller class
 */
class DataContainer extends ContaoController
{
	/**
	 * Hide the whole article content stuff
	 */
	public static function hideArticles()
	{
		// Remove articles from the backend module array
		unset($GLOBALS['BE_MOD']['content']['article']);

		// Remove article related modules
		unset($GLOBALS['FE_MOD']['navigationMenu']['articlenav']);
		unset($GLOBALS['FE_MOD']['miscellaneous']['articlelist']);

		// Remove article related content elements
		unset($GLOBALS['TL_CTE']['includes']['article']);
		unset($GLOBALS['TL_CTE']['includes']['teaser']);
	}	
}
