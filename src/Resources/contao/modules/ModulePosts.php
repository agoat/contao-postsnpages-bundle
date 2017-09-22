<?php

/*
 * Contao Extended Articles Extension
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */
 
namespace Agoat\PostsnPages;

use Patchwork\Utf8;


/**
 * Provides methodes to handle posts content and teaser rendering
 *
 * @property array  $news_archives
 * @property string $news_jumpToCurrent
 * @property string $news_format
 * @property int    $news_readerModule
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
abstract class ModulePosts extends \Module
{


	protected function getPosts ()
	{
		/** @var PageModel $objPage */
		global $objPage;

		// Show the posts from particular archive(s)
		if (empty($varPids = \StringUtil::deserialize($this->archive)))
		{
			$objArchives = \ArchiveModel::findByPid($objPage->id);
			
			if (null === $objArchives)
			{
				return;
			}
			
			$varPids = $objArchives->fetchEach('id');
		}
		
		// Handle featured articles
		if ($this->featured == 'featured_articles')
		{
			$blnFeatured = true;
		}
		elseif ($this->featured == 'unfeatured_articles')
		{
			$blnFeatured = false;
		}
		else
		{
			$blnFeatured = null;
		}

		$arrOptions = array();

		// Handle sorting
		if ($this->sortPosts != 'random')
		{
			$arrOptions['order'] = $this->sortPosts . ' ' . (($this->sortOrder == 'descending') ? 'DESC' : 'ASC');
		}
		
		// Handle category filter
		if ($this->filterByCategory)
		{
			$strCategory = $this->category;
		}
		
		// Maximum number of items
		if ($this->numberOfItems > 0)
		{
			$arrOptions['limit'] = intval($this->numberOfItems);
		}

		// Skip items
		if ($this->skipFirst > 0)
		{
			$arrOptions['offset'] = intval($this->skipFirst);
		}

		// Return published articles
		return \PostsModel::findPublishedByPidsAndFeaturedAndCategory($varPids, $blnFeatured, $strCategory, $arrOptions);
	}
	
	
	protected function getTaggedPosts ($strTag)
	{
		/** @var PageModel $objPage */
		global $objPage;

		// Show the posts from particular archive(s)
		if (empty($varPids = \StringUtil::deserialize($this->archive)))
		{
			$objArchives = \ArchiveModel::findByPid($objPage->id);
			
			if (null === $objArchives)
			{
				return;
			}
			
			$varPids = $objArchives->fetchEach('id');
		}

		$objTags = \TagsModel::findPublishedByLabelAndArchives($strTag, $varPids);

		if (null === $objTags)
		{
			return;
		}

		$varIds = $objTags->fetchEach('pid');

		// Handle featured articles
		if ($this->featured == 'featured_articles')
		{
			$blnFeatured = true;
		}
		elseif ($this->featured == 'unfeatured_articles')
		{
			$blnFeatured = false;
		}
		else
		{
			$blnFeatured = null;
		}

		$arrOptions = array();

		// Handle sorting
		if ($this->sortPosts != 'random')
		{
			$arrOptions['order'] = $this->sortPosts . ' ' . (($this->sortOrder == 'descending') ? 'DESC' : 'ASC');
		}
		
		// Return published articles
		return \PostsModel::findPublishedByIdsAndFeatured($varIds, $blnFeatured, $arrOptions);
	}
	
	
	protected function renderPost ($objPost, $blnTeaser=false, $blnContent=true)
	{
		/** @var PageModel $objPage */
		global $objPage;

		list($strId, $strClass) = \StringUtil::deserialize($objPost->cssID, true);
		$latlong = \StringUtil::deserialize($objPost->latlong);
		
		if ($strClass != '')
		{
			$strClass = ' ' . $strClass;
		}
		if ($objPost->featured)
		{
			$strClass .= ' featured';
		}
		if ($objPost->format != 'standard')
		{
			$strClass .= ' ' . $objPost->format;
		}
		
		$article = $objPost->alias ?: $objPost->id;

		$href = Posts::generatePostUrl($objPost, $this->objModel->getRelated('jumpTo'));
		$readMore = \StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['open'], $objPost->url));

		$objPostTemplate = new \FrontendTemplate($this->postTemplate);
		$objPostTemplate->setData($objPost->row());

		// Add html data
		$objPostTemplate->cssId = ($strId) ?: 'teaser-' . $objPost->id;
		$objPostTemplate->cssClass = $strClass;
		$objPostTemplate->href = $href;
		$objPostTemplate->attributes = ($objPost->target && $objPost->readmore !== 'default') ? ' target="_blank"' : '';
		$objPostTemplate->readMore = $readMore;
		$objPostTemplate->more = $GLOBALS['TL_LANG']['MSC']['more'];
		
		// Add teaser
		if ($blnTeaser)
		{
			$objPostTemplate->showTeaser = true;
			
			// Add meta information
			$objPostTemplate->date = \Date::parse($objPage->datimFormat, $objPost->date);
			$objPostTemplate->timestamp = $objPost->date;
			$objPostTemplate->datetime = date('Y-m-d\TH:i:sP', $objPost->date);
			$objPostTemplate->location = $objPost->location;
			$objPostTemplate->latlong = ($latlong[0] !='' && $latlong[1] != '') ? implode(',', $latlong) : false;

			// Add teaser data
			$objPostTemplate->title = \StringUtil::specialchars($objPost->title);
			$objPostTemplate->subtitle = $objPost->subTitle;
			$objPostTemplate->teaser = \StringUtil::toHtml5($objPost->teaser);

			// Add author
			if (($objAuthor = $objPost->getRelated('author')) instanceof \UserModel)
			{
				$objPostTemplate->author = $objAuthor->name;
			}
		
			// Add image
			$objPostTemplate->addImage = false;
			
			if ($objPost->addImage && $objPost->singleSRC != '')
			{
				$objModel = \FilesModel::findByUuid($objPost->singleSRC);
								
				if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
				{
					$this->addImageToTemplate($objPostTemplate, array(
						'singleSRC' => $objModel->path,
						'size' => $this->imgSize,
						'alt' => $objPost->alt,
						'title' => $objPost->title,
						'caption' => $objPost->caption
					));
				}
			}
			
			if (!$blnContent)
			{
				// Add comments information
				$intCCount = \CommentsModel::countPublishedBySourceAndParent('tl_article', $objPost->id);
				
				$objPostTemplate->ccount = $intCCount;
				$objPostTemplate->comments = ($intCCount > 0) ? sprintf($GLOBALS['TL_LANG']['MSC']['commentCount'], $intCCount) : $GLOBALS['TL_LANG']['MSC']['noComments'];
			}
		}
		
		// Add post content
		if ($blnContent)
		{
			$objPostTemplate->elements = $this->getPostContent($objPost);
		}

		return $objPostTemplate->parse();
	}

	
	protected function getPostContent ($objPost)
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
				$arrElements[] = $this->getContentElement($objRow);
				++$intCount;
			}
		}

		return $arrElements;
		
	}

	
}