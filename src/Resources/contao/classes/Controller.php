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


/**
 * Controller class
 */
class Controller extends ContaoController
{
	/**
	 * Render page content
	 *
	 * @param mixed  $intId      The page id
	 * @param string $strSection The name of the section
	 *
	 * @return string The module HTML markup
	 */	
	public function renderContainer ($intId, $strSection='main')
	{
		$objContainer = \ContainerModel::findPublishedByPidAndSection($intId, $strSection);

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
			
			$return .= static::generateContainer($objRow, false, $strSection);
			++$intCount;
		}
		
		return $return;		
	}
	
	
	/**
	 * Generate the content of a container and return it as html
	 *
	 * @param mixed   $objRow         The ModelContainer object
	 * @param boolean $blnIsInsertTag If true, there will be no page relation
	 * @param string  $strSection     The name of the section
	 *
	 * @return string The container HTML markup or false
	 */
	public static function generateContainer(\ContainerModel $objRow, $blnIsInsertTag=false, $strSection='main')
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

		$objContainer = new ModuleContainer($objRow, $strSection);
		$strBuffer = $objContainer->generate($blnIsInsertTag);

		// Disable indexing if protected
		if ($objContainer->protected && !preg_match('/^\s*<!-- indexer::stop/', $strBuffer))
		{
			$strBuffer = "\n<!-- indexer::stop -->". $strBuffer ."<!-- indexer::continue -->\n";
		}

		return $strBuffer;
	}	


	/**
	 * Generate the content of a container and return it as html
	 *
	 * @param mixed   $objRow         The ModelStatic object
	 * @param boolean $blnIsInsertTag If true, there will be no page relation
	 *
	 * @return string The article HTML markup or false
	 */
	public static function generateStatic(\StaticModel $objRow, $blnIsInsertTag=false)
	{
		$objStatic = new ModuleStatic($objRow);
		
		if ($blnIsInsertTag)
		{
			$objStatic->staticContent = $objRow->id;
		}

		$strBuffer = $objStatic->generate($blnIsInsertTag);

		// Disable indexing if protected
		if ($objStatic->protected && !preg_match('/^\s*<!-- indexer::stop/', $strBuffer))
		{
			$strBuffer = "\n<!-- indexer::stop -->". $strBuffer ."<!-- indexer::continue -->\n";
		}

		return $strBuffer;
	}	

	
	/**
	 * Generate the content of a container and return it as html
	 *
	 * @param mixed   $objRow         The ModelStatic object
	 * @param boolean $blnIsInsertTag If true, there will be no page relation
	 *
	 * @return string The article HTML markup or false
	 */
	public static function generatePost(\PostsModel $objRow, $blnIsInsertTag=false)
	{
		// Check the visibility (see #6311)
		if (!static::isVisibleElement($objRow))
		{
			return '';
		}

		$strBuffer = implode('', Posts::getPostContent($objRow));

		// Disable indexing if protected
		if ($objRow->protected && !preg_match('/^\s*<!-- indexer::stop/', $strBuffer))
		{
			$strBuffer = "\n<!-- indexer::stop -->". $strBuffer ."<!-- indexer::continue -->\n";
		}

		return $strBuffer;
	}	

	
	/**
	 * Get the rootpage ID
	 *
	 * @param string  $strTable
	 * @param integer $intId
	 *
	 * @return integer The theme ID
	 */
	public function getRootPageId ($strTable, $intId)
	{
		if ('tl_posts' == $strTable)
		{
			$objPost = \PostsModel::findByPk($intId);
		
			if ($objPost === null)
			{
				return null;
			}
			
			$objArchive = \ArchiveModel::findByPk($objPost->pid);
		
			if ($objArchive === null)
			{
				return null;
			}

			$objPage = \PageModel::findWithDetails($objArchive->pid);
			
			if ($objPage === null)
			{
				return null;
			}
			
			return $objPage->rootId;
		}
		
		elseif ('tl_container' == $strTable)
		{
			$objContainer = \ContainerModel::findByPk($intId);
		
			if ($objContainer === null)
			{
				return null;
			}

			$objPage = \PageModel::findWithDetails($objContainer->pid);
			
			if ($objPage === null)
			{
				return null;
			}
			
			return $objPage->rootId;
		}
		
		elseif ('tl_static' == $strTable)
		{
			$objStatic = \StaticModel::findByPk($intId);

			if ($objStatic === null)
			{
				return null;
			}

			return $objStatic->rootId;
		}
	}
	
	
	/**
	 * Get the layout ID
	 *
	 * @param string  $strTable
	 * @param integer $intId
	 *
	 * @return integer The theme ID
	 */
	public function getLayoutId ($strTable, $intId)
	{
		if ('tl_posts' == $strTable)
		{
			$objPost = \PostsModel::findByPk($intId);
		
			if ($objPost === null)
			{
				return null;
			}
			
			$objArchive = \ArchiveModel::findByPk($objPost->pid);
		
			if ($objArchive === null)
			{
				return null;
			}

			$objPage = \PageModel::findWithDetails($objArchive->pid);
			
			if ($objPage === null)
			{
				return null;
			}
			
			return $objPage->layout;
		}
		
		elseif ('tl_container' == $strTable)
		{
			$objContainer = \ContainerModel::findByPk($intId);
		
			if ($objContainer === null)
			{
				return null;
			}

			$objPage = \PageModel::findWithDetails($objContainer->pid);
			
			if ($objPage === null)
			{
				return null;
			}
			
			return $objPage->layout;
		}
		
		elseif ('tl_static' == $strTable)
		{
			$objStatic = \StaticModel::findByPk($intId);

			if ($objStatic === null)
			{
				return null;
			}

			return $objStatic->layout;
		}
	}
	
	
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

	
	/**
	 * Show post informations for comments
	 *
	 * @param $arrRow
	 *
	 * @return string
	 */
	public function listPatternComments($arrRow) 
	{
		if ($arrRow['source'] == 'tl_posts')
		{
			$db = Database::getInstance();
			
			$objParent = $db->prepare("SELECT id, title FROM tl_posts WHERE id=?")
						    ->execute($arrRow['parent']);
			
			if ($objParent->numRows)
			{
				return ' (<a href="contao/main.php?do=posts&amp;table=tl_content&amp;id=' . $objParent->id . '&amp;rt=' . REQUEST_TOKEN . '">' . $objParent->title . '</a>)';
			}
		}
	}
}
