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


class Controller extends \Contao\Controller
{
	
	public function getSections ($intId, $strColumn)
	{
		/** @var PageModel $objPage */
		global $objPage;
	
		$objContainer = \ContainerModel::findPublishedByPidAndColumn($objPage->id, $strColumn);

		if ($objContainer === null)
		{
			return '';
		}
		
		$return = '';
		$intCount = 0;
		$intLast = $objContainer->count() - 1;
		
		while ($objContainer->next())
		{
			/** @var ArticleModel $objRow */
			$objRow = $objContainer->current();
			
			// Add the "first" and "last" classes (see #2583)
			if ($intCount == 0 || $intCount == $intLast)
			{
				$arrCss = array();
				
				if ($intCount == 0)
				{
					$arrCss[] = 'first';
				}
				
				if ($intCount == $intLast)
				{
					$arrCss[] = 'last';
				}
				
				$objRow->classes = $arrCss;
			}
			
			$return .= static::compileSection($objRow, false, $strColumn);
			++$intCount;
		}
		
		return $return;		
	}
	
	/**
	 * Generate an article and return it as string
	 *
	 * @param mixed   $varId          The article ID or a Model object
	 * @param boolean $blnMultiMode   If true, only teasers will be shown
	 * @param boolean $blnIsInsertTag If true, there will be no page relation
	 * @param string  $strColumn      The name of the column
	 *
	 * @return string|boolean The article HTML markup or false
	 */
	public static function compileSection($objRow, $blnIsInsertTag=false, $strColumn='main')
	{
		/** @var PageModel $objPage */
		global $objPage;

		// Check the visibility (see #6311)
		if (!static::isVisibleElement($objRow))
		{
			return '';
		}

		$objRow->headline = $objRow->title;

		// HOOK: add custom logic
		if (isset($GLOBALS['TL_HOOKS']['getArticle']) && is_array($GLOBALS['TL_HOOKS']['getArticle']))
		{
			foreach ($GLOBALS['TL_HOOKS']['getArticle'] as $callback)
			{
				static::importStatic($callback[0])->{$callback[1]}($objRow);
			}
		}

		$objArticle = new ModuleContainer($objRow, $strColumn);
		$strBuffer = $objArticle->generate($blnIsInsertTag);

		// Disable indexing if protected
		if ($objArticle->protected && !preg_match('/^\s*<!-- indexer::stop/', $strBuffer))
		{
			$strBuffer = "\n<!-- indexer::stop -->". $strBuffer ."<!-- indexer::continue -->\n";
		}

		return $strBuffer;
	}	

	
	/**
	 * Avoid article output from the core-bundle (by not using the 'articles' fragment)
	 *
	 * @param mixed   $arrFragments
	 *
	 * @return $arrFragments 
	 */
	public static function hideArticles()
	{
		// Remove the articles from the backend module array
		unset($GLOBALS['BE_MOD']['content']['article']);
	}	

}