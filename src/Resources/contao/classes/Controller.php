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


class Controller extends \Controller
{
	
	/**
	 * Render page content
	 *
	 * @param mixed  $intId     The page id
	 * @param string $strColumn The name of the column
	 *
	 * @return string The module HTML markup
	 */	
	public function renderContainer ($intId, $strColumn)
	{
		$objContainer = \ContainerModel::findPublishedByPidAndColumn($intId, $strColumn);

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
			
			$return .= static::compileContainer($objRow, false, $strColumn);
			++$intCount;
		}
		
		return $return;		
	}
	
	
	/**
	 * Generate the content of a container and return it as html
	 *
	 * @param mixed   $varId          The article ID or a Model object
	 * @param boolean $blnMultiMode   If true, only teasers will be shown
	 * @param boolean $blnIsInsertTag If true, there will be no page relation
	 * @param string  $strColumn      The name of the column
	 *
	 * @return string|boolean The article HTML markup or false
	 */
	public static function compileContainer($objRow, $blnIsInsertTag=false, $strColumn='main')
	{
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
	 * Hide the whole article content stuff
	 */
	public static function hideArticles()
	{
		// Remove the articles from the backend module array
		unset($GLOBALS['BE_MOD']['content']['article']);
	}	

}