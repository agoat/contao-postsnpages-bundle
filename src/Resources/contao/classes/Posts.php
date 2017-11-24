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

namespace Agoat\PostsnPages;


/**
 * Posts class
 */
class Posts extends \Frontend
{

	/**
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache;
	
	
	/**
	 * Generate a URL and return it as string
	 *
	 * @param \PostModel $objPost
	 * @param boolean    $blnAlternativeLink
	 * @param boolean    $intJumpTo
	 *
	 * @return string
	 */
	public static function generatePostUrl ($objPost, $blnAlternativeLink=false, $intJumpTo=false)
	{
		if (!$objPost instanceof \PostsModel)
		{
			return;
		}
	
		$strCacheKey = 'id_' . $objPost->id;
		
		// Load the URL from cache
		if (isset(self::$arrUrlCache[$strCacheKey]))
		{
			return self::$arrUrlCache[$strCacheKey];
		}
		
		// Initialize the cache
		self::$arrUrlCache[$strCacheKey] = null;
		
		if ($objPost->alternativeLink && $blnAlternativeLink)
		{
			self::$arrUrlCache[$strCacheKey] = $objPost->url;
		}
		else
		{
			$objArchive = \ArchiveModel::findByPk($objPost->pid);
			$objPage = \PageModel::findWithDetails($intJumpTo ?: $objArchive->pid);
			
			$urlGenerator = \System::getContainer()->get('contao.routing.url_generator');

			self::$arrUrlCache[$strCacheKey] = $urlGenerator->generate
			(
				($objPage->alias ?: $objPage->id) . '/posts/' . ($objPost->alias ?: $objPost->id),
				array
				(
					'_locale' => ($strForceLang ?: $objPage->rootLanguage),
					'_domain' => $objPage->domain,
					'_ssl' => (bool) $objPage->rootUseSSL,
				)
			);
		}

		return self::$arrUrlCache[$strCacheKey];
	}

	
	/**
	 * Render the content of a post article
	 *
	 * @param \PostModel $objPost
	 *
	 * @return array
	 */
	public static function getPostContent ($objPost)
	{
		$arrElements = array();
		$objCte = \ContentModel::findPublishedByPidAndTable($objPost->id, 'tl_posts');

		if ($objCte !== null)
		{
			$intCount = 0;
			$intLast = $objCte->count() - 1;

			while ($objCte->next())
			{
				$arrCss = array();

				/** @var ContentModel $objRow */
				$objRow = $objCte->current();

				// Add the "first" and "last" classes (see #2583)
				if ($intCount == 0 || $intCount == $intLast)
				{
					if ($intCount == 0)
					{
						$arrCss[] = 'first';
					}

					if ($intCount == $intLast)
					{
						$arrCss[] = 'last';
					}
				}

				$objRow->classes = $arrCss;
				$arrElements[] = parent::getContentElement($objRow);
				++$intCount;
			}
		}

		return $arrElements;
	}
}
