<?php
 
 /**
 * Contao Open Source CMS - Content management extension
 *
 * Copyright (c) 2017 Arne Stappen (aGoat)
 *
 *
 * @package   contentblocks
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
 */

namespace Agoat\PostsnPages;


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
	 * @param PostModel $objPost
	 * @param boolean   $blnNoAlternativeLink
	 *
	 * @return string
	 */
	public static function generatePostUrl($objPost, $blnNoAlternativeLink=false)
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
		
		if ($objPost->alternativeLink && !$blnNoAlternativeLink)
		{
			self::$arrUrlCache[$strCacheKey] = $objPost->url;
		}
		else
		{
			$objArchive = \ArchiveModel::findByPk($objPost->pid);
			$objPage = \PageModel::findByPk($objArchive->pid);
			
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


}