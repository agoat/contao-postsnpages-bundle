<?php

/**
 * Contao Open Source CMS - Posts'n'Pages extension
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PostsnPagesBundle\Permalink;

use Agoat\PermalinkBundle\Permalink\PermalinkProviderFactory;
use Agoat\PermalinkBundle\Permalink\PermalinkProviderInterface;
use Contao\CoreBundle\Exception\AccessDeniedException;


/**
 * Main front end controller.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class PostPermalinkProvider extends PermalinkProviderFactory implements PermalinkProviderInterface
{
	
	/**
     * {@inheritdoc}
     */	
	public function getDcaTable()
	{
		return 'tl_posts';
	}


	/**
     * {@inheritdoc}
     */	
	public function getHost($activeRecord)
	{
		$objArchive = \ArchiveModel::findByPk($activeRecord->pid);

		return \PageModel::findWithDetails($objArchive->pid)->domain;
	}


	/**
     * {@inheritdoc}
     */	
	public function getSchema($id)
	{
		$objPosts = \PostsModel::findByPk($id);
		$objArchive = \ArchiveModel::findByPk($activeRecord->pid);

		return \PageModel::findWithDetails($objArchive->pid)->rootUseSSL ? 'https://' : 'http://';
	}


	/**
     * {@inheritdoc}
     */	
	public function getLanguage($id)
	{
		return \PageModel::findWithDetails($id)->rootLanguage;
	}


	/**
     * {@inheritdoc}
     */	
	public function getParentAlias($id)
	{
		return \PageModel::findWithDetails($id)->parentAlias;
	}

	/**
     * {@inheritdoc}
     */	
	protected function getInheritDetails($activeRecord)
	{
		$objCalendar = \CalendarModel::findByPk($activeRecord->pid);

		return \PageModel::findWithDetails($objCalendar->jumpTo);
	}

	
	/**
     * {@inheritdoc}
     */	
	public function createAlias($activeRecord)
	{
		$alias = $this->replaceInsertTags($activeRecord);

		return $alias;
	}


	/**
     * {@inheritdoc}
     */	
	public function getAbsoluteUrl($source)
	{
		$objPosts = \PostsModel::findByPk($source);
		$objArchive = \ArchiveModel::findByPk($activeRecord->pid);

		$objPage = \PageModel::findWithDetails($objArchive->pid);

		$objPermalink = \PermalinkModel::findByContextAndSource('page', $source);

		$schema = $objPage->rootUseSSL ? 'https://' : 'http://';
		$guid = $objPermalink->guid;
		$suffix = $this->suffix;
		
		return $schema . $guid . $suffix;
	}


	/**
	 * Run the controller
	 *
	 * @return Response
	 *
	 * @throws PageNotFoundException
	 */
	protected function replaceInsertTags($activeRecord)
	{
		$tags = preg_split('~{{([\pL\pN][^{}]*)}}~u', $activeRecord->permalink, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		if (count($tags) < 2)
		{
			return $activeRecord->permalink;
		}
		
		$buffer = '';
		
		for ($_rit=0, $_cnt=count($tags); $_rit<$_cnt; $_rit+=2)
		{
			$buffer .= $tags[$_rit];
			list($tag,$addition) = explode ('+', $tags[$_rit+1]);

			// Skip empty tags
			if ($tag == '')
			{
				continue;
			}

			// Replace the tag
			switch (strtolower($tag))
			{
				// Alias
				case 'alias':
					$buffer .= \StringUtil::generateAlias($activeRecord->title) . $addition;
					break;
			
				// Alias
				case 'author':
					$objUser = \UserModel::findByPk($activeRecord->author);
					
					if ($objUser)
					{
						$buffer .= \StringUtil::generateAlias($objUser->name) . $addition;
					}
					break;
			
				// Parent (alias)
				case 'parent':
					$objArchive = \ArchiveModel::findByPk($activeRecord->pid);
					$objParent = \PageModel::findByPk($objArchive->pid);
				
					if ($objParent && 'root' != $objParent->type)
					{
						$buffer .= $objParent->alias . $addition;
					}
					break;
					
				// Date
				case 'date':
					$objArchive = \ArchiveModel::findByPk($activeRecord->pid);
					$objPage = \PageModel::findByPk($objArchive->pid);
	
					if (!($format = $objPage->dateFormat))
					{
						$format = \Config::get('dateFormat');
					}
				
					$buffer .= date($format, $activeRecord->date) . $addition;
					break;
					
				// Year
				case 'year':
					$buffer .= date('Y', $activeRecord->date) . $addition;
					break;
			
				// Month
				case 'month':
					$buffer .= date('m', $activeRecord->date) . $addition;
					break;
			
				// Language
				case 'language':
				
				
				default:
					throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['unknownInsertTag'], $tag)); 
			}
			
		}
		
		
		return $buffer;
	}
}