<?php

/*
 * Contao Extended Articles Extension
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */
 
namespace Agoat\ContentManager;

use Patchwork\Utf8;


/**
 * Provides methodes to handle article teaser rendering
 *
 * @property array  $news_archives
 * @property string $news_jumpToCurrent
 * @property string $news_format
 * @property int    $news_readerModule
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModulePostTeaser extends ModulePost
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_teasers';


	/**
	 * Do not render the module if an article is called directly
	 *
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			/** @var BackendTemplate|object $objTemplate */
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['articleteaser'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Show the article reader if an article is called directly
		if ($this->readerModule > 0 && (isset($_GET['posts']) || (\Config::get('useAutoItem') && isset($_GET['auto_item']))))
		{
			return $this->getFrontendModule($this->readerModule, $this->strColumn);
		}
		
		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		$pageId = $objPage->id;
		$pageObj = $objPage;

		// Show the articles of a different page
		if ($this->defineRoot && $this->rootPage > 0)
		{
			if (($objTarget = $this->objModel->getRelated('rootPage')) instanceof \PageModel)
			{
				$pageId = $objTarget->id;
				$pageObj = $this->objModel->getRelated('rootPage');

				/** @var PageModel $objTarget */
				$this->Template->request = $objTarget->getFrontendUrl();
			}
		}

		// Handle archives
		switch ($this->archive)
		{
			case 'all':
				$objArchive = \ArchiveModel::findByPid($pageId);
				break;
				
			case 'first':
				$objArchive = \ArchiveModel::findFirstByPid($pageId);
				break;
			
			default:
				$objArchive = \ArchiveModel::findById($this->archive);
		}
	
		if ($objArchive === null)
		{
			return;
		}

		$varPids = ($objArchive instanceof \Contao\Model\Collection) ? $objArchive->fetchEach('id') : $objArchive->id;
		
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

		// Handle sorting
		$arrOptions = array('order' => $this->sortBy . ' ' . (($this->sortOrder == 'descending') ? 'DESC' : 'ASC'));
		
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

		// Get published articles
		$objArticles = \PostsModel::findPublishedByPidsAndFeaturedAndCategory($varPids, $blnFeatured, $strCategory, $arrOptions);

		if ($objArticles === null)
		{
			return;
		}

		$arrArticles = array();
		
		if ($objArticles !== null)
		{
			while ($objArticles->next())
			{
				list($strId, $strClass) = \StringUtil::deserialize($objArticles->cssID, true);
				$latlong = \StringUtil::deserialize($objArticles->latlong);
	
				if ($strClass != '')
				{
					$strClass = ' ' . $strClass;
				}
				if ($objArticles->featured)
				{
					$strClass .= ' featured';
				}
				if ($objArticles->format != 'standard')
				{
					$strClass .= ' ' . $objArticles->format;
				}

				if ($objArticles->readmore)
				{
					if (strpos($objArticles->url, 'article_url') !== false)
					{
						$href = 'dadad';
					}
					else
					{
						$href = $objArticles->url;
					}
					$readMore = \StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['open'], $href));
				}
				else
				{
					$href = $pageObj->getFrontendUrl((\Config::get('useAutoItem') ? '/' : '/articles/') . ($objArticles->alias ?: $objArticles->id));
					$readMore = \StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $this->headline), true);
				}
			
				$objTeaserTemplate = new \FrontendTemplate($this->teaserTpl);
				$objTeaserTemplate->setData($objArticles->row());

				// Add html data
				$objTeaserTemplate->cssId = ($strId) ?: 'teaser-' . $objArticles->id;
				$objTeaserTemplate->cssClass = $strClass;
				$objTeaserTemplate->href = $href;
				$objTeaserTemplate->attributes = ($objArticles->target && $objArticles->readmore !== 'default') ? ' target="_blank"' : '';
				$objTeaserTemplate->readMore = $readMore;
				$objTeaserTemplate->more = $GLOBALS['TL_LANG']['MSC']['more'];

				// Add meta information
				$objTeaserTemplate->date = \Date::parse($pageObj->datimFormat, $objArticles->date);
				$objTeaserTemplate->timestamp = $objArticles->date;
				$objTeaserTemplate->datetime = date('Y-m-d\TH:i:sP', $objArticles->date);
				$objTeaserTemplate->location = $objArticles->location;
				$objTeaserTemplate->latlong = ($latlong[0] !='' && $latlong[1] != '') ? implode(',', $latlong) : false;

				// Add teaser data
				$objTeaserTemplate->title = \StringUtil::specialchars($objArticles->title);
				$objTeaserTemplate->subtitle = $objArticles->subTitle;
				$objTeaserTemplate->teaser = \StringUtil::toHtml5($objArticles->teaser);
dump($objTeaserTemplate);	
				// Add author
				if (($objAuthor = $objArticles->getRelated('author')) instanceof UserModel)
				{
					$objTeaserTemplate->author = $objAuthor->name;
				}
				
				// Add image
				$objTeaserTemplate->addImage = false;
				
				if ($objArticles->addImage && $objArticles->singleSRC != '')
				{
					$objModel = \FilesModel::findByUuid($objArticles->singleSRC);
									
					if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
					{
						$this->addImageToTemplate($objTeaserTemplate, array(
							'singleSRC' => $objModel->path,
							'size' => $this->imgSize,
							'alt' => $objArticles->alt,
							'title' => $objArticles->title,
							'caption' => $objArticles->caption
						));
					}
				}
				
				// Add comments information
				$intCCount = \CommentsModel::countPublishedBySourceAndParent('tl_article', $objArticles->id);
				
				$objTeaserTemplate->ccount = $intCCount;
				$objTeaserTemplate->comments = ($intCCount > 0) ? sprintf($GLOBALS['TL_LANG']['MSC']['commentCount'], $intCCount) : $GLOBALS['TL_LANG']['MSC']['noComments'];


				$arrArticles[] = $objTeaserTemplate->parse();

			}
		}

		$this->Template->articles = $arrArticles;
	}
}