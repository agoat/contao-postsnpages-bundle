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

namespace Agoat\ContentManager;


class Post extends \Frontend
{
	
	/**
	 * Generate a URL and return it as string
	 *
	 * @param NewsModel $objItem
	 * @param boolean   $blnAddArchive
	 *
	 * @return string
	 */
	public static function generateUrl($objItem, $blnAddArchive=false)
	{
		$strCacheKey = 'id_' . $objItem->id;
		// Load the URL from cache
		if (isset(self::$arrUrlCache[$strCacheKey]))
		{
			return self::$arrUrlCache[$strCacheKey];
		}
		// Initialize the cache
		self::$arrUrlCache[$strCacheKey] = null;
		switch ($objItem->source)
		{
			// Link to an external page
			case 'external':
				if (substr($objItem->url, 0, 7) == 'mailto:')
				{
					self::$arrUrlCache[$strCacheKey] = \StringUtil::encodeEmail($objItem->url);
				}
				else
				{
					self::$arrUrlCache[$strCacheKey] = ampersand($objItem->url);
				}
				break;
			// Link to an internal page
			case 'internal':
				if (($objTarget = $objItem->getRelated('jumpTo')) instanceof PageModel)
				{
					/** @var PageModel $objTarget */
					self::$arrUrlCache[$strCacheKey] = ampersand($objTarget->getFrontendUrl());
				}
				break;
			// Link to an article
			case 'article':
				if (($objArticle = \ArticleModel::findByPk($objItem->articleId, array('eager'=>true))) !== null && ($objPid = $objArticle->getRelated('pid')) instanceof PageModel)
				{
					/** @var PageModel $objPid */
					self::$arrUrlCache[$strCacheKey] = ampersand($objPid->getFrontendUrl('/articles/' . ($objArticle->alias ?: $objArticle->id)));
				}
				break;
		}
		// Link to the default page
		if (self::$arrUrlCache[$strCacheKey] === null)
		{
			$objPage = \PageModel::findByPk($objItem->getRelated('pid')->jumpTo);
			if (!$objPage instanceof PageModel)
			{
				self::$arrUrlCache[$strCacheKey] = ampersand(\Environment::get('request'), true);
			}
			else
			{
				self::$arrUrlCache[$strCacheKey] = ampersand($objPage->getFrontendUrl((\Config::get('useAutoItem') ? '/' : '/items/') . ($objItem->alias ?: $objItem->id)));
			}
			// Add the current archive parameter (news archive)
			if ($blnAddArchive && \Input::get('month') != '')
			{
				self::$arrUrlCache[$strCacheKey] .= '?month=' . \Input::get('month');
			}
		}
		return self::$arrUrlCache[$strCacheKey];
	}


}