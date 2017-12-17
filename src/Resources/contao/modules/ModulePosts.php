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

use Patchwork\Utf8;


/**
 * Abstract ModulePosts class
 */
abstract class ModulePosts extends \Module
{

	/**
	 * Return posts in consideration of the selection procedures 
	 *
	 * @return Collection|\PostsModel|Null
	 */
	protected function getPosts()
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
		if ($this->featured == 'featured_posts')
		{
			$blnFeatured = true;
		}
		elseif ($this->featured == 'unfeatured_posts')
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
	
	
	/**
	 * Return posts with a specific tag label in consideration of the selection procedures 
	 *
	 * @param string $strTag The tag name
	 *
	 * @return Collection|\PostsModel|Null
	 */
	protected function getTaggedPosts($strTag)
	{
		/** @var PageModel $objPage */
		global $objPage;
	
	
		// Get posts tags menu settings (archives)
		$moduleTagsMenu = \ModuleModel::findById($this->tagmenuModule);
	
		// Show the posts from particular archive(s)
		if (null === $moduleTagsMenu || empty($varPids = \StringUtil::deserialize($moduleTagsMenu->archive)))
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
		return \PostsModel::findPublishedByIdsAndFeatured($varIds, $blnFeatured, $arrOptions);
	}
	
	
	/**
	 * Return posts related to the given post id in consideration of the selection procedures 
	 *
	 * @param integer $varId The post id
	 *
	 * @return Collection|\PostsModel|Null
	 */
	protected function getRelatedPosts($varId)
	{
		$objPost = \PostsModel::findPublishedByIdOrAlias($varId);
	
		if (null === $objPost)
		{
			return;
		}

		$varIds = \StringUtil::deserialize($objPost->related);

		$arrOptions = array();

		// Handle sorting
		if (!in_array($this->sortRelated, ['random', 'custom']))
		{
			$arrOptions['order'] = $this->sortRelated . ' ' . (($this->sortOrder == 'descending') ? 'DESC' : 'ASC');
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
		return \PostsModel::findPublishedByIds($varIds, $arrOptions);
	}
	
	
	/**
	 * Renders a post article with teaser and its content
	 *
	 * @param \PostsModel $objPost
	 * @param boolean     $blnTeaser
	 * @param boolean     $blnContent
	 *
	 * @return string
	 */
	protected function renderPost($objPost, $blnTeaser=false, $blnContent=true)
	{
		/** @var PageModel $objPage */
		global $objPage;

		list($strId, $strClass) = \StringUtil::deserialize($objPost->cssID, true);
		list($lat, $long) = \StringUtil::deserialize($objPost->latlong);
		
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

		$objPostTemplate = new \FrontendTemplate($this->postTemplate);
		$objPostTemplate->setData($objPost->row());

		// Add html data
		$objPostTemplate->cssId = ($strId) ?: 'teaser-' . $objPost->id;
		$objPostTemplate->cssClass = $strClass;
		$objPostTemplate->href = Posts::generatePostUrl($objPost, $this->alternativeLink, $this->jumpTo);
		$objPostTemplate->attributes = ($objPost->alternativeLink && $objPost->target) ? ' target="_blank"' : '';
		$objPostTemplate->readMore = \StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['open'], $objPost->url));
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
			$objPostTemplate->latlong = ($lat !='' && $long != '') ? $lat . ', ' . $long : false;

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
				$intCCount = \CommentsModel::countPublishedBySourceAndParent('tl_posts', $objPost->id);
				
				$objPostTemplate->ccount = $intCCount;
				$objPostTemplate->comments = ($intCCount > 0) ? sprintf($GLOBALS['TL_LANG']['MSC']['commentCount'], $intCCount) : $GLOBALS['TL_LANG']['MSC']['noComments'];
			}
		}
		
		// Add post content
		if ($blnContent)
		{
			$objPostTemplate->elements = Posts::getPostContent($objPost);
		}
		else
		{
			$objPostTemplate->elements = array();
		}

		return $objPostTemplate->parse();
	}
}
