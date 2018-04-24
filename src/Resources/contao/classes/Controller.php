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

use Contao\Controller as ContaoController;
use Contao\Database;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Patchwork\Utf8;


/**
 * Controller class
 */
class Controller extends ContaoController
{
	/**
	 * Render page content (output either containers or a post)
	 *
	 * @param mixed  $intId      The page id
	 * @param string $strSection The name of the section
	 *
	 * @return string The module HTML markup
	 */	
	public function renderPageContent($intId, $strSection='main')
	{
		/** @var PageModel $objPage */
		global $objPage;

		if ('post' == $objPage->type)
		{
			return $this->renderPost($intId, $strSection);
		}
		
		else
		{
			return $this->renderContainer($intId, $strSection);
		}
	}
	

	/**
	 * Render post content
	 *
	 * @param mixed  $intId      The page id
	 * @param string $strSection The name of the section
	 *
	 * @return string The module HTML markup
	 */	
	public function renderPost($intId, $strSection='main')
	{
		/** @var PageModel $objPage */
		global $objPage;

		// Set the item from the auto_item parameter
		if (!isset($_GET['posts']) && \Config::get('useAutoItem') && isset($_GET['auto_item']))
		{
			\Input::setGet('posts', \Input::get('auto_item'));
		}

		// Get post id/alias
		$strPost = \Input::get('posts');

		if (!strlen($strPost))
		{
			switch($objPage->emptyPost)
			{
				case 'nothing':
					return;
					
				case 'recent':
					$objArchives = \ArchiveModel::findByPid($objPage->id);
					
					if (null === $objArchives)
					{
						break;
					}

					$objRecent = \PostModel::findRecentPublishedByArchive($objArchives->fetchEach('id'));

					if (null === $objRecent)
					{
						break;
					}

					throw new RedirectResponseException(Posts::generatePostUrl($objRecent, false, false, true));
					
				case 'page':
					if ($objPage->jumpTo && ($objTarget = $objPage->getRelated('jumpTo')) instanceof \PageModel)
					{
						/** @var PageModel $objTarget */
						throw new RedirectResponseException($objTarget->getAbsoluteUrl());
					}					

				case 'notfound':
				default:
			}

			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}

		// Get published post
		$objPost = \PostModel::findPublishedByIdOrAlias($strPost);

		if (null === $objPost)
		{
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}
	
		// Check the visibility
		if (!static::isVisibleElement($objPost))
		{
			return '';
		}
		
		$objPostContent = new ModulePostContent($objPost, $strSection);

		$strBuffer = $objPostContent->generate();

		// Disable indexing if protected
		if ($objContainer->protected && !preg_match('/^\s*<!-- indexer::stop/', $strBuffer))
		{
			$strBuffer = "\n<!-- indexer::stop -->". $strBuffer ."<!-- indexer::continue -->\n";
		}

		return $strBuffer;
	}
	

	/**
	 * Render page content
	 *
	 * @param mixed  $intId      The page id
	 * @param string $strSection The name of the section
	 *
	 * @return string The module HTML markup
	 */	
	public function renderContainer($intId, $strSection='main')
	{
		$objContainer = \ContainerModel::findPublishedByPidAndSection($intId, $strSection);

		if (null === $objContainer)
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
	public static function generatePost(\PostModel $objRow, $blnIsInsertTag=false)
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
		if ('tl_post' == $strTable)
		{
			$objPost = \PostModel::findByPk($intId);
		
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
		if ('tl_post' == $strTable)
		{
			$objPost = \PostModel::findByPk($intId);
		
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
	 * Show post informations for comments
	 *
	 * @param $arrRow
	 *
	 * @return string
	 */
	public function listPatternComments($arrRow) 
	{
		if ($arrRow['source'] == 'tl_post')
		{
			$db = Database::getInstance();
			
			$objParent = $db->prepare("SELECT id, title FROM tl_post WHERE id=?")
						    ->execute($arrRow['parent']);
			
			if ($objParent->numRows)
			{
				return ' (<a href="contao/main.php?do=posts&amp;table=tl_content&amp;id=' . $objParent->id . '&amp;rt=' . REQUEST_TOKEN . '">' . $objParent->title . '</a>)';
			}
		}
	}
	
	
	/**
	 * Calculate the page status icon for postreader pages
	 *
	 * @param $objPage
	 * @param $image
	 *
	 * @return string
	 */
	public static function getPostsPageStatusIcon($objPage, $image) 
	{
		return str_replace('post', 'bundles/agoatpostsnpages/post', $image);
	}
}
