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
	 * @param NewsModel $objItem
	 * @param boolean   $blnAddArchive
	 *
	 * @return string
	 */
	public static function generatePostUrl($objPost, $objTarget=null)
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
		
		if ($objPost->readmore)
		{
			self::$arrUrlCache[$strCacheKey] = $objPost->url;
		}
		else
		{
			if (null === $objTarget && (($objArchive = $objPost->getRelated('pid')) instanceof \ArchiveModel))
			{
				$objTarget = $objArchive->getRelated('pid');
			}

			if (null !== $objTarget)
			{
				self::$arrUrlCache[$strCacheKey] = ampersand($objTarget->getFrontendUrl((\Config::get('useAutoItem') ? '/' : '/posts/') . ($objPost->alias ?: $objPost->id)));
			}
		}
		
		return self::$arrUrlCache[$strCacheKey];
	}


}